<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecapController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $account = $this->account($request);
        $report = $this->report($request, $account);

        return Inertia::render('Recap', [
            ...$report['props'],
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        $report = $this->report($request, $this->account($request));

        $filename = 'recap-'.str($report['props']['period_label'])->slug('-').'.csv';

        return response()->stream(function () use ($report): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['date', 'time', 'type', 'category', 'amount', 'notes']);

            foreach ($report['transactions'] as $transaction) {
                fputcsv($handle, [
                    $transaction['date'],
                    $transaction['time'],
                    $transaction['type'],
                    $transaction['category'],
                    $transaction['amount'],
                    $transaction['notes'],
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function html(Request $request): HttpResponse
    {
        $report = $this->report($request, $this->account($request));

        return response()->view('recap.report', $report['props']);
    }

    public function pdf(Request $request): HttpResponse
    {
        $report = $this->report($request, $this->account($request));

        return Pdf::loadView('recap.report', $report['props'])
            ->setPaper('a4')
            ->download('recap-'.str($report['props']['period_label'])->slug('-').'.pdf');
    }

    private function report(Request $request, Account $account): array
    {
        $data = $this->validatedFilters($request);
        $mode = $data['mode'] ?? 'month';
        [$periodStart, $periodEnd, $periodLabel] = $this->period($mode, $data);
        $query = $account->transactions()->whereBetween('date', [$periodStart, $periodEnd]);
        $income = (int) (clone $query)->where('type', 'income')->sum('amount');
        $expense = (int) (clone $query)->where('type', 'expense')->sum('amount');
        $categoryTotals = (clone $query)
            ->with('category:id,name')
            ->select('type', 'categories_id')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('type', 'categories_id')
            ->orderBy('type')
            ->orderByDesc('total')
            ->get()
            ->map(fn (Transaction $transaction): array => [
                'type' => $transaction->type,
                'category' => $transaction->category?->name ?? 'Uncategorized',
                'total' => (int) $transaction->total,
            ]);
        $transactions = $query
            ->with('category:id,name')
            ->orderBy('date')
            ->orderBy('time')
            ->orderBy('id')
            ->get()
            ->map(fn (Transaction $transaction): array => [
                'date' => $transaction->date,
                'time' => $transaction->time,
                'type' => $transaction->type,
                'category' => $transaction->category?->name ?? 'Uncategorized',
                'amount' => $transaction->amount,
                'notes' => $transaction->notes,
            ]);

        return [
            'transactions' => $transactions,
            'props' => [
                'mode' => $mode,
                'month' => $data['month'] ?? now()->format('Y-m'),
                'from' => $data['from'] ?? now()->format('Y-m'),
                'to' => $data['to'] ?? now()->format('Y-m'),
                'year' => $data['year'] ?? now()->format('Y'),
                'period_label' => $periodLabel,
                'summary' => [
                    'income' => $income,
                    'expense' => $expense,
                    'net' => $income - $expense,
                ],
                'category_totals' => [
                    'income' => $categoryTotals->where('type', 'income')->values()->all(),
                    'expense' => $categoryTotals->where('type', 'expense')->values()->all(),
                ],
                'transactions' => $transactions->all(),
            ],
        ];
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'mode' => ['nullable', 'in:month,range,year'],
            'month' => ['nullable', 'date_format:Y-m'],
            'from' => ['nullable', 'date_format:Y-m'],
            'to' => ['nullable', 'date_format:Y-m', 'after_or_equal:from'],
            'year' => ['nullable', 'date_format:Y'],
        ]);
    }

    private function period(string $mode, array $data): array
    {
        if ($mode === 'year') {
            $date = Carbon::createFromFormat('Y', $data['year'] ?? now()->format('Y'));

            return [
                $date->copy()->startOfYear()->toDateString(),
                $date->copy()->endOfYear()->toDateString(),
                $date->format('Y'),
            ];
        }

        if ($mode === 'range') {
            $from = Carbon::createFromFormat('Y-m', $data['from'] ?? now()->format('Y-m'));
            $to = Carbon::createFromFormat('Y-m', $data['to'] ?? now()->format('Y-m'));

            return [
                $from->copy()->startOfMonth()->toDateString(),
                $to->copy()->endOfMonth()->toDateString(),
                $from->format('F Y').' to '.$to->format('F Y'),
            ];
        }

        $date = Carbon::createFromFormat('Y-m', $data['month'] ?? now()->format('Y-m'));

        return [
            $date->copy()->startOfMonth()->toDateString(),
            $date->copy()->endOfMonth()->toDateString(),
            $date->format('F Y'),
        ];
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
