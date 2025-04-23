# Nexusggr PHP API Client

A PHP client library for integrating with the Nexusggr API, providing a simple interface for gaming operations.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kevindoni/nexusggr.svg?style=flat-square)](https://packagist.org/packages/kevindoni/nexusggr)
[![Total Downloads](https://img.shields.io/packagist/dt/kevindoni/nexusggr.svg?style=flat-square)](https://packagist.org/packages/kevindoni/nexusggr)
[![License](https://img.shields.io/packagist/l/kevindoni/nexusggr.svg?style=flat-square)](https://packagist.org/packages/kevindoni/nexusggr)

## Requirements

- PHP 8.2 or higher
- Composer

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

## Available Methods

### User Management

- `register(string $username)` - Register a new user
- `info(?string $username = null)` - Get user account information
- `transaction(string $username, string $type, int $amount, ?string $uniqueId = null)` - Execute deposit or withdrawal
- `resetUserBalance(?string $username)` - Reset user balance to 0
- `transferStatus(string $username, string $uniqueId)` - Check transaction status

### Game Operations

- `providers()` - Get list of available game providers
- `games(string $provider)` - Get list of games for a specific provider
- `launchGame(string $username, string $provider, string $game, ?string $language = null, ?array $additionalParams = null)` - Launch a game
- `turnovers(string $username, string $gameType = 'slot', ?string $startDate = null, ?string $endDate = null, int $page = 0, int $perPage = 1000)` - Get game history

### Scatter Controls

- `currentPlayers()` - Get list of currently playing users
- `callScatterList(string $provider, string $game)` - Get list of available scatter calls
- `callScatterApply(string $username, string $provider, string $game, int $rtp, int $type)` - Apply a scatter call
- `callHistory(int $offset = 0, int $limit = 100)` - Get call history
- `cancelCall(int $callId)` - Cancel a pending call

### RTP Controls

- `controlUserRtp(string $username, string $provider, int $rtp)` - Set RTP for specific user
- `controlAllUsersRtp(int $rtp)` - Set RTP for all users

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

