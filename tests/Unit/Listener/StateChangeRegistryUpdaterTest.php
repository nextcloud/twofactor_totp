<?php

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Test\Listener;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorTOTP\Event\StateChanged;
use OCA\TwoFactorTOTP\Listener\StateChangeRegistryUpdater;
use OCA\TwoFactorTOTP\Provider\TotpProvider;
use OCP\Authentication\TwoFactorAuth\IRegistry;
use OCP\EventDispatcher\Event;
use OCP\IUser;

class StateChangeRegistryUpdaterTest extends TestCase {

	/** @var StateChangeRegistryUpdater */
	private $listener;

	/** @var IRegistry */
	private $registry;

	/** @var TotpProvider */
	private $provider;

	protected function setUp(): void {
		parent::setUp();

		$this->registry = $this->createMock(IRegistry::class);
		$this->provider = $this->createMock(TotpProvider::class);

		$this->listener = new StateChangeRegistryUpdater($this->registry, $this->provider);
	}

	public function testIgnoresGenericEvent(): void {
		$event = new Event();
		$this->registry->expects($this->never())
			->method('enableProviderFor');
		$this->registry->expects($this->never())
			->method('disableProviderFor');

		$this->listener->handle($event);
	}

	public function testProviderEnabledEvent(): void {
		$user = $this->createMock(IUser::class);
		$event = new StateChanged($user, true);
		$this->registry->expects($this->once())
			->method('enableProviderFor')
			->with($this->provider, $user);

		$this->listener->handle($event);
	}

	public function testProviderDisabledEvent(): void {
		$user = $this->createMock(IUser::class);
		$event = new StateChanged($user, false);
		$this->registry->expects($this->once())
			->method('disableProviderFor')
			->with($this->provider, $user);

		$this->listener->handle($event);
	}
}
