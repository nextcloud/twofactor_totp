<?php if (isset($_['qr'])): ?>
<a href="<?php echo $_['qr']; ?>" target="_blank">Scan QR Code</a>
<?php endif; ?>

<form method="POST">
    <input type="text" name="challenge" required="required">
    <input type="submit" class="button" value="Verify">
</form>
