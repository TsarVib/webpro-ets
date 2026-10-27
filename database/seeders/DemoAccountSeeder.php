<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccountSeeder extends Seeder
{
    /**
     * A public demo login with three months of sample data. Skipped when it already exists,
     * so redeploys never reset or duplicate what visitors have entered.
     */
    public function run(): void
    {
        if (Account::where('username', 'demo')->exists()) {
            return;
        }

        $account = Account::create([
            'username' => 'demo',
            'full_name' => 'Demo User',
            'email' => 'demo@example.com',
            'password_hash' => Hash::make('demo1234'),
            'initial_balance' => 2_000_000,
        ]);

        // Categories come from the default_categories trigger on account insert.
        $categories = $account->categories()->pluck('id', 'name');

        // [months ago, day, type, category, amount, notes, time]
        $entries = [
            [2, 1, 'income', 'Salary', 7_500_000, 'Monthly salary', '08:00'],
            [2, 3, 'expense', 'Bills', 850_000, 'Electricity and internet', '19:30'],
            [2, 6, 'expense', 'Food & Drink', 420_000, 'Groceries', '17:10'],
            [2, 12, 'expense', 'Transport', 300_000, 'Fuel', '07:45'],
            [2, 18, 'income', 'Freelance', 1_200_000, 'Logo design project', '14:00'],
            [2, 22, 'expense', 'Entertainment', 150_000, 'Cinema', '20:15'],
            [1, 1, 'income', 'Salary', 7_500_000, 'Monthly salary', '08:00'],
            [1, 4, 'expense', 'Bills', 870_000, 'Electricity and internet', '19:00'],
            [1, 9, 'expense', 'Shopping', 1_350_000, 'New running shoes', '16:20'],
            [1, 14, 'expense', 'Food & Drink', 510_000, 'Groceries', '18:05'],
            [1, 20, 'expense', 'Health', 275_000, 'Pharmacy', '10:30'],
            [1, 25, 'income', 'Investment', 650_000, 'Dividend', '09:00'],
            [0, 1, 'income', 'Salary', 7_500_000, 'Monthly salary', '08:00'],
            [0, 2, 'expense', 'Bills', 860_000, 'Electricity and internet', '19:15'],
            [0, 3, 'expense', 'Education', 2_500_000, 'Online course', '13:40'],
            [0, 5, 'expense', 'Food & Drink', 95_000, 'Lunch with team', '12:30'],
            [0, 7, 'income', 'Gift', 500_000, 'Birthday gift', '11:00'],
            [0, 8, 'expense', 'Transport', 120_000, 'Ride hailing', '08:20'],
        ];

        foreach ($entries as [$monthsAgo, $day, $type, $category, $amount, $notes, $time]) {
            // Capped at today so this month's entries are never in the future.
            $date = now()->startOfMonth()->subMonths($monthsAgo)->day($day)->min(now());

            $account->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'notes' => $notes,
                'date' => $date->toDateString(),
                'time' => $time.':00',
                'categories_id' => $categories[$category],
            ]);
        }
    }
}
