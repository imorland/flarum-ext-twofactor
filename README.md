# 2FA

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/ianm/twofactor.svg)](https://packagist.org/packages/ianm/twofactor) [![Total Downloads](https://img.shields.io/packagist/dt/ianm/twofactor.svg)](https://packagist.org/packages/ianm/twofactor)

A [Flarum](http://flarum.org) extension. 2FA for Flarum

## Requirements

This extension requires a minimum of PHP 8.1, due to a 3rd party library constraint.

## Features

- Enforces `admin` accounts to have 2FA enabled for increased security
- Configure which additional user groups should also be enforced
- Supports all common authentication apps
- Protects `login`, `forgot password` endpoints
- Integrates with `fof/oauth` to protect OAuth logins to protected accounts
- 2FA Enabled/Disabled notifications
- 2FA Status page
- Backup/recovery codes
- Option to revoke dormant access tokens after X days of no usage

## Permissions

This extension provides the ability to view the status of 2FA of other users (intended for admin and/or moderator use). In order for this to function correctly, you must also set the permission `Moderate Access Tokens` to at least the same group as you require for `View 2FA status of other users`.

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

### CLI
Independantly of the setting, you may remove dormant access tokens using the CLI. The days setting defaults to 30 days, and the CLI will still respect this value from the extension settings, as well as the developer token setting:
```bash
php flarum twofactor:kill-inactive-tokens
```

![cli](https://private-user-images.githubusercontent.com/16573496/339968512-ef914d1e-cc65-4621-9484-4b8eeb7642f9.png?jwt=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJnaXRodWIuY29tIiwiYXVkIjoicmF3LmdpdGh1YnVzZXJjb250ZW50LmNvbSIsImtleSI6ImtleTUiLCJleHAiOjE3MTg0NDE1ODQsIm5iZiI6MTcxODQ0MTI4NCwicGF0aCI6Ii8xNjU3MzQ5Ni8zMzk5Njg1MTItZWY5MTRkMWUtY2M2NS00NjIxLTk0ODQtNGI4ZWViNzY0MmY5LnBuZz9YLUFtei1BbGdvcml0aG09QVdTNC1ITUFDLVNIQTI1NiZYLUFtei1DcmVkZW50aWFsPUFLSUFWQ09EWUxTQTUzUFFLNFpBJTJGMjAyNDA2MTUlMkZ1cy1lYXN0LTElMkZzMyUyRmF3czRfcmVxdWVzdCZYLUFtei1EYXRlPTIwMjQwNjE1VDA4NDgwNFomWC1BbXotRXhwaXJlcz0zMDAmWC1BbXotU2lnbmF0dXJlPTYyMjA5NmMzMzg1ZjJhMzdjMDRmNGI1ZGQyNDVjM2U4NjRlYjIxZWE3NDQ2ODI5NDkyNzk0ZjE4YjE4NWI0ZTMmWC1BbXotU2lnbmVkSGVhZGVycz1ob3N0JmFjdG9yX2lkPTAma2V5X2lkPTAmcmVwb19pZD0wIn0.Bb8Wgml7ohvCF7m89EXhRhNBS7zo95tDx777g5gj8vg)

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
- [Discuss](https://discuss.flarum.org/d/33339)

## Support

Please consider supporting my extension development and maintenance work.

<a href="https://www.buymeacoffee.com/ianm1" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/default-orange.png" alt="Buy Me A Coffee" height="41" width="174"></a>
