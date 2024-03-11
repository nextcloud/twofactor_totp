<?php
style('twofactor_email', 'style');
?>

<img class="two-factor-icon two-factor-email-icon" src="<?php print_unescaped(image_path('twofactor_email', 'app.svg')); ?>" alt="">

<p><?php p($l->t('Get the authentication code from your email inbox.')) ?></p>

<form method="POST" class="twofactor-email-form">
	<input type="tel" minlength="6" maxlength="10" name="challenge" required="required" autofocus autocomplete="off" autocapitalize="off" placeholder="<?php p($l->t('Authentication code')) ?>">
	<button class="primary two-factor-submit" type="submit">
		<?php p($l->t('Submit')); ?>
	</button>
</form>
