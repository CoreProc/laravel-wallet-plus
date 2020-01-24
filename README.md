# Laravel Wallet Plus

[![Latest Version on Packagist](https://img.shields.io/packagist/v/coreproc/laravel-wallet-plus.svg?style=flat-square)](https://packagist.org/packages/coreproc/laravel-wallet-plus)
[![Quality Score](https://img.shields.io/scrutinizer/g/coreproc/laravel-wallet-plus.svg?style=flat-square)](https://scrutinizer-ci.com/g/coreproc/laravel-wallet-plus)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/coreproc/laravel-wallet-plus/run-tests?label=tests)](https://github.com/coreproc/laravel-wallet-plus/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/coreproc/laravel-wallet-plus.svg?style=flat-square)](https://packagist.org/packages/coreproc/laravel-wallet-plus)

Easily add a virtual wallet to your Laravel models. Features multiple wallets and a ledger system to help keep track of all transactions in the wallets.

## Installation

You can install the package via composer:

```bash
composer require coreproc/laravel-wallet-plus
```

You can publish the migration with:

```bash
php artisan vendor:publish --provider="CoreProc\WalletPlus\WalletPlusServiceProvider" --tag="migrations"
```

After the migration file has been published you can create the wallet-plus tables by running the migration:

```bash
php artisan migrate
```

## Usage

First, you'll need to add the `HasWallets` trait to your model.

```php
use CoreProc\WalletPlus\Models\Traits\HasWallets;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, HasWallets;
}
```

By adding the `HasWallets` trait, you've essentially added all the wallet relationships to the model.

You can start by creating a wallet for the given model.

```php
$user = User::find(1);

$wallet = $user->wallets()->create();
```

You can then increment the wallet balance by:

```php
$wallet->incrementBalance(100);
```

Or decrement the balance by:

```php
$wallet->decrementBalance(100);
```

To get the balance of the wallet, you can use the `balance` accessor:

```php
$wallet->balance;
```

A wallet can be accessed using the `wallet()` method in the model:

```php
$user->wallet();
```

You can set up multiple types of wallets by defining a `WalletType`. Simply create a wallet type entry in the 
`wallet_types` table and create a wallet using this wallet type.

```php
use CoreProc\WalletPlus\Models\WalletType;

$walletType = WalletType::create([
    'name' => 'Peso Wallet',
    'decimals' => 2, // Set how many decimal points your wallet accepts here. Defaults to 0.
]);

$user->wallets()->create(['wallet_type_id' => $walletType->id]);
```

You can access a model's particular wallet type by using the `wallet()` method as well:

```php
$pesoWallet = $user->wallet('Peso Wallet'); // This method also accepts the ID of the wallet type as a parameter

$pesoWallet->incrementBalance(100);

$pesoWallet->balance; // Returns the updated balance without having to refresh the model.
```

All movements made in the wallet are recorded in the `wallet_ledgers` table.

### Defining Transactions

Ideally, we want to record all transactions concerning the wallet by linking it to a transaction model. Let's say we
have a `PurchaseTransaction` model which holds the data of a purchase the user makes in our app.

```php
use Illuminate\Database\Eloquent\Model;

class PurchaseTransaction extends Model
{
    //
}
```

We can link this `PurchaseTransaction` to the wallet ledger by implementing the `WalletTransaction` contract to this 
model and using this transaction to decrement (or increment, whatever the case may be) the wallet balance.

```php
use CoreProc\WalletPlus\Contracts\WalletTransaction;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransaction extends Model implements WalletTransaction
{
    public function getAmount() 
    {
        return $this->amount;
    }
}
```

Now we can use this in the wallet:

```php
$wallet = $user->wallet('Peso Wallet');

$purchaseTransaction = PurchaseTransaction::create([
    ...,
    'amount' => 100,
]);

$wallet->decrementBalance($purchaseTransaction);
```

By doing this, you will be able to see in the `wallet_ledgers` table the transaction that is related to the movement
in the wallet.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email chris.bautista@coreproc.ph instead of using the issue tracker.

## Credits

- [Chris Bautista](https://github.com/chrisbjr)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
