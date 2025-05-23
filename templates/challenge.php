<?php

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

style('twofactor_totp', 'style');
?>

<img class="two-factor-icon two-factor-totp-icon" src="<?php print_unescaped(image_path('twofactor_totp', 'app.svg')); ?>" alt="">

<p><?php p($l->t('Get the authentication code from the two-factor authentication app on your device.')) ?></p>

<form method="POST" class="totp-form">
	<input type="text" minlength="6" maxlength="10" name="challenge" required="required" autofocus autocomplete="one-time-code" inputmode="numeric" autocapitalize="off" placeholder="<?php p($l->t('Authentication code')) ?>">
	<button class="primary two-factor-submit" type="submit">
		<?php p($l->t('Submit')); ?>
	</button>
</form>
