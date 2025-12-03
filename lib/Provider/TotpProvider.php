<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Provider;

use OCA\TwoFactorTOTP\AppInfo\Application;
use OCA\TwoFactorTOTP\Exception\NoTotpSecretFoundException;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCA\TwoFactorTOTP\Settings\Personal;
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
use OCP\Template;

class TotpProvider implements IProvider, IProvidesIcons, IProvidesPersonalSettings, IDeactivatableByAdmin, IActivatableAtLogin {

	public function __construct(
		private ITotp $totp,
		private IL10N $l10n,
		private IAppContainer $container,
		private IInitialState $initialState,
		private IURLGenerator $urlGenerator,
	) {
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
		try {
			$secret = $this->totp->getSecret($user);
		} catch (NoTotpSecretFoundException $e) {
			return false;
		}
		return $this->totp->validateSecret($secret, $challenge);
	}

	/**
	 * Decides whether 2FA is enabled for the given user
	 */
	public function isTwoFactorAuthEnabledForUser(IUser $user): bool {
		return $this->totp->hasSecret($user);
	}

	public function getLightIcon(): String {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app.svg');
	}

	public function getDarkIcon(): String {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg');
	}

	public function getPersonalSettings(IUser $user): IPersonalProviderSettings {
		$this->initialState->provideInitialState('state', $this->totp->hasSecret($user) ? ITotp::STATE_ENABLED : ITotp::STATE_DISABLED);
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
