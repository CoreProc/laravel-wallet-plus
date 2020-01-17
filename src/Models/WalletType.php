<?php

namespace CoreProc\WalletPlus\Models;

use Illuminate\Database\Eloquent\Model;

class WalletType extends Model
{
    protected $fillable = [
        'name',
        'decimals',
    ];
}
