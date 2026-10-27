<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $account = $this->account($request);

        return Inertia::render('Categories', [
            'categories' => $account->categories()
                ->withCount('transactions')
                ->orderBy('type')
                ->orderBy('name')
                ->get(['id', 'name', 'type']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $account = $this->account($request);
        $data = $this->validated($request, $account);

        $account->categories()->create($data);

        return back();
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $account = $this->account($request);
        $this->ensureOwned($category, $account);
        $data = $this->validated($request, $account, $category);

        if ($data['type'] !== $category->type && $category->transactions()->exists()) {
            return back()->withErrors([
                'type' => 'This category cannot change type while it has transactions.',
            ]);
        }

        $category->update($data);

        return back();
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $account = $this->account($request);
        $this->ensureOwned($category, $account);

        if ($category->transactions()->exists()) {
            return back()->withErrors([
                'category' => 'This category cannot be deleted while it has transactions.',
            ]);
        }

        $category->delete();

        return back();
    }

    private function account(Request $request): Account
    {
        $account = $request->user();

        if (! $account instanceof Account) {
            abort(403);
        }

        return $account;
    }

    private function ensureOwned(Category $category, Account $account): void
    {
        abort_unless($category->accounts_id === $account->id, 404);
    }

    /**
     * @return array{name: string, type: string}
     */
    private function validated(Request $request, Account $account, ?Category $category = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')
                    ->where('accounts_id', $account->id)
                    ->where('type', $request->input('type'))
                    ->whereNull('deleted_at')
                    ->ignore($category?->id),
            ],
            'type' => ['required', Rule::in(['income', 'expense'])],
        ]);
    }
}
