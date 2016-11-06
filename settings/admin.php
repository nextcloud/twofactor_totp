<?php

OCP\User::checkAdminUser();

$tmpl = new OCP\Template('twofactor_totp', 'admin');
$tmpl->assign('totp_type', OCP\Config::getAppValue('twofactor_totp', 'totp_type', 0));

return $tmpl -> fetchPage();
