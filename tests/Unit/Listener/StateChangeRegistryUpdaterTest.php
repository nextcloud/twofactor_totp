<?php
/**
 * Created by PhpStorm.
 * User: christoph
 * Date: 31.07.18
 * Time: 06:33
 */

namespace OCA\TwoFactorTOTP\Test\Listener;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorTOTP\Event\StateChanged;
use OCA\TwoFactorTOTP\Listener\StateChangeRegistryUpdater;
use OCA\TwoFactorTOTP\Provider\TotpProvider;
use OCP\Authentication\TwoFactorAuth\IRegistry;
use OCP\IUser;
use Symfony\Component\EventDispatcher\Event;

class StateChangeRegistryUpdaterTest extends TestCase {

	/** @var StateChangeRegistryUpdater */
	private $listener;

	/** @var IRegistry */
	private $registry;

	/** @var TotpProvider */
	private $provider;

	protected function setUp() {
		parent::setUp();

		$this->registry = $this->createMock(IRegistry::class);
		$this->provider = $this->createMock(TotpProvider::class);

		$this->listener = new StateChangeRegistryUpdater($this->registry, $this->provider);
	}

	public function testIgnoresGenericEvent() {
		$event = $this->createMock(Event::class);
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
