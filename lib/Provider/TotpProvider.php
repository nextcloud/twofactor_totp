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
use OCP\Authentication\TwoFactorAuth\IPersonalProviderSettings;
use OCP\Authentication\TwoFactorAuth\IProvider;
use OCP\Authentication\TwoFactorAuth\IProvidesIcons;
use OCP\Authentication\TwoFactorAuth\IProvidesPersonalSettings;
use OCP\IL10N;
use OCP\IUser;
use OCP\Template;

class TotpProvider implements IProvider, IProvidesIcons, IProvidesPersonalSettings {

	/** @var ITotp */
	private $totp;

	/** @var IL10N */
	private $l10n;

	public function __construct(ITotp $totp, IL10N $l10n) {
		$this->totp = $totp;
		$this->l10n = $l10n;
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
		$tmpl = new Template('twofactor_totp', 'challenge');
		return $tmpl;
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
		return new Personal();
	}
}
