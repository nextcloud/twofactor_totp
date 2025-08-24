<?php

namespace OCA\TwoFactorEMail\Service;

interface IEMailAddressMasker {
	public function maskForUI(string $emailAddress): string;
}
