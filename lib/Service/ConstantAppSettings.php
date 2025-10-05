<?php

namespace OCA\TwoFactorEMail\Service;

final class ConstantAppSettings implements IAppSettings {
	public function getCodeValidSeconds(): int {
		return 60 * 60 * 24; // 1 day
	}
}
