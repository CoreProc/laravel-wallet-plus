<?php

namespace CoreProc\WalletPlus\Models\Traits;

use CoreProc\WalletPlus\Models\Wallet;

trait HasWallets
{
    public function wallets()
    {
        return $this->morphMany(Wallet::class, 'user');
    }

    public function wallet($walletTypeId)
    {
        return $this->wallets()->where('wallet_type_id', $walletTypeId)->first();
    }
}
