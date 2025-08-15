<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Service;

use OCA\TwoFactorEMail\Event\StateChanged;
use OCP\Authentication\TwoFactorAuth\IRegistry;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\IUser;

class StateManager implements IStateManager {
	public function __construct(
		private IEventDispatcher $eventDispatcher,
		private IRegistry $registry,
		private IConfig   $config,
	) {
	}

	public function enable(IUser $user, bool $byAdmin = false): void {
		// Registry modification happens in \OCA\TwoFactorEMail\Listener\StateChangeRegistryUpdater due to circular dependency
		$this->eventDispatcher->dispatchTyped(new StateChanged($user, true, $byAdmin));
	}

	public function disable(IUser $user, bool $byAdmin = false): void {
		// Registry modification happens in \OCA\TwoFactorEMail\Listener\StateChangeRegistryUpdater due to circular dependency
		$this->eventDispatcher->dispatchTyped(new StateChanged($user, false, $byAdmin));
	}

	public function isEnabled(IUser $user): bool {
		$activeProviders = $this->registry->getProviderStates($user);
		if (isset($activeProviders['email'])) {
			return $activeProviders['email'];
		}
		return $this->isTwoFactorAuthenticationEnforced()
			&& $this->isNoOtherProviderActive($activeProviders)
			&& $this->hasUserEmail($user);
	}

	private function isNoOtherProviderActive(array $activeProviders): bool
	{
		unset($activeProviders['backup_codes']); // backup codes are not a primary second factor
		return !array_reduce($activeProviders, fn($a, $b) => $a || $b, false);
	}

	private function isTwoFactorAuthenticationEnforced(): bool
	{
		return $this->config->getSystemValueBool('twofactor_enforced');
	}

	private function hasUserEmail(IUser $user): bool
	{
		return empty($user->getEMailAddress());
	}
}
