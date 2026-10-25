<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $account = $this->account($request);
        $filters = $this->filters($request);
        $query = $this->filteredQuery($account, $filters);

        $income = (int) (clone $query)->where('type', 'income')->sum('amount');
        $expense = (int) (clone $query)->where('type', 'expense')->sum('amount');
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $monthIncome = (int) (clone $query)
            ->where('type', 'income')
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->sum('amount');
        $monthExpense = (int) (clone $query)
            ->where('type', 'expense')
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->sum('amount');

        $recentTransactions = $query
            ->with('category:id,name')
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Transaction $transaction): array => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'date' => $transaction->date,
                'category' => $transaction->category?->name,
                'notes' => $transaction->notes,
            ]);

        return Inertia::render('Home', [
            'summary' => [
                'balance' => $account->initial_balance + $income - $expense,
                'month_income' => $monthIncome,
                'month_expense' => $monthExpense,
                'month_label' => now()->format('F Y'),
            ],
            'filters' => $filters,
            'categories' => $account->categories()
                ->orderBy('type')
                ->orderBy('name')
                ->get(['id', 'name', 'type']),
            'recent_transactions' => $recentTransactions,
        ]);
    }

    private function filters(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'category' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(['income', 'expense'])],
        ]);

        return array_filter($data, fn ($value): bool => $value !== null && $value !== '');
    }

    private function filteredQuery(Account $account, array $filters): Builder
    {
        $query = $account->transactions()->getQuery();

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

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query;
    }

    private function account(Request $request): Account
    {
        $account = $request->user();

        if (! $account instanceof Account) {
            abort(403);
        }

        return $account;
    }
}
