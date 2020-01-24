<?php

namespace CoreProc\WalletPlus\Models\Traits;

use CoreProc\WalletPlus\Models\Wallet;

trait HasWallets
{
    public function wallets()
    {
        return $this->morphMany(Wallet::class, 'user');
    }

    /**
     * @param int|string|null $walletType Can either be the name, or the wallet type ID. Can also be null if you're not
     * using wallet types.
     * @return Wallet
     */
    public function wallet($walletType = null)
    {
        if(is_null($walletType)) {
            return $this->wallets()->whereNull('wallet_type_id')->first();
        }

        if(is_int($walletType)) {
            return $this->wallets()->where('wallet_type_id', $walletType)->first();
        }

        if(is_string($walletType)) {
            return $this->wallets()->whereHas('walletType', function($q) use ($walletType) {
                return $q->where('name', $walletType);
            })->first();
        }
    }
}
