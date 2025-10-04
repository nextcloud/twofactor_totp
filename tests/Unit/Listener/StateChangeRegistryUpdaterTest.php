<?php

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Test\Unit\Listener;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorEMail\Event\StateChanged;
use OCA\TwoFactorEMail\Listener\StateChangeRegistryUpdater;
use OCA\TwoFactorEMail\Provider\TwoFactorEMail;
use OCP\Authentication\TwoFactorAuth\IRegistry;
use OCP\EventDispatcher\Event;
use OCP\IUser;
use PHPUnit\Framework\MockObject\MockObject;

class StateChangeRegistryUpdaterTest extends TestCase {

	private StateChangeRegistryUpdater $listener;

	private IRegistry|MockObject $registry;
	private TwoFactorEMail|MockObject $provider;

	protected function setUp(): void {
		parent::setUp();

		$this->registry = $this->createMock(IRegistry::class);
		$this->provider = $this->createMock(TwoFactorEMail::class);

		$this->listener = new StateChangeRegistryUpdater($this->registry, $this->provider);
	}

	public function testIgnoresGenericEvent() {
		$event = new Event();
		$this->registry->expects($this->never())
			->method('enableProviderFor');
		$this->registry->expects($this->never())
			->method('disableProviderFor');

		$this->listener->handle($event);
	}

	public function testProviderEnabledEvent() {
		$user = $this->createMock(IUser::class);
		$event = new StateChanged($user, true);
		$this->registry->expects($this->once())
			->method('enableProviderFor')
			->with($this->provider, $user);

		$this->listener->handle($event);
	}

	public function testProviderDisabledEvent() {
		$user = $this->createMock(IUser::class);
		$event = new StateChanged($user, false);
		$this->registry->expects($this->once())
			->method('disableProviderFor')
			->with($this->provider, $user);

		$this->listener->handle($event);
	}
}
