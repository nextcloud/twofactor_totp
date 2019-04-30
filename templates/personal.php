<?php

script('twofactor_totp', 'main-settings');
style('twofactor_totp', 'settings');

?>

<input type="hidden" id="twofactor-totp-initial-state" value="<?php p($_['state']); ?>">

<div id="twofactor-totp-settings"></div>
