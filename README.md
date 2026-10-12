# ForumKit Logs

An audit log viewer and an email log for ForumKit, as a Laravel 13 / Filament 5 plugin. Requires
[`zcwilt/forumkit-core`](https://github.com/zcwilt/forumkit-core) and
[`zcwilt/forumkit-entitlements`](https://github.com/zcwilt/forumkit-entitlements).

> The package is at `0.1`: the API may change between `0.x` minor versions.

## What it does

- **Audit log.** Reads the `audits` table that
  [`owen-it/laravel-auditing`](https://github.com/owen-it/laravel-auditing) writes: a read-only
  Filament screen listing who changed what and when (event, model, actor, IP address), with
  filters by event and model, and a detail page showing the old and new values. Make a model
  auditable with that package's `Auditable` trait; the package points `audit.implementation` at
  its own `Audit` model, which uses the prefixed table. A model you set there yourself is kept.
- **Email log.** When the `mail_log_sending` setting is on (it is off by default), every
  outgoing message is recorded: recipients (to, cc, bcc), subject, mailer, the notification class
  that sent it, and its status (`sending`, then `sent` with the message id). Logging never blocks
  the mail: a failure to log is reported to the application log and the message goes out. The
  `EmailLog::forRecipient($address)` scope finds a person's rows (given spelling and lowercase;
  a blank address matches nothing), for privacy export and erasure.
- **Access.** Both screens are read-only and need the `forumkit.logs.view` ability, mapped in
  `config/forumkit-logs.php` to the entitlement of the same name. Point it at your own key there.
- **Tables** (`audits`, `email_logs`, prefixed `forumkit_` by default) match an existing site's
  index for index, so `php artisan forumkit:adopt` accepts them.

Recording changes to a particular package's models (settings, entitlements, forums) is not done
here; add `Auditable` to your own subclasses of those models.

## Install

```bash
composer require zcwilt/forumkit-logs
php artisan migrate
php artisan forumkit:entitlements:sync
```

```php
->plugin(\ForumKit\Logs\ForumKitLogsPlugin::make())
```

Then turn on the "Log Email Sending Activity" setting if you want the email log.

## Testing

```bash
composer test
```

Tests run against a real MariaDB database named `testing`.

## Licence

MIT. See [LICENSE](LICENSE).
