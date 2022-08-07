# Data Token

<p align="center">
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-lightgrey.svg" alt="License"></a>
<a href="https://packagist.org/packages/attla/data-token"><img src="https://img.shields.io/packagist/v/attla/data-token" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/attla/data-token"><img src="https://img.shields.io/packagist/dt/attla/data-token" alt="Total Downloads"></a>
</p>

🪅 Turn everything into a token: randomized or predictable.

## Installation

```bash
composer require attla/data-token
```

## Usage

Creating and managing a token

```php

use Attla\DataToken\Facade as DataToken;

// Create with facade
$token = DataToken::secrete('your secret phrase');

// Set the issuer claim
$token->iss();

// Set the expiration in minutes
$token->exp(120);

// Set payload of the token
$token->payload($model);

// Set the browser identifier on token
$token->bwr();

// Set user IP on the token
$token->ip();

// Get the token
$tokenEncoded = $token->encode();

```

Decoding the token

```php

// Get token value as associative array
$tokenValue = DataToken::decode($tokenEncoded, true);

// Aliases for decode a token
$tokenValue = DataToken::fromString($tokenEncoded);
$tokenValue = DataToken::parseString($tokenEncoded);
$tokenValue = DataToken::parse($tokenEncoded);

```

Others ways to make a token

```php

// Make a unique token from anything
$id = DataToken::id(123);

// Make always the same token
$sid = DataToken::sid(123);

// Make a strong token
$sign = DataToken::sign(123);

```

### List of message methods

| Method | Parameters | Description |
|--|--|--|
| ``encode()`` | - | Encode the token |
| ``decode(data, assoc)`` | String, Boolean | Decode the token, will be return false if as invalid |
| ``fromString(data, assoc)`` | String, Boolean | Alias for ``decode()`` |
| ``parseString(data, assoc)`` | String, Boolean | Alias for ``decode()`` |
| ``parse(data, assoc)`` | String, Boolean | Alias for ``decode()`` |
| ``iss()`` | - | Define the token issuer claim |
| ``exp(minutes)`` | Integer|CarbonInterface | Time to expire the token |
| ``payload(content)`` | Mixed | Set the content of the token, that can be a model, array, object, or anything, but can't be false |
| ``bwr()`` | - | Set the browser identifier to the token |
| ``ip()`` | - | Set the IP address from user to the token |
| ``body()`` | - | Alias for ``payload()`` |
| ``same()`` | - | Make the token always the same |
| ``sign(minutesExpiration)`` | Integer|CarbonInterface | Create a strong validation of the token |
| ``id(data)`` | Mixed | Generate a unique identifier of anything |
| ``sid(data)`` | - | Always generate the same identifier of anything |
| ``getEntropy()`` | - | Get entropy of the token |

## License

This package is licensed under the [MIT license](LICENSE) © [Octha](https://octha.com).
