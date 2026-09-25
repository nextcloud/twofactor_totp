<?php

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

style('twofactor_totp', 'style');
script('twofactor_totp', 'twofactor_totp-main-challenge');
?>

<img class="two-factor-icon two-factor-totp-icon" src="<?php print_unescaped(image_path('twofactor_totp', 'app.svg')); ?>" alt="">

<p><?php p($l->t('Get the authentication code from the two-factor authentication app on your device.')) ?></p>

<form method="POST" class="totp-form">
	<input class="two-factor-code-input two-factor-code-digits" type="text" minlength="6" maxlength="10" pattern="[0-9]{6,10}" name="challenge" required="required" autofocus autocomplete="one-time-code" inputmode="numeric" autocapitalize="off" placeholder="000000" aria-label="<?php p($l->t('Authentication code')) ?>">
	<button class="primary two-factor-submit" type="submit">
		<?php p($l->t('Submit')); ?>
	</button>
</form>
