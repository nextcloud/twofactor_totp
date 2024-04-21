# Twofactor Email

This two factor authentication provider for Nextcloud creates a 6-digit random authentication code and sends it to the user's primary email address.

## Installation, activation and usage

The app must be installed and activated by a Nextcloud server admin. The easiest way to do so is to select "Apps" from the menu and search for "two", then select and install it – which will retrieve it from the [App Store](https://apps.nextcloud.com/apps/twofactor_email).

Users may enable any installed 2FA app. Upon login, they may choose from all enabled 2FA apps. It cannot be enabled if no email address is set in 'Personal info'. In the future, the app might be enhanced to allow using alternate address set there.

Admins may enforce 2FA for certain or all users. If enforced, users are prompted enable any installed 2FA app. In the future, the app might be enhanced to allow admins to enable Twofactor Email for new or even existing users. 

Mind that, once you enable 2FA, you can no longer use your passwort in applications that don't support the web based 2FA login flow. For them, you need to create and use [app passwords](https://docs.nextcloud.com/server/stable/user_manual/en/session_management.html#managing-devices).

## Any development help welcome

To build the app, check out the repo and follow these steps:

* `composer update`
* `composer i`
* `npm install`
* `npm ci`
* `npm run build` or `npm run dev` [more info](https://docs.nextcloud.com/server/latest/developer_manual/digging_deeper/npm.html)

Any pull requests or offers to help are welcome, please contact [the dev team](https://github.com/datenschutz-individuell/twofactor_email/wiki/Developer-notes).
