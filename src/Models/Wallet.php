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

    private function createWalletLedgerEntry($transaction, $newRunningRawBalance)
    {
        if (is_int($transaction)) {
            return WalletLedger::query()->create([
                'wallet_id' => $this->id,
                'amount' => $transaction,
                'running_raw_balance' => $newRunningRawBalance,
            ]);
        }

        if (! $transaction instanceof WalletTransaction) {
            throw new Exception('Increment balance expects parameter to be an integer or a WalletTransaction object.');
        }

        return WalletLedger::query()->create([
            'wallet_id' => $this->id,
            'transaction_id' => $transaction->id,
            'transaction_type' => get_class($transaction),
            'amount' => $transaction->getAmount(),
            'running_raw_balance' => $newRunningRawBalance,
        ]);
    }
}
