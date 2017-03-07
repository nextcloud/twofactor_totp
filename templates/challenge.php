<?php
style('twofactor_totp', 'style');
?>

<form method="POST" class="totp-form">
	<input type="tel" minlength="6" maxlength="6" name="challenge" required="required" autofocus autocomplete="off" autocapitalize="off" placeholder="<?php p($l->t('Authentication code')) ?>">
	<button type="submit">
		<span>Submit</span>
	</button>
	<p><?php p($l->t('Get the authentication code from the two-factor authentication app on your device.')) ?></p>
</form>
