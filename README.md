# Nexusggr & Telo PHP API Client

A PHP client library for integrating with the Nexusggr and Telo APIs, providing a simple interface for gaming operations.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kevindoni/nexusggr.svg?style=flat-square)](https://packagist.org/packages/kevindoni/nexusggr)
[![Total Downloads](https://img.shields.io/packagist/dt/kevindoni/nexusggr.svg?style=flat-square)](https://packagist.org/packages/kevindoni/nexusggr)
[![License](https://img.shields.io/packagist/l/kevindoni/nexusggr.svg?style=flat-square)](https://packagist.org/packages/kevindoni/nexusggr)

## Requirements

- PHP 8.2 or higher
- Composer
- PHP extensions: curl, json

## Installation

You can install the package via composer:

```shell
composer require kevindoni/nexusggr
```

### Laravel Installation

For Laravel applications, the service provider will automatically register itself.

You can publish the config file with:
```shell
php artisan vendor:publish --provider="Kevindoni\Nexusggr\NexusggrServiceProvider" --tag="config"
```

## Configuration

### Nexusggr Configuration

Configure the client using environment variables in your `.env` file:

```dotenv
NEXUSGGR_AGENT=your_agent_code
NEXUSGGR_TOKEN=your_agent_token
NEXUSGGR_ENDPOINT=https://api.nexusggr.com
```

Alternatively, you can pass configuration directly when instantiating the client:

```php
$client = new Kevindoni\Nexusggr\Nexusggr(
    'your_agent_code',
    'your_agent_token',
    'https://api.nexusggr.com'
);
```

### Telo Configuration

Configure the Telo client using environment variables in your `.env` file:

```dotenv
TELO_AGENT=your_agent_code
TELO_TOKEN=your_agent_token
TELO_ENDPOINT=https://api.telo.is/api/v2
```

Alternatively, you can pass configuration directly when instantiating the client:

```php
$client = new Kevindoni\Nexusggr\Telo(
    'your_agent_code',
    'your_agent_token',
    'https://api.telo.is/api/v2'
);
```

### Database Configuration

You can also store credentials in a database and retrieve them when instantiating the client:

```php
// Example using Laravel's DB facade
$credentials = DB::table('api_credentials')->where('name', 'nexusggr')->first();

$client = new Kevindoni\Nexusggr\Nexusggr(
    $credentials->agent_code,
    $credentials->agent_token,
    $credentials->endpoint
);

// Or update credentials later using setConfig method
$client->setConfig(
    $credentials->agent_code,
    $credentials->agent_token,
    $credentials->endpoint
);
```

Recommended database structure:

```sql
CREATE TABLE api_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    agent_code VARCHAR(100) NOT NULL,
    agent_token VARCHAR(100) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Basic Usage

### Nexusggr Usage

```php
// Initialize the client
$nexus = new Kevindoni\Nexusggr\Nexusggr();

// User management
$userInfo = $nexus->info('username');
$register = $nexus->register('new_username');
$deposit = $nexus->transaction('username', 'deposit', 10000, 'unique_id_123');

// Game operations
$providers = $nexus->providers();
$games = $nexus->games('PRAGMATIC');
$launch = $nexus->launchGame('username', 'PRAGMATIC', 'vs20doghouse', 'en');

// Advanced features
$rtp = $nexus->controlUserRtp('username', 'PRAGMATIC', 92);
$scatterList = $nexus->callScatterList('PRAGMATIC', 'vs20doghouse');
```

### Telo Usage

```php
// Initialize the client
$telo = new Kevindoni\Nexusggr\Telo();

// User management
$userInfo = $telo->info('username');
$newUser = $telo->createUser('new_username');
$deposit = $telo->deposit('username', 10000);

// Game operations
$providers = $telo->providers('slot');
$games = $telo->games('PRAGMATIC');
$launch = $telo->launchGame('username', 'PRAGMATIC', 'vs20doghouse');

// Advanced features
$rtp = $telo->setUserRtp('PRAGMATIC', 'username', 92);
$callList = $telo->callList('PRAGMATIC', 'vs20doghouse', 'username');
```

## Available Methods

### Nexusggr Methods

#### User Management

- `register(string $username)` - Register a new user
- `info(?string $username = null)` - Get user account information
- `transaction(string $username, string $type, int $amount, ?string $uniqueId = null)` - Execute deposit or withdrawal
- `resetUserBalance(?string $username)` - Reset user balance to 0
- `transferStatus(string $username, string $uniqueId)` - Check transaction status

#### Game Operations

- `providers()` - Get list of available game providers
- `games(string $provider)` - Get list of games for a specific provider
- `launchGame(string $username, string $provider, string $game, ?string $language = null, ?array $additionalParams = null)` - Launch a game
- `turnovers(string $username, string $gameType = 'slot', ?string $startDate = null, ?string $endDate = null, int $page = 0, int $perPage = 1000)` - Get game history

#### Scatter Controls

- `currentPlayers()` - Get list of currently playing users
- `callScatterList(string $provider, string $game)` - Get list of available scatter calls
- `callScatterApply(string $username, string $provider, string $game, int $rtp, int $type)` - Apply a scatter call
- `callHistory(int $offset = 0, int $limit = 100)` - Get call history
- `cancelCall(int $callId)` - Cancel a pending call

#### RTP Controls

- `controlUserRtp(string $username, string $provider, int $rtp)` - Set RTP for specific user
- `controlAllUsersRtp(int $rtp)` - Set RTP for all users

### Telo Methods

#### User Management

- `createUser(string $username, ?int $depositAmount = null)` - Create a new user with optional initial deposit
- `info(?string $username = null)` - Get agent and user information
- `deposit(string $username, int $amount)` - Deposit funds to user account
- `withdraw(string $username, ?int $amount = null)` - Withdraw funds from user account
- `withdrawAll()` - Withdraw all users' balances

#### Game Operations

- `providers(string $gameType = 'slot')` - Get list of game providers by type (slot or casino)
- `games(string $provider, ?string $language = null)` - Get list of games for a specific provider
- `launchGame(string $username, string $provider, string $game, string $gameType = 'slot', ?string $language = null, ?int $depositAmount = null)` - Launch a game
- `getGameLogByDate()` - Get game history by date range
- `getGameLogById(int $lastHistoryId, string $gameType = 'slot', ?string $username = null)` - Get game history by ID
- `getExchangeHistory()` - Get payment transaction history
- `getLogDetail(string $provider, string $roundId)` - Get detailed game log

#### Call Controls

- `currentPlayers()` - Get list of currently playing users
- `callList(string $provider, string $game, string $username, int $callType = 1)` - Get list of available calls
- `callApply(string $provider, string $game, string $username, int $callRtp, int $callType = 1)` - Apply a call
- `callHistory(int $offset = 0, int $limit = 100, ?int $lastCallId = null, string $orderDir = 'DESC')` - Get call history
- `callCancel(int $callId)` - Cancel a pending call

#### RTP Controls

- `setAgentRtp(int $rtp)` - Set RTP for the agent
- `setUserRtp(string $provider, string $username, int $rtp)` - Set RTP for a specific user

## Error Handling

The API returns responses with status codes:
- `status: 1` - Success
- `status: 0` - Failure

Example error handling:

```php
$response = $nexus->transaction('username', 'deposit', 10000);
if (!isset($response['status']) || $response['status'] !== 1) {
    // Handle error
    $errorMessage = $response['msg'] ?? 'Unknown error';
    // Log or display error message
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Authors

- [@kevindoni](https://t.me/synystergatesofolympus)

