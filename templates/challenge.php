<?php
style('twofactor_totp', 'style');
?>

<form method="POST" class="totp-form">
	<input type="text" class="challenge" name="challenge" required="required" autofocus autocomplete="off" autocapitalize="off" placeholder="<?php p($l->t('Authentication code')) ?>">
	<input type="submit" class="confirm-inline icon-confirm" value="">
	<p>Get the authentication code from the two-factor authentication app on your device.</p>
</form>
