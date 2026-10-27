<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function income(Request $request): Response
    {
        return $this->listing($request, 'income');
    }

    public function expense(Request $request): Response
    {
        return $this->listing($request, 'expense');
    }

    public function store(Request $request): RedirectResponse
    {
        $account = $this->account($request);
        $data = $this->validated($request, $account);

        $account->transactions()->create([
            'type' => $data['type'],
            'amount' => $data['amount'],
            'notes' => $data['notes'] ?? null,
            'date' => $data['date'],
            'time' => $data['time'] ? $data['time'].':00' : null,
            'categories_id' => $data['category_id'],
        ]);

        return back();
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('update', $transaction);

        $account = $this->account($request);
        $data = $this->validated($request, $account);

        $transaction->update([
            'type' => $data['type'],
            'amount' => $data['amount'],
            'notes' => $data['notes'] ?? null,
            'date' => $data['date'],
            'time' => $data['time'] ? $data['time'].':00' : null,
            'categories_id' => $data['category_id'],
        ]);

        return back();
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('delete', $transaction);

        $redirectRoute = $transaction->type;
        $transaction->delete();

        return redirect()->route($redirectRoute);
    }

    public function exportIncome(Request $request): StreamedResponse
    {
        return $this->exportListing($request, 'income');
    }

    public function exportExpense(Request $request): StreamedResponse
    {
        return $this->exportListing($request, 'expense');
    }

    private function listing(Request $request, string $type): Response
    {
        $account = $this->account($request);
        $filters = $this->filters($request);

        $query = $this->filteredQuery($account, $type, $filters);
        $total = (int) (clone $query)->sum('amount');

        $transactions = $query
            ->with('category:id,name')
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Transaction $transaction): array => [
                'id' => $transaction->id,
                'date' => $transaction->date,
                'time' => $transaction->time,
                'category' => $transaction->category?->name,
                'category_id' => $transaction->categories_id,
                'notes' => $transaction->notes,
                'amount' => $transaction->amount,
            ]);

        return Inertia::render($type === 'income' ? 'Income' : 'Expense', [
            'type' => $type,
            'transactions' => $transactions,
            'categories' => $account->categories()
                ->where('type', $type)
                ->orderBy('name')
                ->get(['id', 'name']),
            'filters' => $filters,
            'total' => $total,
        ]);
    }

    private function exportListing(Request $request, string $type): StreamedResponse
    {
        $account = $this->account($request);
        $filters = $this->filters($request);

        $query = $this->filteredQuery($account, $type, $filters)
            ->with('category:id,name')
            ->orderBy('date')
            ->orderBy('time')
            ->orderBy('id');

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['date', 'time', 'type', 'category', 'amount', 'notes']);

            $query->chunk(500, function ($transactions) use ($handle): void {
                foreach ($transactions as $transaction) {
                    fputcsv($handle, [
                        $transaction->date,
                        $transaction->time,
                        $transaction->type,
                        $transaction->category?->name,
                        $transaction->amount,
                        $transaction->notes,
                    ]);
                }
            });

            fclose($handle);
        }, $type.'-transactions-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function account(Request $request): Account
    {
        $account = $request->user();

        if (! $account instanceof Account) {
            abort(403);
        }

        return $account;
    }

    public function categories(Request $request): Response
    {
        $account = $this->account($request);

        return Inertia::render('Categories', [
            'categories' => $account->categories()
                ->orderBy('type')
                ->orderBy('name')
                ->get(['id', 'name', 'type']),
        ]);
    }

    /**
     * @return array{from?: string, to?: string, category?: int, q?: string}
     */
    private function filters(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'category' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $filters = [];

        if (! empty($data['from'])) {
            $filters['from'] = $data['from'];
        }

        if (! empty($data['to'])) {
            $filters['to'] = $data['to'];
        }

        if (! empty($data['category'])) {
            $filters['category'] = (int) $data['category'];
        }

        if (! empty($data['q'])) {
            $filters['q'] = $data['q'];
        }

        return $filters;
    }

    /**
     * @param  array{from?: string, to?: string, category?: int, q?: string}  $filters
     * @return Builder<Transaction>
     */
    private function filteredQuery(Account $account, string $type, array $filters): Builder
    {
        $query = Transaction::query()
            ->where('accounts_id', $account->id)
            ->where('type', $type);

        if (isset($filters['from'])) {
            $query->where('date', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('date', '<=', $filters['to']);
        }

        if (isset($filters['category'])) {
            $query->where('categories_id', $filters['category']);
        }

        if (isset($filters['q'])) {
            $query->where('notes', 'like', '%'.$filters['q'].'%');
        }

        return $query;
    }

    /**
     * @return array{type: string, category_id: int, amount: int, notes: string|null, date: string, time: string|null}
     */
    private function validated(Request $request, Account $account): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'category_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['nullable', 'date_format:H:i'],
        ]);

        $category = Category::query()
            ->whereKey($data['category_id'])
            ->where('accounts_id', $account->id)
            ->where('type', $data['type'])
            ->exists();

        if (! $category) {
            throw ValidationException::withMessages([
                'category_id' => 'Choose a category that matches this transaction type.',
            ]);
        }

        return [
            'type' => $data['type'],
            'category_id' => (int) $data['category_id'],
            'amount' => (int) $data['amount'],
            'notes' => $data['notes'] ?? null,
            'date' => $data['date'],
            'time' => $data['time'] ?? null,
        ];
    }
}
