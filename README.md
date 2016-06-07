# Two Factor Totp
[![Build Status](https://travis-ci.org/ChristophWurst/twofactor_totp.svg?branch=master)](https://travis-ci.org/ChristophWurst/twofactor_totp)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ChristophWurst/twofactor_totp/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ChristophWurst/twofactor_totp/?branch=master)

Tested with the following apps:
* [OTP Authenticator](https://github.com/0xbb/otp-authenticator) (open source) which can be downloaded from [F-Droid](https://f-droid.org/repository/browse/?fdfilter=totp&fdid=net.bierbaumer.otp_authenticator) and has a built-in QR-code reader.
* [Google Authenticator](https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2) (proprietary)

## Enabling TOTP 2FA for your account
![](https://cloud.githubusercontent.com/assets/1374172/15863012/c3b0401a-2cd1-11e6-92d8-50f7e51425d3.png)
![](https://cloud.githubusercontent.com/assets/1374172/15863011/c3ae589a-2cd1-11e6-8e94-66c0369a76d4.png)
![](https://cloud.githubusercontent.com/assets/1374172/15863014/c3d7bbae-2cd1-11e6-9bf9-3f1917d13598.png)
![](https://cloud.githubusercontent.com/assets/1374172/15863013/c3d7cfe0-2cd1-11e6-9694-5d872d30f7f1.png)


## Running tests
You can use the provided Makefile to run all tests by using:

    make test

This will run the PHP unit and integration tests and if a package.json is present in the **js/** folder will execute **npm run test**

Of course you can also install [PHPUnit](http://phpunit.de/getting-started.html) and use the configurations directly:

    phpunit -c phpunit.xml

or:

    phpunit -c phpunit.integration.xml

for integration tests
