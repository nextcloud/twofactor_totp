<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Provider;

use OCA\TwoFactorEMail\AppInfo\Application;
use OCA\TwoFactorEMail\Service\IChallengeService;
use OCA\TwoFactorEMail\Service\IStateManager;
use OCA\TwoFactorEMail\Settings\Personal;
use OCP\AppFramework\Services\IInitialState;
use OCP\Authentication\TwoFactorAuth\IActivatableAtLogin;
use OCP\Authentication\TwoFactorAuth\IActivatableByAdmin;
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
use Psr\Container\ContainerInterface;

class EMailProvider implements IProvider, IProvidesIcons, IProvidesPersonalSettings, IDeactivatableByAdmin, IActivatableByAdmin, IActivatableAtLogin {

	public function __construct(
		private IL10N              $l10n,
		private IInitialState      $initialState,
		private IURLGenerator      $urlGenerator,
		private ContainerInterface $container,
		private IChallengeService   $challengeService,
		private IStateManager      $stateManager,
	) {
	}

	public function getId(): string {
		return 'email';
	}

	public function getDisplayName(): string {
		return 'E-mail';
	}

	public function getDescription(): string {
		return $this->l10n->t('Authenticate by e-mail');
	}

	/**
	 * Get the template for rending the 2FA provider view.
	 * This function is called from nextcloud when the user activated the e-mail 2FA and is now logging in.
	 */
	public function getTemplate(IUser $user): Template {
		$this->challengeService->sendChallenge($user);
		// Return the template for the challenge view (challenge.php file in the templates folder of the app)
		return new Template(Application::APP_ID, 'challenge');
	}

	public function verifyChallenge(IUser $user, string $challenge): bool {
		return $this->challengeService->verifyChallenge($user, $challenge);
	}

	/**
	 * Decides whether 2FA is enabled for the given user.
	 * The Nextcloud stores two-factor provider states for every user in the oc_twofactor_providers table.
	 * If no entry for an installed provider exists for a user then this method will be called.
	 * The result will then be written to that table by Nextcloud.
	 */
	public function isTwoFactorAuthEnabledForUser(IUser $user): bool {
		return $this->stateManager->isEnabled($user);
	}

	public function getLightIcon(): String {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app.svg');
	}

	public function getDarkIcon(): String {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg');
	}

	public function getPersonalSettings(IUser $user): IPersonalProviderSettings {
		$this->initialState->provideInitialState('enabled', $this->stateManager->isEnabled($user));
		$this->initialState->provideInitialState('hasEmail', !empty($user->getEMailAddress()));
		return new Personal();
	}

	public function disableFor(IUser $user): void {
		$this->stateManager->disable($user, true);
	}

	public function enableFor(IUser $user): void
	{
		$this->stateManager->enable($user, true);
	}

	public function getLoginSetup(IUser $user): ILoginSetupProvider
	{
		return $this->container->get(AtLoginProvider::class);
	}
}
