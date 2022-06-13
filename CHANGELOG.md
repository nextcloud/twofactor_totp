# Changelog
All notable changes to this project will be documented in this file.

## 6.4.0 – 2022-06-14
### Added
- Nextcloud 25 support
- PHP8.1 support
### Changed
- Updated dependencies
- New and updated translations
### Removed
- Nextcloud 21 support (EOL)
- PHP7.3 support (EOL)

## 6.3.0-beta1 - 2022-04-19
### Added
- Nextcloud 24 support
### Changed
- Updated dependencies

## 6.3.0-alpha1 - 2022-03-30
### Fixed
- Set autocomplete attribute of input field to one-time-code 
- Fix the js build
### Changed
- Updated dependencies

## 6.2.0 – 2021-12-01
### Added
- Nextcloud 23 support
### Changed
- Updated dependencies

## 6.1.0 – 2021-06-24
### Added
- Nextcloud 22 support
### Changed
- New and updated translations
- Updated dependencies

## 6.0.0 – 2021-01-25
### Added
- Nextcloud 21 support
### Changed
- New and updated translations
- Updated dependencies
### Removed
- Nextcloud 18-20 support

## 5.0.0 – 2020-08-26
### Added
- Nextcloud 20 support
### Changed
- New and updated translations
- Updated dependencies
### Removed
- Nextcloud 17 support

## 4.1.3 – 2020-03-19
### Changed
- New and updated translations
- Updated dependencies

## 4.1.2 – 2020-01-07
### Changed
- New and updated translations
- Updated dependencies

## 4.1.1 – 2019-12-12
### Changed
- New and updated translations
- Updated dependencies
### Fixed
- JavaScript vulnerabilities in `mem` and `serialize-javascript` dependencies

## 4.1.0 – 2019-12-02
### Added
- Nextcloud 18 support
- php7.4 support
### Changed
- New and updated translations
- Updated dependencies

## 4.0.0 – 2019-08-26
### Added
- Ability to set up during login
### Changed
- New and updated translations
### Removed
- Nextcloud 16 support

## 3.0.1 – 2019-08-07
### Fixed
- Numeric overflow on 32bit php installations

## 3.0.0 – 2019-08-06
### Added
- Nextcloud 17 support
### Fixed
- Updated `lodash` vulnerability
### Changed
- Updated translations
- Updated dependencies
### Removed
- Nextcloud 15 support

## 2.1.2 – 2019-03-07
### Fixed
- Initial migration for outdated database schemas
- App name typo
### Changed
- Updated translations
- Updated dependencies

## 2.1.1 – 2019-02-12
### Fixed
- IE11 compatibility
- Updated vulnerable `lodash` dependency
- Fixed old settings page registrations causing log spam
- Packaging of .git directory inside `vendor`

## 2.1.0 – 2018-12-12
### Added
- Ability to disable provider via `occ twofactor:disable <uid> totp`
- Support for Nextcloud 16
- Support for php 7.3
- New and updated translations
### Fixed
- Password confirmation on IE and other outdated browsers
- Removed unused assets in release tarball

## 2.0.1 – 2018-11-19
### Fixed
- Handle/log rejected password confirmation
### Changed
- New and updated translations

## 2.0.0 – 2018-11-09
### Added
- Nextcloud 15 support
- New personal settings page (consolidated with other 2FA providers)
### Removed
- Nextcloud 14 support

## 1.5.0 – 2018-08-02
### Added
- Nextcloud 14 support
### Changed
- App requires php7+
### Fixed
- Provider registration (requires Beta 2+)

## 1.4.1 – 2018-01-09
### Added
- New and updated translations

## 1.4.0 – 2017-12-13
### Added
- Nextcloud 13 support
- Php7.2 support
- Translations
### Fixed
- List settings in security section
- Remove whitespaces from OTP code

## 1.3.1 – 2017-08-14
### Added
- Translations
### Fixed
- Activity type for 2FA activies

## 1.3.0 – 2017-05-02
### Added
- Confirmation before enabling
- Translations
### Fixed
- Client-side js error on personal page due to wrong js namespacing
- Icon on personal settings page

## 1.2.0 – 2017-04-03
### Added
- Settings icon (NC12 only)
### Changed
- Dedicated login button
- Translations

## 1.1.0 – 2017-02-06
### Added
- App icon
- Translations

## 1.0.0 – 2017-01-23
### Added
- Nextcloud 12 support
- php7.1 support
- Password confirmation when enabling/disabling the provider
- Publish events to two-factor activities stream (Nextcloud 12 only)

### Changed
- Vendor neutral provider name

## 0.5 – 2016-11-25
### Added
- Support NC11
### Changed
- php 5.6-7.0

## 0.4.0 – 2016-08-19
### Added
- Cloud ID, product name and URL encoded in QR code label
- Support for NC10/OC9.1

## 0.3.0 – 2016-06-09
### Added
- App is now signed
