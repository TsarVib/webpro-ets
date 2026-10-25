<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Recap report: {{ $period_label }}</title>
    <style>
        body { color: #303446; font-family: Arial, sans-serif; font-size: 12px; margin: 32px; }
        h1 { margin-bottom: 4px; }
        h2 { border-bottom: 1px solid #bcc0cc; margin-top: 24px; padding-bottom: 6px; }
        .muted { color: #5c5f77; }
        .summary { display: table; margin: 18px 0; width: 100%; }
        .summary div { display: table-cell; padding: 10px; width: 33%; }
        .income { color: #358524; }
        .expense { color: #b00c30; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #d5d8e0; padding: 7px; text-align: left; }
        th:last-child, td:last-child { text-align: right; }
        .category-grid { display: table; width: 100%; }
        .category-list { display: table-cell; padding-right: 24px; vertical-align: top; width: 50%; }
    </style>
</head>
<body>
    <h1>Financial recap</h1>
    <p class="muted">{{ $period_label }}</p>

    <div class="summary">
        <div><strong>Income</strong><br><span class="income">Rp {{ number_format($summary['income'], 0, ',', '.') }}</span></div>
        <div><strong>Expense</strong><br><span class="expense">Rp {{ number_format($summary['expense'], 0, ',', '.') }}</span></div>
        <div><strong>Net change</strong><br>Rp {{ number_format($summary['net'], 0, ',', '.') }}</div>
    </div>

    <div class="category-grid">
        @foreach (['income' => 'Income by category', 'expense' => 'Expense by category'] as $type => $title)
            <div class="category-list">
                <h2>{{ $title }}</h2>
                <table>
                    @foreach ($category_totals[$type] as $item)
                        <tr>
                            <td>{{ $item['category'] }}</td>
                            <td>Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endforeach
    </div>

    <h2>Transactions</h2>
    <table>
        <thead>
            <tr><th>Date</th><th>Time</th><th>Type</th><th>Category</th><th>Amount</th><th>Notes</th></tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction['date'] }}</td>
                    <td>{{ $transaction['time'] ?? '-' }}</td>
                    <td>{{ ucfirst($transaction['type']) }}</td>
                    <td>{{ $transaction['category'] }}</td>
                    <td>Rp {{ number_format($transaction['amount'], 0, ',', '.') }}</td>
                    <td>{{ $transaction['notes'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
