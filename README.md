# 2FA

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/ianm/twofactor.svg)](https://packagist.org/packages/ianm/twofactor) [![Total Downloads](https://img.shields.io/packagist/dt/ianm/twofactor.svg)](https://packagist.org/packages/ianm/twofactor)

A [Flarum](http://flarum.org) extension. 2FA for Flarum

## Features

- Enforces `admin` accounts to have 2FA enabled for increased security
- Configure which additional user groups should also be enforced
- Supports all common authentication apps
- Protects `login`, `forgot password` endpoints
- Integrates with `fof/oauth` to protect OAuth logins to protected accounts
- 2FA Enabled/Disabled notifications
- 2FA Status page
- Backup/recovery codes

## Installation

Install with composer:

```sh
composer require ianm/twofactor:"*"
```

## Updating

```sh
composer update ianm/twofactor
php flarum migrate
php flarum cache:clear
```

## Usage

TODO

## Screenshots
##### QR Code setup
![qr-code-setup](https://github.com/imorland/flarum-ext-twofactor/assets/16573496/dbf6c4d1-ac1f-4de9-b966-0065529e8edf)
##### Manual setup
![manual setup](https://github.com/imorland/flarum-ext-twofactor/assets/16573496/555078cc-041d-4ac3-a0c9-b3ed87ca181b)
##### Security tab integration
![security tab integration](https://github.com/imorland/flarum-ext-twofactor/assets/16573496/f0247afe-16f4-4ccf-b09c-0637d7b9c783)

##### Enabled/Disabled notifications
![notifications](https://github.com/imorland/flarum-ext-twofactor/assets/16573496/d5c052e2-bb08-4c3a-a24d-ac8c4b9e3f57)

##### Admin user list status icon
![userlist](https://github.com/imorland/flarum-ext-twofactor/assets/16573496/9c1a58c9-919b-4552-ad1f-f022a5240f17)
## Links

- [Packagist](https://packagist.org/packages/ianm/twofactor)
- [GitHub](https://github.com/imorland/flarum-ext-twofactor)
- [Discuss](https://discuss.flarum.org/d/PUT_DISCUSS_SLUG_HERE)
