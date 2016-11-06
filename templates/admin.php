<?php

//script('twofactor_totp', 'settingsview');
script('twofactor_totp', 'admin');
$totp_type = intval($_['totp_type']);
if($totp_type > 2 || $totp_type < 1) $totp_type = 0;
?>

<div class="section" id="section-totp">
    <h2><?php p($l->t('TOTP Second-Factor Authentication')); ?></h2>

    <div class="subection">
        <h3><?php p($l->t('Usage Mode')); ?></h3>

        <input class="radio" type="radio" name="totp_type" value="0" id="totp_type_0"<?php echo $totp_type==0 ? ' checked' : ''; ?>>
        <label for="totp_type_0"><?php p($l->t('Default Mode')); ?></label>
        | <em><?php p($l->t('Each user can decide whether to use TOTP or not')); ?></em>
        <br>
        <s>
            <input class="radio" type="radio" name="totp_type" value="1" id="totp_type_1"<?php echo $totp_type==1 ? ' checked' : ''; ?> disabled>
            <label for="totp_type_1"><?php p($l->t('Whitelist Mode')); ?></label>
            | <em><?php p($l->t('Nobody is forced to TOTP, except for mentioned groups/users')); ?></em>
            <br>
        </s>
        <input class="radio" type="radio" name="totp_type" value="2" id="totp_type_2"<?php echo $totp_type==2 ? ' checked' : ''; ?>>
        <label for="totp_type_2"><?php p($l->t('Blacklist Mode')); ?></label>
        | <em><?php p($l->t('Everybody is forced to TOTP')); ?><s>, except for mentioned groups/users</s></em>

        <hr>
        <code><strong>@TODO:</strong> To be implemented:</code>
        <br>
        <s>
            <strong>Select Users/Groups:</strong><br>
            <input type="text" id="totp_sharee_search" value="">

            <div id="totp_sharee_list">
            </div>
        </s>

        <p><small>Keep in mind that a user who is forced to TOTP will see the QR-Code and Char-Code after his first valid login!</small></p>
    </div>
</div>
