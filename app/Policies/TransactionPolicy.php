<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Transaction;

class TransactionPolicy
{
    public function update(Account $account, Transaction $transaction): bool
    {
        return $transaction->accounts_id === $account->id;
    }

    public function delete(Account $account, Transaction $transaction): bool
    {
        return $transaction->accounts_id === $account->id;
    }
}
