<?php

namespace OCA\TwoFactorEMail\Service;

use OCP\Authentication\TwoFactorAuth\IRegistry;
use OCP\IConfig;
use OCP\IUser;

class EMailProviderState implements IEMailProviderState
{
	public function __construct(
		private IRegistry $registry,
		private IConfig   $config,
	)
	{
	}

	public function isEnabled(IUser $user): bool {
		$activeProviders = $this->registry->getProviderStates($user);
		if ($activeProviders['email'] ?? false) {
			return true;
		}
		return $this->isTwoFactorAuthenticationEnforced()
			&& $this->isNoOtherProviderActive($activeProviders)
			&& $this->hasUserEmail($user);
	}

	private function isNoOtherProviderActive(array $activeProviders): bool
	{
		return !array_reduce($activeProviders, fn($a, $b) => $a || $b, false);
	}

	private function isTwoFactorAuthenticationEnforced(): bool
	{
		return $this->config->getSystemValueBool('twofactor_enforced');
	}

	private function hasUserEmail(IUser $user): bool
	{
		return $user->getEMailAddress() !== null;
	}
}
