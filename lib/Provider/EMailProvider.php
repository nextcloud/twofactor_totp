<?php

declare(strict_types=1);

/**
 * @author Nico Kluge <nico.kluge@klugecoded.com>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * Two-factor TOTP
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\TwoFactorEMail\Provider;

use OCA\TwoFactorEMail\AppInfo\Application;
use OCA\TwoFactorEMail\Service\IEMailService;
use OCA\TwoFactorEMail\Settings\Personal;
use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\Services\IInitialState;
use OCP\Authentication\TwoFactorAuth\IActivatableAtLogin;
use OCP\Authentication\TwoFactorAuth\IDeactivatableByAdmin;
use OCP\Authentication\TwoFactorAuth\ILoginSetupProvider;
use OCP\Authentication\TwoFactorAuth\IPersonalProviderSettings;
use OCP\Authentication\TwoFactorAuth\IProvider;
use OCP\Authentication\TwoFactorAuth\IProvidesIcons;
use OCP\Authentication\TwoFactorAuth\IProvidesPersonalSettings;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Security\ISecureRandom;
use OCP\Template;

class EMailProvider implements IProvider, IProvidesIcons, IProvidesPersonalSettings, IDeactivatableByAdmin, IActivatableAtLogin {

	/** @var IEMailService */
	private $emailService;

	/** @var IL10N */
	private $l10n;

	/** @var IAppContainer */
	private $container;

	/** @var IInitialState */
	private $initialState;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var ISecureRandom */
	private $secureRandom;

	public function __construct(IEMailService $emailService,
								IL10N         $l10n,
								IAppContainer $container,
								IInitialState $initialStateService,
								IURLGenerator $urlGenerator,
								ISecureRandom $secureRandom	) {
		$this->emailService = $emailService;
		$this->l10n = $l10n;
		$this->container = $container;
		$this->initialState = $initialStateService;
		$this->urlGenerator = $urlGenerator;
		$this->secureRandom = $secureRandom;
	}

	/**
	 * Get unique identifier of this 2FA provider
	 */
	public function getId(): string {
		return 'email';
	}

	/**
	 * Get the display name for selecting the 2FA provider
	 */
	public function getDisplayName(): string {
		return 'E-mail';
	}

	/**
	 * Get the description for selecting the 2FA provider
	 */
	public function getDescription(): string {
		return $this->l10n->t('Authenticate by e-mail');
	}

	/**
	 * Get the template for rending the 2FA provider view
	 * This function is called from nextcloud when the user activated the e-mail 2FA and is now logging in
	 */
	public function getTemplate(IUser $user): Template {
		// Send new authentication code
		$authenticationCode = $this->secureRandom->generate(6, ISecureRandom::CHAR_DIGITS);
		$this->emailService->setAndSendAuthCode($user, $authenticationCode);
		// Return the template for the challenge view (challenge.php file in the templates folder of the app)
		return new Template(Application::APP_ID, 'challenge');
	}

	/**
	 * Verify the given challenge
	 */
	public function verifyChallenge(IUser $user, string $challenge): bool {
		$challenge = preg_replace('/[^0-9]/', '', $challenge);
		return $this->emailService->validateTwoFactorEMail($user, $challenge);
	}

	/**
	 * Decides whether 2FA is enabled for the given user
	 */
	public function isTwoFactorAuthEnabledForUser(IUser $user): bool {
		return $this->emailService->isEnabled($user);
	}

	public function getLightIcon(): String {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app.svg');
	}

	public function getDarkIcon(): String {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg');
	}

	public function getPersonalSettings(IUser $user): IPersonalProviderSettings {
		$this->initialState->provideInitialState('state', $this->emailService->isEnabled($user) ? IEMailService::STATE_ENABLED : IEMailService::STATE_DISABLED);
		return new Personal();
	}

	/**
	 * Disable this provider for the given user.
	 *
	 * @param IUser $user the user to deactivate this provider for
	 */
	public function disableFor(IUser $user): void {
		$this->emailService->deleteTwoFactorEMail($user, true);
	}

	public function getLoginSetup(IUser $user): ILoginSetupProvider {
		return $this->container->query(AtLoginProvider::class);
	}
}
