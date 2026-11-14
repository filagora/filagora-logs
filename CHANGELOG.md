# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [0.3.3] - 2026-11-14

### Changed

- The licence names Ian Wilson as the copyright holder. No code changes.

## [0.3.2] - 2026-11-13

### Changed

- The README's What it provides section has the same sub-sections as every other Filagora package (Overview, Tables, Commands, Events, Access, Using it). No code changes.

## [0.3.1] - 2026-11-13

### Changed

- The README follows the layout shared by every Filagora package (What it provides, Requirements, Install, Configuration, Extending, Testing, Local development, Licence), so the docs page built from it reads the same as the others. No code changes.

## [0.3.0] - 2026-11-05

### Added

- Message bodies in the email log, off by default (`bodies.enabled`). `bodies.never_store` lists the notifications whose text is left out, password resets and email verification to start with. Shown on the log's view page and included in the privacy export. New column `email_logs.body`, in the migrations and the adoption snapshot.

## [0.2.2] - 2026-10-14

### Added

- **Privacy contributions**, registered with core's registries, so the compliance package (or any host) can export, erase and prune without logs knowing it: `EmailLogContributor` (the rows that list a person's address, matched as `EmailLog::forRecipient()` does; the address is read before the account is anonymised) and a retention rule `logs.email_logs` that deletes rows older than the new setting `mail_log_retention_days` (default 60; zero keeps them). Nothing here needs `filagora-compliance`; without it nothing runs the rule.

## [0.2.1] - 2026-10-14

### Changed

- **`filagora-entitlements` is now optional.** The email log ability goes through core's `AccessChecker`; entitlements answers it when installed, a host can bind its own. The gate comes from core's `GateAbilities`, the screen uses core's `GatedByAbility`, the module now requires only `core`, and `filagora-entitlements` moves from `require` to `suggest`. Behaviour with entitlements installed is unchanged. Requires `filagora/filagora-core` `^0.2.1`.

## [0.2.0] - 2026-10-13

### Changed

- **Renamed to Filagora.** The Composer package is now `filagora/filagora-logs` (it was `zcwilt/forumkit-logs`) and the repository is `github.com/filagora/filagora-logs`. The PHP namespace is `Filagora\Logs\` (was `ForumKit\Logs\`), the config file is `config/filagora-logs.php`, the ability and its default entitlement key are `filagora.logs.view`, and the table prefix is `filagora_`. It requires `filagora/filagora-core` and `filagora/filagora-entitlements` `^0.2`. Nothing else changed: moving from 0.1 means renaming those names in your application and its config.


## [0.1.0] - 2026-10-12

First release.

- Table `email_logs`, with a schema snapshot for `forumkit:adopt`.
- A read-only Filament screen for the email log, behind the `forumkit.logs.view` ability, with a "Logs" navigation group.
- Email activity logging (`mail_log_sending` setting, off by default) and `EmailLog::forRecipient()`.
