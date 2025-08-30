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
use OCP\IUser;

final class StateManager implements IStateManager {
	public function __construct(
		private IEventDispatcher $eventDispatcher,
		private IRegistry $registry,
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
		return $this->registry->getProviderStates($user)['email'] ?? false;
	}
}
