<?php

declare(strict_types=1);

/**
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

namespace OCA\TwoFactorTOTP\Provider;

use OCA\TwoFactorTOTP\Service\ITotp;
use OCA\TwoFactorTOTP\Settings\Personal;
use OCP\AppFramework\IAppContainer;
use OCP\Authentication\TwoFactorAuth\IActivatableAtLogin;
use OCP\Authentication\TwoFactorAuth\IDeactivatableByAdmin;
use OCP\Authentication\TwoFactorAuth\ILoginSetupProvider;
use OCP\Authentication\TwoFactorAuth\IPersonalProviderSettings;
use OCP\Authentication\TwoFactorAuth\IProvider;
use OCP\Authentication\TwoFactorAuth\IProvidesIcons;
use OCP\Authentication\TwoFactorAuth\IProvidesPersonalSettings;
use OCP\IInitialStateService;
use OCP\IL10N;
use OCP\IUser;
use OCP\Template;

class TotpProvider implements IProvider, IProvidesIcons, IProvidesPersonalSettings, IDeactivatableByAdmin, IActivatableAtLogin {

	/** @var ITotp */
	private $totp;

	/** @var IL10N */
	private $l10n;

	/** @var IAppContainer */
	private $container;

	/** @var IInitialStateService */
	private $initialStateService;

	public function __construct(ITotp $totp,
								IL10N $l10n,
								IAppContainer $container,
								IInitialStateService $initialStateService) {
		$this->totp = $totp;
		$this->l10n = $l10n;
		$this->container = $container;
		$this->initialStateService = $initialStateService;
	}

	/**
	 * Get unique identifier of this 2FA provider
	 */
	public function getId(): string {
		return 'totp';
	}

	/**
	 * Get the display name for selecting the 2FA provider
	 */
	public function getDisplayName(): string {
		return 'TOTP (Authenticator app)';
	}

	/**
	 * Get the description for selecting the 2FA provider
	 */
	public function getDescription(): string {
		return $this->l10n->t('Authenticate with a TOTP app');
	}

	/**
	 * Get the template for rending the 2FA provider view
	 */
	public function getTemplate(IUser $user): Template {
		return new Template('twofactor_totp', 'challenge');
	}

	/**
	 * Verify the given challenge
	 */
	public function verifyChallenge(IUser $user, string $challenge): bool {
		$challenge = preg_replace('/[^0-9]/', '', $challenge);
		return $this->totp->validateSecret($user, $challenge);
	}

	/**
	 * Decides whether 2FA is enabled for the given user
	 */
	public function isTwoFactorAuthEnabledForUser(IUser $user): bool {
		return $this->totp->hasSecret($user);
	}

	public function getLightIcon(): String {
		return image_path('twofactor_totp', 'app.svg');
	}

	public function getDarkIcon(): String {
		return image_path('twofactor_totp', 'app-dark.svg');
	}

	public function getPersonalSettings(IUser $user): IPersonalProviderSettings {
		$this->initialStateService->provideInitialState('twofactor_totp', 'state', $this->totp->hasSecret($user) ? ITotp::STATE_ENABLED : ITotp::STATE_DISABLED);
		return new Personal();
	}

	/**
	 * Disable this provider for the given user.
	 *
	 * @param IUser $user the user to deactivate this provider for
	 */
	public function disableFor(IUser $user) {
		$this->totp->deleteSecret($user, true);
	}

	public function getLoginSetup(IUser $user): ILoginSetupProvider {
		return $this->container->query(AtLoginProvider::class);
	}

}
