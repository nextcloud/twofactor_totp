<?php
style('twofactor_totp', 'style');

if(isset($_['new_totp_user'])){
    $s = $_['secret'];
    $qr = $_['qr_src'];
    ?>
    <fieldset class="warning">
        <legend>Welcome to TOTP</legend>
        <p>As this is your first login we have just setup everything for you.</p>
        <p>Please scan the following QR-Code with an Authenticator-App or type the secret by hand.</p>
        <p><img src="<?php echo $qr; ?>"></p>
        <p>Your secret: <strong><?php echo $s; ?></strong></p>

        <p>&nbsp;<br>Afterwards please enter the 6 digits to authenticate</p>
    </fieldset>
    <?php
}
?>


<form method="POST" class="totp-form">
	<input type="number" class="challenge" name="challenge" required="required" autofocus autocomplete="off" autocapitalize="off" placeholder="<?php p($l->t('Authentication code')) ?>">
	<input type="submit" class="confirm-inline icon-confirm" value="">
	<p><?php p($l->t('Get the authentication code from the two-factor authentication app on your device.')) ?></p>
</form>
