<?php
script('core', 'login');
?>

<form method="POST" name="login">
    <input type="text" name="challenge" required="required" autofocus autocomplete="off" autocapitalize="off">
    <div class="submit-wrap">
        <button type="submit" id="submit" class="login-button">
            <span><?php p($l->t('Verify')); ?></span>
			<div class="loading-spinner"><div></div><div></div><div></div><div></div></div>
        </button>
    </div>
</form>
