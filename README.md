# Filagora Logs

An email log for Filagora, as a Laravel 13 / Filament 5 plugin. Requires
[`filagora/filagora-core`](https://github.com/filagora/filagora-core). Who may open the screen is
answered by core's `AccessChecker`: install
[`filagora/filagora-entitlements`](https://github.com/filagora/filagora-entitlements) and it is
answered from entitlement grants, or bind your own checker. With neither, nobody may.

> The package is at `0.1`: the API may change between `0.x` minor versions.

## What it provides

- **Email log.** A read-only Filament screen over every outgoing message. When the `mail_log_sending` setting is on (it is off by default), every
  outgoing message is recorded: recipients (to, cc, bcc), subject, mailer, the notification class
  that sent it, and its status (`sending`, then `sent` with the message id). Logging never blocks
  the mail: a failure to log is reported to the application log and the message goes out. The
  `EmailLog::forRecipient($address)` scope finds a person's rows (given spelling and lowercase;
  a blank address matches nothing), for privacy export and erasure.
- **Access.** The screen is read-only and needs the `filagora.logs.view` ability, mapped in
  `config/filagora-logs.php` to a key of the same name (an entitlement, when entitlements is
  installed). Point it at your own key there.
- **Privacy.** Logs registers a `PrivacyContributor` (the rows that list a person's address, for
  an export or an erasure) and a retention rule that deletes rows older than the setting
  `mail_log_retention_days` (60 by default) with core. With `filagora-compliance` installed, privacy
  requests and `filagora:compliance:prune` use them.
- **Message bodies** are off by default (`bodies.enabled` in `config/filagora-logs.php`). When on, the
  log keeps each message's text and shows it on the view page and in the privacy export.
  `bodies.never_store` lists the notifications whose text is never kept, because it carries a live
  token or signed link or private content; password reset and email verification are listed, and a
  site must add its own.
- **Table** (`email_logs`, prefixed `filagora_` by default) matches an existing site's index for
  index, so `php artisan filagora:adopt` accepts it.

An audit log viewer is planned but not part of this release.

## Requirements

PHP 8.4, Laravel 13 and Filament 5. Tested against MariaDB 11.4. Requires [`filagora/filagora-core`](https://github.com/filagora/filagora-core).

## Install

```bash
composer require filagora/filagora-logs
php artisan migrate
php artisan filagora:entitlements:sync   # only with entitlements installed
```

```php
->plugin(\Filagora\Logs\FilagoraLogsPlugin::make())
```

Then turn on the "Log Email Sending Activity" setting if you want the email log.

## Configuration

`config/filagora-logs.php` is merged with the host's copy, so list only what
differs. Publish it with `php artisan vendor:publish --tag=filagora-logs-config`.

## Extending

Extend through documented contracts, events and registries. Anything not documented
is `@internal`.

## Testing

```bash
composer test
```

Tests run against a real MariaDB, never SQLite, so engine behaviour (FULLTEXT,
`ONLY_FULL_GROUP_BY`, collation) matches production. Host and credentials come from
your environment; the database is always named `testing`, never your development one.

## Local development (DDEV)

`.ddev/` is included. `ddev start` creates both `db` and `testing` (run `ddev restart` once
if `testing` is missing), then `ddev exec vendor/bin/pest`.

## Licence

MIT. See [LICENSE](LICENSE).
