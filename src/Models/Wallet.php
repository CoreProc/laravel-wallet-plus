<?php

namespace CoreProc\WalletPlus\Models;

use CoreProc\WalletPlus\Contracts\WalletTransaction;
use Exception;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'wallet_type_id',
        'raw_balance',
    ];

    public function user()
    {
        return $this->morphTo('user');
    }

    public function walletType()
    {
        return $this->belongsTo(WalletType::class);
    }

    public function getBalanceAttribute()
    {
        if ($this->walletType->decimals === 0) {
            return $this->raw_balance;
        }

        return $this->raw_balance * pow(10, $this->walletType->decimals);
    }

    /**
     * @param $transaction WalletTransaction|integer
     * @throws Exception
     */
    public function incrementBalance($transaction)
    {
        if (is_int($transaction)) {
            $this->increment('raw_balance', $transaction);
            $this->createWalletLedgerEntry($transaction, $this->raw_balance);

            return;
        }

        if (! $transaction instanceof WalletTransaction) {
            throw new Exception('Increment balance expects parameter to be an integer or a WalletTransaction object.');
        }

        $this->increment('raw_balance', $transaction->getAmount());

        // Record in ledger
        $this->createWalletLedgerEntry($transaction, $this->raw_balance);

        return;
    }

    /**
     * @param $transaction WalletTransaction|integer
     * @throws Exception
     */
    public function decrementBalance($transaction)
    {
        if (is_int($transaction)) {
            $this->decrement('raw_balance', $transaction);
            $this->createWalletLedgerEntry($transaction, $this->raw_balance, 'decrement');

            return;
        }

        if (! $transaction instanceof WalletTransaction) {
            throw new Exception('Decrement balance expects parameter to be an integer or a WalletTransaction object.');
        }

        $this->decrement('raw_balance', $transaction->getAmount());

        // Record in ledger
        $this->createWalletLedgerEntry($transaction, $this->raw_balance, 'decrement');

        return;
    }

    private function createWalletLedgerEntry($transaction, $newRunningRawBalance, $type = 'increment')
    {
        if (is_int($transaction)) {
            if ($type === 'decrement') {
                $transaction = -$transaction;
            }

            return WalletLedger::query()->create([
                'wallet_id' => $this->id,
                'amount' => $transaction,
                'running_raw_balance' => $newRunningRawBalance,
            ]);
        }

        if (! $transaction instanceof WalletTransaction) {
            throw new Exception('Increment balance expects parameter to be an integer or a WalletTransaction object.');
        }

        $amount = $transaction->getAmount();

        if ($type === 'decrement') {
            $amount = -$amount;
        }

        return WalletLedger::query()->create([
            'wallet_id' => $this->id,
            'transaction_id' => $transaction->id,
            'transaction_type' => get_class($transaction),
            'amount' => $amount,
            'running_raw_balance' => $newRunningRawBalance,
        ]);
    }
}
