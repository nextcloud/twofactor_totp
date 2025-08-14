<?php

namespace OCA\TwoFactorEMail\Service;

interface IApplicationSettings
{
	public function getCodeValidSeconds(): int;
}
