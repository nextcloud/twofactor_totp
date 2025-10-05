<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

style('twofactor_email', 'LoginChallenge');
?>

<img class="two-factor-icon twofactor_email-challenge-icon" src="<?php print_unescaped(image_path('twofactor_email', 'app.svg')); ?>" alt="Icon depicting a letter and a user">

<p><?php p($l->t('Get the authentication code from your e-mail inbox.')) ?></p>

<form method="POST" class="twofactor_email-challenge-form">
	<input type="text" minlength="6" maxlength="10" name="challenge" required="required" autofocus autocomplete="one-time-code" inputmode="numeric" autocapitalize="off" placeholder="<?php p($l->t('Authentication code')) ?>">
	<button class="primary two-factor-submit" type="submit">
		<?php p($l->t('Submit')); ?>
	</button>
</form>
