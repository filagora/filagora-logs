# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

- Tables `audits` (the owen-it/laravel-auditing shape) and `email_logs`, with a schema snapshot for `forumkit:adopt`.
- Read-only Filament screens for the audit log and the email log, behind the `forumkit.logs.view` ability, with a "Logs" navigation group.
- Email activity logging (`mail_log_sending` setting, off by default) and `EmailLog::forRecipient()`.
- `Audit` model that uses the prefixed table, set as `audit.implementation` unless the host chose one.
