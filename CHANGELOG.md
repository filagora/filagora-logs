# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [0.2.0] - 2026-10-13

### Changed

- **Renamed to Filagora.** The Composer package is now `filagora/filagora-logs` (it was `zcwilt/forumkit-logs`) and the repository is `github.com/filagora/filagora-logs`. The PHP namespace is `Filagora\Logs\` (was `ForumKit\Logs\`), the config file is `config/filagora-logs.php`, the ability and its default entitlement key are `filagora.logs.view`, and the table prefix is `filagora_`. It requires `filagora/filagora-core` and `filagora/filagora-entitlements` `^0.2`. Nothing else changed: moving from 0.1 means renaming those names in your application and its config.


## [0.1.0] - 2026-10-12

First release.

- Table `email_logs`, with a schema snapshot for `forumkit:adopt`.
- A read-only Filament screen for the email log, behind the `forumkit.logs.view` ability, with a "Logs" navigation group.
- Email activity logging (`mail_log_sending` setting, off by default) and `EmailLog::forRecipient()`.
