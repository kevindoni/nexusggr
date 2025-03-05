
# NEXUSGGR API DOCUMENTATION (LARAVEL)


## Installation

Required : 
- Laravel 11 or higher
- PHP 8.2 or higher

Open your laravel project directory then install

```bash
  composer require awkaay/nexusggr
```
    
- Place it inside the .env file

```bash
NEXUSGGR_AGENT=youragent
NEXUSGGR_TOKEN=yourtoken
NEXUSGGR_ENDPOINT=https://api.nexusggr.com
```
## Usage/Examples

- Create new user:

```php
use Awkaay\Nexusggr\Nexusggr;

$nexusggr = new Nexusggr;
$register = $nexusggr->register(username: 'test');
```
- Deposit or Withdraw:

```php
use Awkaay\Nexusggr\Nexusggr;

$nexusggr = new Nexusggr;
$deposit = $nexusggr->transaction(username: 'test', type: 'deposit', amount: 100000);
$withdraw = $nexusggr->transaction(username: 'test', type: 'withdraw', amount: 100000);
```
- Reset User Balance:

```php
use Awkaay\Nexusggr\Nexusggr;

$nexusggr = new Nexusggr;
$resetBalanceAllUsers = $nexusggr->resetUserBalance();
<!-- OR -->
$resetBalanceByUsername = $nexusggr->resetUserBalance(username: 'test');
```

- Etc
```php
use Awkaay\Nexusggr\Nexusggr;

$nexusggr = new Nexusggr;
$launchGame = $nexusggr->launchGame();
$providers = $nexusggr->providers();
$games = $nexusggr->games();
$turnovers = $nexusggr->turnovers();
$currentPlayers = $nexusggr->currentPlayers();
$callScatterList $nexusggr->callScatterList();
$callScatterApply = $nexusggr->callScatterApply();
$callHistory = $nexusggr->callHistory();
$cancelCall = $nexusggr->cancelCall();
$controlUserRtp = $nexusggr->controlUserRtp();
$controlAllUsersRtp = $nexusggr->controlAllUsersRtp();
```
## Authors

- [@awkaay](https://t.me/synystergatesofolympus)

