<?php

OCP\User::checkAdminUser();

$tmpl = new OCP\Template('twofactor_totp', 'admin');
$tmpl->assign('totp_type', OCP\Config::getAppValue('twofactor_totp', 'totp_type', 0));

$tmpl->assign('groups', []); // @TODO: Get the Groups from db. provide as array of strings
$tmpl->assign('users', []); // @TODO: Get the Users from db. provide as array of strings

return $tmpl -> fetchPage();
