# Two Factor Totp
[![Build Status](https://travis-ci.org/ChristophWurst/twofactor_totp.svg?branch=master)](https://travis-ci.org/ChristophWurst/twofactor_totp)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ChristophWurst/twofactor_totp/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ChristophWurst/twofactor_totp/?branch=master)

Works with [OTP Authenticator](https://github.com/0xbb/otp-authenticator) which can be downloaded from [F-Droid](https://f-droid.org/repository/browse/?fdfilter=totp&fdid=net.bierbaumer.otp_authenticator).

## Enabling TOTP 2FA for your account
![](https://cloud.githubusercontent.com/assets/1374172/15801137/05c40164-2a8c-11e6-843e-d48b511dffc7.png)
![](https://cloud.githubusercontent.com/assets/1374172/15801138/05c54ea2-2a8c-11e6-9df4-bf8ef13391c9.png)
![](https://cloud.githubusercontent.com/assets/1374172/15801141/05ccbafc-2a8c-11e6-86a5-0f9bbfdd9a36.png)
![](https://cloud.githubusercontent.com/assets/1374172/15801139/05c75af8-2a8c-11e6-9adf-7820bece4965.png)
![](https://cloud.githubusercontent.com/assets/1374172/15801140/05c8c21c-2a8c-11e6-80de-c85faa851826.png)

## Running tests
You can use the provided Makefile to run all tests by using:

    make test

This will run the PHP unit and integration tests and if a package.json is present in the **js/** folder will execute **npm run test**

Of course you can also install [PHPUnit](http://phpunit.de/getting-started.html) and use the configurations directly:

    phpunit -c phpunit.xml

or:

    phpunit -c phpunit.integration.xml

for integration tests
