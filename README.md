# Two Factor Totp
[![Build Status](https://travis-ci.org/ChristophWurst/twofactor_totp.svg?branch=master)](https://travis-ci.org/ChristophWurst/twofactor_totp)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ChristophWurst/twofactor_totp/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ChristophWurst/twofactor_totp/?branch=master)

Tested with the following apps:
* [OTP Authenticator](https://github.com/0xbb/otp-authenticator) (open source) which can be downloaded from [F-Droid](https://f-droid.org/repository/browse/?fdfilter=totp&fdid=net.bierbaumer.otp_authenticator) and has a built-in QR-code reader.
* [Google Authenticator](https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2) (proprietary)

## Enabling TOTP 2FA for your account
![](screenshots/settings.png)
![](screenshots/login.png)
![](screenshots/select_challenge.png)
![](screenshots/enter_challenge.png)
![](screenshots/files.png)

## Running tests
You can use the provided Makefile to run all tests by using:

    make test

This will run the PHP unit and integration tests and if a package.json is present in the **js/** folder will execute **npm run test**

Of course you can also install [PHPUnit](http://phpunit.de/getting-started.html) and use the configurations directly:

    phpunit -c phpunit.xml

or:

    phpunit -c phpunit.integration.xml

for integration tests
