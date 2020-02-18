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
    
    public function walletLedgers()
    {
        return $this->hasMany(WalletLedger::class);
    }

    public function getBalanceAttribute()
    {
        if (empty($this->walletType->decimals)) {
            return $this->raw_balance;
        }

        return $this->raw_balance / pow(10, $this->walletType->decimals);
    }

    /**
     * @param $transaction WalletTransaction|integer|float|double
     * @return Wallet
     * @throws Exception
     */
    public function incrementBalance($transaction)
    {
        if (is_numeric($transaction)) {
            $amount = $this->convertToWalletTypeInteger($transaction);
            $this->increment('raw_balance', $amount);
            $this->createWalletLedgerEntry($amount, $this->raw_balance);

            return $this;
        }

        if (! $transaction instanceof WalletTransaction) {
            throw new Exception('Increment balance expects parameter to be a float or a WalletTransaction object.');
        }

        $this->increment('raw_balance', $transaction->getAmount() * pow(10, $this->walletType->decimals));

        // Record in ledger
        $this->createWalletLedgerEntry($transaction, $this->raw_balance);

        return $this;
    }

    /**
     * @param $transaction WalletTransaction|integer|float|double
     * @return Wallet
     * @throws Exception
     */
    public function decrementBalance($transaction)
    {
        if (is_numeric($transaction)) {
            $amount = $this->convertToWalletTypeInteger($transaction);
            $this->decrement('raw_balance', $amount);
            $this->createWalletLedgerEntry($amount, $this->raw_balance, 'decrement');

            return $this;
        }

        if (! $transaction instanceof WalletTransaction) {
            throw new Exception('Decrement balance expects parameter to be a number or a WalletTransaction object.');
        }

        $this->decrement('raw_balance', $transaction->getAmount() * pow(10, $this->walletType->decimals));

        // Record in ledger
        $this->createWalletLedgerEntry($transaction, $this->raw_balance, 'decrement');

        return $this;
    }

    /**
     * @param $transaction
     * @param $newRunningRawBalance
     * @param string $type
     * @return mixed
     * @throws Exception
     */
    private function createWalletLedgerEntry($transaction, $newRunningRawBalance, $type = 'increment')
    {
        if (is_numeric($transaction)) {
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
            throw new Exception('Wallet ledger entries expect first parameter to be numeric or a WalletTransaction ' .
                'instance');
        }

        $amount = $this->convertToWalletTypeInteger($transaction->getAmount());

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

    /**
     * Converts the given value to an integer that is compatible with this wallet's type.
     *
     * @param int $value
     * @return float|int
     */
    private function convertToWalletTypeInteger($value)
    {
        if (empty($this->walletType) || $this->walletType->decimals === 0) {
            return $value;
        }

        return (int)($value * pow(10, $this->walletType->decimals));
    }
}
