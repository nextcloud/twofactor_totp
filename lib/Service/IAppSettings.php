<?php

namespace OCA\TwoFactorEMail\Service;

interface IAppSettings {
	public function getCodeValidSeconds(): int;
}
