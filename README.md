# Filagora Logs

An email log for Filagora, as a Laravel 13 / Filament 5 plugin. Requires
[`filagora/filagora-core`](https://github.com/filagora/filagora-core). Who may open the screen is
answered by core's `AccessChecker`: install
[`filagora/filagora-entitlements`](https://github.com/filagora/filagora-entitlements) and it is
answered from entitlement grants, or bind your own checker. With neither, nobody may.

> The package is at `0.1`: the API may change between `0.x` minor versions.

## What it does

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
- **Table** (`email_logs`, prefixed `filagora_` by default) matches an existing site's index for
  index, so `php artisan filagora:adopt` accepts it.

An audit log viewer is planned but not part of this release.

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

## Testing

```bash
composer test
```

Tests run against a real MariaDB database named `testing`.

## Licence

MIT. See [LICENSE](LICENSE).
