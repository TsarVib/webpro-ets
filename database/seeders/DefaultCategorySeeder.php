<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultCategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'Salary', 'type' => 'income'],
            ['name' => 'Freelance', 'type' => 'income'],
            ['name' => 'Investment', 'type' => 'income'],
            ['name' => 'Gift', 'type' => 'income'],
            ['name' => 'Other Income', 'type' => 'income'],
            ['name' => 'Food & Drink', 'type' => 'expense'],
            ['name' => 'Transport', 'type' => 'expense'],
            ['name' => 'Shopping', 'type' => 'expense'],
            ['name' => 'Bills', 'type' => 'expense'],
            ['name' => 'Health', 'type' => 'expense'],
            ['name' => 'Entertainment', 'type' => 'expense'],
            ['name' => 'Education', 'type' => 'expense'],
            ['name' => 'Other Expense', 'type' => 'expense'],
        ];

        DB::table('default_categories')->insertOrIgnore($defaults);

        $accountIds = DB::table('accounts')->pluck('id');

        foreach ($accountIds as $accountId) {
            foreach ($defaults as $default) {
                DB::table('categories')->insertOrIgnore([
                    'name' => $default['name'],
                    'type' => $default['type'],
                    'accounts_id' => $accountId,
                ]);
            }
        }
    }
}
