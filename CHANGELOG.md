# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

- Table `email_logs`, with a schema snapshot for `forumkit:adopt`.
- A read-only Filament screen for the email log, behind the `forumkit.logs.view` ability, with a "Logs" navigation group.
- Email activity logging (`mail_log_sending` setting, off by default) and `EmailLog::forRecipient()`.
