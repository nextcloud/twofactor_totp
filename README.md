# Two Factor Totp
[![Build Status](https://drone.owncloud.com/api/badges/owncloud/twofactor_totp/status.svg?branch=master)](https://drone.owncloud.com/owncloud/twofactor_totp)
[![codecov](https://codecov.io/gh/owncloud/twofactor_totp/branch/master/graph/badge.svg)](https://codecov.io/gh/owncloud/twofactor_totp)

Tested with the following apps:
* [OTP Authenticator](https://github.com/0xbb/otp-authenticator) (open source) which can be downloaded from [F-Droid](https://f-droid.org/repository/browse/?fdfilter=totp&fdid=net.bierbaumer.otp_authenticator) and has a built-in QR-code reader.
* [Google Authenticator](https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2) (proprietary)

## Enabling TOTP 2FA for your account
![](https://raw.githubusercontent.com/owncloud/twofactor_totp/stable9.1/screenshots/settings.png)
![](https://raw.githubusercontent.com/owncloud/twofactor_totp/stable9.1/screenshots/verify.png)

## Running tests
You can use the provided Makefile to run all tests by using:

    make test

This will run the PHP unit and integration tests and if a package.json is present in the **js/** folder will execute **npm run test**

Of course you can also install [PHPUnit](http://phpunit.de/getting-started.html) and use the configurations directly:

    phpunit -c phpunit.xml

or:

    phpunit -c phpunit.integration.xml

for integration tests
