<?php

namespace CoreProc\WalletPlus\Models;

use Illuminate\Database\Eloquent\Model;

class WalletLedger extends Model
{
    protected $fillable = [
        'wallet_id',
        'transaction_id',
        'transaction_type',
        'amount',
        'running_raw_balance',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transaction()
    {
        return $this->morphTo('transaction');
    }
}
