# Two Factor Totp
[![Build Status](https://travis-ci.org/ChristophWurst/twofactor_totp.svg?branch=master)](https://travis-ci.org/ChristophWurst/twofactor_totp)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ChristophWurst/twofactor_totp/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ChristophWurst/twofactor_totp/?branch=master)

Tested with the following apps:
* [OTP Authenticator](https://github.com/0xbb/otp-authenticator) (open source) which can be downloaded from [F-Droid](https://f-droid.org/repository/browse/?fdfilter=totp&fdid=net.bierbaumer.otp_authenticator) and has a built-in QR-code reader.
* [Google Authenticator](https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2) (proprietary)

## Enabling TOTP 2FA for your account
![](https://cloud.githubusercontent.com/assets/1374172/16909141/ea427288-4cd0-11e6-9c43-1c718759992d.png)
![](https://cloud.githubusercontent.com/assets/1374172/16909103/8f424296-4cd0-11e6-8d81-2678e6f75c76.png)
![](https://cloud.githubusercontent.com/assets/1374172/16909104/8f68d28a-4cd0-11e6-8c9a-ab0afe49c0f2.png)
![](https://cloud.githubusercontent.com/assets/1374172/16909116/abd01a96-4cd0-11e6-9c0f-0c7fa6ddd0e7.png)
![](https://cloud.githubusercontent.com/assets/1374172/16909105/8f6d5b34-4cd0-11e6-8ad0-0433441007d9.png)

## Running tests
You can use the provided Makefile to run all tests by using:

    make test

This will run the PHP unit and integration tests and if a package.json is present in the **js/** folder will execute **npm run test**

Of course you can also install [PHPUnit](http://phpunit.de/getting-started.html) and use the configurations directly:

    phpunit -c phpunit.xml

or:

    phpunit -c phpunit.integration.xml

for integration tests
