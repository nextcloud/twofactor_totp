<?php

namespace OCA\TwoFactorEMail\Service;

class ConstantApplicationSettings implements IApplicationSettings
{
	public function getCodeValidSeconds(): int
	{
		return 60 * 60 * 24; // 1 day
	}
}
