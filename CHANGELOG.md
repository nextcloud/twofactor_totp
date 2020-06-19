# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

## [0.7.0] - 2020-06-19

### Fixed

- Fix casting problem - [#182](https://github.com/owncloud/twofactor_totp/issues/182)

### Changed

- Support PHP 7.4 - [#175](https://github.com/owncloud/twofactor_totp/issues/175)
- Bump libraries

## [0.6.1] - 2019-10-17

### Added

- occ command for deleting redundant secrets - [#133](https://github.com/owncloud/twofactor_totp/pull/133)
- Php 7.3 support - [#136](https://github.com/owncloud/twofactor_totp/pull/136)

## [0.6.0] - 2019-07-11

### Added

- add validate api for totp [#38](https://github.com/owncloud/twofactor_totp/pull/38)

## [0.5.3] - 2019-03-14

### Fixed

- Disable TOTP after user deletion - [#89](https://github.com/owncloud/twofactor_totp/issues/89)

## [0.5.2] - 2018-12-03

### Changed

- Set max version to 10 because core platform is switching to Semver
- Updating QR code library - [#39](https://github.com/owncloud/twofactor_totp/issues/39)

## [0.5.1] - 2018-09-28

### Fixed

- do not allow multiple uses of the same key [#63](https://github.com/owncloud/twofactor_totp/pull/63) [#66](https://github.com/owncloud/twofactor_totp/pull/66)

## [0.5.0] - 2018-09-28

### Added

- add commands to change verified state of secret [#53](https://github.com/owncloud/twofactor_totp/pull/53)
- hu_HU translation improved [d3bbe03](https://github.com/owncloud/twofactor_totp/commit/d3bbe03976fae9467ce6197a4d2dff6f05589bf3)
- is translation improved [1ec5bae](https://github.com/owncloud/twofactor_totp/commit/1ec5baee7007933cc91d73edac58a6f51e0721b4)
- cs_CZ translation improved [4ee81d4](https://github.com/owncloud/twofactor_totp/commit/4ee81d4d8d6cd8d68b1eacf8572c02f9122d5415), [0b01b50](https://github.com/owncloud/twofactor_totp/commit/0b01b505e8373941ee6066082bb3a35101af405a),
[ec10461](https://github.com/owncloud/twofactor_totp/commit/ec104613388c3436b1dce4733bbf92f6495527c9)
- pl translation improved [b5d787c](https://github.com/owncloud/twofactor_totp/commit/b5d787c760b508a780564d654ed5bf8bb6d9e464)
- fi_FI translation improved [d8d18b](https://github.com/owncloud/twofactor_totp/commit/d8d18bcab511e12dc380498b74e250ffdf91f370)

## [0.4.4] - 2018-02-15

### Added

- Support for php7.2 [#32](https://github.com/owncloud/twofactor_totp/pull/32)

### Changed

- Enabling the second factor requires a mandatory authentication [#28](https://github.com/owncloud/twofactor_totp/pull/28)
- Adjusted application display name [#43](https://github.com/owncloud/twofactor_totp/pull/43)

## 0.4.0 – 2016-08-19

- Cloud ID, product name and URL encoded in QR code label
- Support for NC11/OC9.1

## 0.3.0 – 2016-06-09

### Added

- App is now signed

[0.7.0]: https://github.com/owncloud/twofactor_totp/compare/v0.6.1...v0.7.0
[0.6.1]: https://github.com/owncloud/twofactor_totp/compare/v0.6.0...v0.6.1
[0.6.0]: https://github.com/owncloud/twofactor_totp/compare/0.5.3...v0.6.0
[0.5.3]: https://github.com/owncloud/twofactor_totp/compare/0.5.2...v0.5.3
[0.5.2]: https://github.com/owncloud/twofactor_totp/compare/0.5.1...0.5.2
[0.5.1]: https://github.com/owncloud/twofactor_totp/compare/0.5.0...0.5.1
[0.5.0]: https://github.com/owncloud/twofactor_totp/compare/0.4.4...0.5.0
[0.4.4]: https://github.com/owncloud/twofactor_totp/compare/0.3...0.4
