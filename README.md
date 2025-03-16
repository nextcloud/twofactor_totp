# Twofactor Email

This two factor authentication provider for Nextcloud creates a 6-digit random authentication code and sends it to the user's primary email address.

## Installation, activation and usage

The app must be installed and activated by a Nextcloud server admin. The easiest way to do so is to select "Apps" from the menu and search for "two", then select and install it – which will retrieve it from the [App Store](https://apps.nextcloud.com/apps/twofactor_email).

Users may enable any installed 2FA app. Upon login, they may choose from all enabled 2FA apps. It cannot be enabled if no email address is set in 'Personal info'. In the future, the app might be enhanced to allow using alternate address set there.

Admins may enforce 2FA for certain or all users. If enforced, users are prompted enable any installed 2FA app. In the future, the app might be enhanced to allow admins to enable Twofactor Email for new or even existing users. 

Mind that, once you enable 2FA, you can no longer use your passwort in applications that don't support the web based 2FA login flow. For them, you need to create and use [app passwords](https://docs.nextcloud.com/server/stable/user_manual/en/session_management.html#managing-devices).

## Building yourself and call for help

To build the app, check out the repo and follow these steps:

* `composer i --no-dev`
* `npm ci`
* `npm run build` or `npm run dev` [more info](https://docs.nextcloud.com/server/latest/developer_manual/digging_deeper/npm.html)

Any offers to help are welcome, whether it's development knowledge, refactoring to fully adhere SOLID principles, better test coverage or implemeting new features, but also good documentation, examples, security audits, etc. Please contact [the dev team](https://github.com/datenschutz-individuell/twofactor_email/wiki/Developer-notes).

## State of the app

This version 3 ("v3") of the currently official [twofactor_email](https://github.com/nursoda/twofactor_email/) app version 2 ("v2") (available in the [Nextcloud App Store](https://apps.nextcloud.com/apps/twofactor_email)) is meant to someday replace v2. v3 is based on [twofactor_totp](https://github.com/nextcloud/twofactor_totp/). My idea was to only modify what's necessary and to cherrypick all changes that reflect the Nextcloud framwork changes. It turned out, that this may not have been my best idea. But the code is there, and usable.

Until the code of this app is not able to replace the currently working v2 without disturbances for users, I won't release it. Currently, the migration from v2 to v3 gives me headaches. Mind that the state machine changed, and will change further: I intend to simplify it (code not yet committed here since I cannot find why the app no longer registers itself as twofactor provider.

So if you have deeper Nextcloud framework knowledge and ideas for features, I suggest you rather create PRs here in v3 than in v2. If you know how to replace vue2 and the dependencies it pulls, please preferrably help Nextcloud to get rid of vue2 altogether. If you know how to switch to vue3 for this v3 app only, please create a PR.  Mi biggest concern is to be able to build this app without any security warnings for all officially supported Nextcloud versions.
