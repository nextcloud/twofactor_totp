# Two-Factor Email Provider for Nextcloud

[Nextcloud](https://nextcloud.com/) supports web logins with [two factor authentication](https://en.wikipedia.org/wiki/Multi-factor_authentication#Factors) (2FA). To support a certain type of 2nd factor, an add-on server-app "2FA provider" must be installed. It will replace the current implementation of Two-Factor Email Provider for Nextcloud (see "State of the app" below).

It kicks in after the primary authentication stage (typically username and password). It challenges the user to enter an authentication code (aka one-time password, OTP, currently 6 digits) - a code that is randomly generated and sent to the user's primary email address by this [Nextcloud App (category Security)](https://apps.nextcloud.com/categories/security).

## Installation, activation and usage

The app must be installed and activated by a Nextcloud server admin. The easiest way to do so will soon be to select "Apps" from the menu and search for "two", then select and install it – which will retrieve it from the [App Store](https://apps.nextcloud.com/apps/twofactor_email).

Users may enable any installed 2FA app. Upon login, they may choose from all enabled 2FA apps. It cannot be enabled if no email address is set in 'Personal info'. We refrained from the possibility to be able to set an alternate address. You are free to contribute code that implements is. Make sure it doesn't break existing functionality though.

Admins may enforce 2FA for certain or all users. This is a Nextcloud setting and not specific to this app. If enforced, users are prompted enable any installed 2FA app. If the admin installs this app AND enforces 2FA AND makes sure that each user does have a valid e-mail address, then it is sufficient to use that admin setting. Additionaly, admins with console access may use a script to enable this Twofactor Provider via OCC. 

Mind that, once you enable 2FA, you can no longer use your passwort in applications that don't support the web based 2FA login flow. For them, you need to create and use [app passwords](https://docs.nextcloud.com/server/stable/user_manual/en/session_management.html#managing-devices).

## Building yourself and call for help

To build the app, check out the repo and use [krankerl](https://github.com/ChristophWurst/krankerl/) package` or follow these steps:

* `composer i --no-dev`
* `npm ci`
* `npm run build` or `npm run dev` [more info](https://docs.nextcloud.com/server/latest/developer_manual/digging_deeper/npm.html)

Any offers to help are welcome, whether it's development knowledge, refactoring to fully adhere SOLID principles, better test coverage or implemeting new features, but also good documentation, examples, security audits, translations / integration if transation tools, etc. Please contact [the dev team](https://github.com/datenschutz-individuell/twofactor_email/wiki/Developer-notes).

## State of the app

This version 3 ("v3") of the currently official [twofactor_email](https://github.com/nursoda/twofactor_email/) app version 2 ("v2") (available in the [Nextcloud App Store](https://apps.nextcloud.com/apps/twofactor_email)) is meant to someday soon replace v2. v3 is based on [twofactor_totp](https://github.com/nextcloud/twofactor_totp/). My idea was to only modify what's necessary and to cherrypick all changes that reflect the Nextcloud framwork changes. It turned out, that this may not have been my best idea. The code is merely stable now, thus lacking some polishing. This is NOT production quality yet, but public beta!

This new app now migrates existing v2 settings to v3. Thus, there should be little user disturbance. Nevertheless, the look slightly changed, and behaviour changed for edge cases. I will only release it in the app store when I am pretty sure it doesn't break things.

If you have deeper Nextcloud framework knowledge and ideas for features, I suggest you rather create PRs here in v3 than in v2. If you know how to replace vue2 and the dependencies it pulls, please preferrably help Nextcloud to get rid of vue2 altogether. If you know how to switch to vue3 for this v3 app only, please create a PR. My biggest concern is to be able to build this app without ANY security warnings for all officially supported Nextcloud versions.

I try to review PRs timely. I will also make sure that there's a v2 release for all officially supported Nextcloud versions until this app is released.
