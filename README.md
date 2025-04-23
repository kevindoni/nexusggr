# Nexusggr PHP API Client

A PHP client library for integrating with the Nexusggr API.

## Installation

```bash
composer require awkaay/nexusggr
```

## Configuration

Configure the client using environment variables in your `.env` file:

```
NEXUSGGR_AGENT=your_agent_code
NEXUSGGR_TOKEN=your_agent_token
NEXUSGGR_ENDPOINT=https://api.nexusggr.com
```

Alternatively, you can pass configuration directly when instantiating the client:

```php
$client = new Awkaay\Nexusggr\Nexusggr(
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

$client = new Awkaay\Nexusggr\Nexusggr(
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
$nexus = new Awkaay\Nexusggr\Nexusggr();

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

- User Management: `register()`, `info()`, `transaction()`, `resetUserBalance()`, `transferStatus()`
- Game Operations: `providers()`, `games()`, `launchGame()`, `turnovers()`
- Scatter Controls: `currentPlayers()`, `callScatterList()`, `callScatterApply()`, `callHistory()`, `cancelCall()`
- RTP Controls: `controlUserRtp()`, `controlAllUsersRtp()`

For detailed documentation, please refer to the official Nexusggr API documentation.

## Authors

- [@awkaay](https://t.me/synystergatesofolympus)

