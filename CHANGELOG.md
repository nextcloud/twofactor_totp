<!--
  - SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# Changelog
Notable changes in [changelog format](https://keepachangelog.com/en/1.0.0/), project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html)

## UNRELEASED



## 3.0.4-beta.1 (2025-08-29)

### Added

- Support for Nextcloud 32

### Fixed

- Migration errors when updating from v2 to v3

### Removed

- Support for Nextcloud <=31

## 3.0.3-beta.2 (2025-08-19)

### Added

- If twofactor_email version 2.x was installed before, user settings are now migrated
- Support for enabling the provider for users via OCC command
- Support for Nextcloud 32 (but broken)

### Changed

- twofactor_email versions 3.0.0-dev - 3.0.2-dev used their own database table.
  This table is dropped. When updating from these dev versions, pending codes are lost.

### Removed

- Support for Nextcloud <=30
- Support for PHP 8.0

## 3.0.2-dev (2024-12-05)

### Added

- Support for Nextcloud 29-31 (tested against 30.0.3)
- Support for PHP 8.4

### Removed

- Support for Nextcloud <=28

## 3.0.0-dev (2024-04-21)

### Added

- Users can set up the two-factor e-mail provider at login if 2FA is enforced

### Changed

- Complete rewrite, based on twofactor_totp 11.0.0-dev and twofactor_email 2.7.4
