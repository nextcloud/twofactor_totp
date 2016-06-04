<p>TOTP: <?php p($_['secret']); ?></p>

<form method="POST">
	<input type="text" name="challenge" required="required">
	<input type="submit" class="button" value="Verify">
</form>
