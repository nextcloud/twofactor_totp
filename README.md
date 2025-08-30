# Two-Factor E-Mail Provider for Nextcloud

[Nextcloud](https://nextcloud.com/) supports web logins with a second factor
([two factor authentication](https://en.wikipedia.org/wiki/Multi-factor_authentication#Factors),
2FA). To support a certain type of 2FA, a "2FA provider" (server-)app must be
installed. 2FA kicks in after the primary authentication stage (typically
username and password) were successful. This provider challenges the user to
enter a randomly generated authentication code (aka one-time password, OTP,
currently 6 digits). It sends that code to the user's primary e-mail address and
expects the user to enter it on an additional 2nd step web login page.

### Installation, activation and usage

As with any 2FA provider, two-factor e-mail must be installed from the
[Nextcloud app store](https://apps.nextcloud.com/apps/twofactor_email) and
enabled by a Nextcloud server admin. Additionally, the Nextcloud must have a
working e-mail server configured.

The user may set up any of the installed providers or even multiple. This
provider uses e-mail to send the code and thus can only be enabled if an e-mail
address is set in 'Personal info'. Mind that a user may not be able to log in
if that e-mail address is invalid (or e-mail server setup of the Nextcloud is
not working properly).

Admins with console access may enable and disable this provider for specified
users via OCC command. Admins may also enforce 2FA for all users (or specific
groups) via Admin Settings. This is a Nextcloud feature and not specific to
this provider. If enforced, users with no 2FA are prompted to enable any
installed provider (that supports AtLogin setup – this provider supports it
since v3). If the admin installs this provider and enforces 2FA, it should be
ensured that each user does have a valid e-mail address.

Mind that, once a user enabled any 2FA provider, they can no longer use their
password in applications that don't support the web based 2FA login flow. For
such applications, the user needs to create and use
[app passwords](https://docs.nextcloud.com/server/stable/user_manual/en/session_management.html#managing-devices)
(to be found at the bottom of Personal Settings/Security).

## Building yourself and call for help

To build the app, check out the repo and use [krankerl](https://github.com/ChristophWurst/krankerl/)
package` or follow these steps:

* `composer i --no-dev`
* `npm ci`
* `npm run build` or `npm run dev` [more info](https://docs.nextcloud.com/server/latest/developer_manual/digging_deeper/npm.html)

Any offers to help are welcome, whether it's development, better test coverage
or implementing new features, but also good documentation, examples, security
audits, translations / integration in translation tools, etc.

Please contact [the current maintainers](https://github.com/datenschutz-individuell/CONTRIBUTORS.md).

## State of the app

This version 3 ("v3") of the currently official [twofactor_email](https://github.com/nursoda/twofactor_email/)
app version 2 ("v2") (available in the [Nextcloud App Store](https://apps.nextcloud.com/apps/twofactor_email))
is meant to someday soon replace v2. v3 is based on [twofactor_totp](https://github.com/nextcloud/twofactor_totp/).
My idea was to only modify what's necessary and to cherrypick all changes that
reflect the Nextcloud framework changes. It turned out, that this may not have
been my best idea. The code is merely stable now, thus lacking some polishing.
Currently, this is NOT production quality yet, but public beta!

This new app now migrates existing v2 settings to v3. Thus, there should be
little user disturbance. Nevertheless, the look slightly changed, and behaviour
changed for edge cases. I will only release it in the app store when I am
pretty sure it doesn't break things.

We refrained from the possibility to be able to set an alternate address. You
are free to contribute code that implements is. Make sure it doesn't break
existing functionality though.

If you have deeper Nextcloud framework knowledge and ideas for features, I
suggest you rather create PRs here in v3 than in v2. If you know how to replace
vue2 and the dependencies it pulls, please preferably help Nextcloud to get rid
of vue2 altogether. If you know how to switch to vue3 for this v3 app only,
please create a PR. My biggest concern is to be able to build this app without
ANY security warnings for all officially supported Nextcloud versions.

I try to review PRs timely. I will also make sure that there's a v2 release
for all officially supported Nextcloud versions until this app is released.
