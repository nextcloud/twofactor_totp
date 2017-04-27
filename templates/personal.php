<?php

script('twofactor_totp', 'settingsview');
script('twofactor_totp', 'settings');
style('twofactor_totp', 'settings');

?>

<div class="section">
    <h2 data-anchor-name="totp-second-factor-auth"><?php p($l->t('TOTP second-factor auth')); ?></h2>
    <div id="twofactor-totp-settings"></div>
</div>
