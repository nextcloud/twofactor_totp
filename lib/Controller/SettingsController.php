<?php

declare(strict_types = 1);

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

namespace OCA\TwoFactorTOTP\Controller;

use InvalidArgumentException;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Authentication\TwoFactorAuth\ALoginSetupController;
use OCP\Defaults;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use RuntimeException;
use function is_null;

class SettingsController extends ALoginSetupController {

	/** @var ITotp */
	private $totp;

	/** @var IUserSession */
	private $userSession;

	/** @var Defaults */
	private $defaults;

	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(
		string $appName,
		IRequest $request,
		IUserSession $userSession,
		ITotp $totp,
		Defaults $defaults,
		IURLGenerator $urlGenerator
	) {
		parent::__construct($appName, $request);
		$this->userSession = $userSession;
		$this->totp = $totp;
		$this->defaults = $defaults;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @NoAdminRequired
	 * @return JSONResponse
	 */
	public function state(): JSONResponse {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new \Exception('user not available');
		}
		return new JSONResponse([
			'state' => $this->totp->hasSecret($user) ? ITotp::STATE_ENABLED : ITotp::STATE_DISABLED,
			'tokenLength' => $this->totp->getTokenLength($user),
			'hashAlgorithm' => $this->totp->getHashAlgorithmId($user),
		]);
	}

	/**
	 * @NoAdminRequired
	 * @PasswordConfirmationRequired
	 *
	 * @param int $state
	 * @param string|null $code for verification
	 * @param int $tokenLength
	 * @param int $hashAlgorithm
	 */
	public function enable(int $state, string $code = null, int $tokenLength = 6, int $hashAlgorithm = 1) {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new \Exception('user not available');
		}
		switch ($state) {
			case ITotp::STATE_DISABLED:
				$this->totp->deleteSecret($user);
				return new JSONResponse([
					'state' => ITotp::STATE_DISABLED,
				]);
			case ITotp::STATE_CREATED:
				$secret = $this->totp->createSecret($user);

				$secretName = $this->getSecretName();
				$issuer = $this->getSecretIssuer();
				$faviconUrl = $this->getFaviconUrl();
				$qrUrl = "otpauth://totp/$secretName?secret=$secret&issuer=$issuer&image=$faviconUrl";
				return new JSONResponse([
					'state' => ITotp::STATE_CREATED,
					'secret' => $secret,
					'qrUrl' => $qrUrl,
				]);
			case ITotp::STATE_ENABLED:
				if ($code === null) {
					throw new InvalidArgumentException("code is missing");
				}
				$success = $this->totp->enable($user, $code);
				if ($success) {
					// Redirect to the 'state' route after successful enabling
					$url = $this->urlGenerator->linkToRoute('twofactor_totp.settings.state');
					return new RedirectResponse($url);
				} else {
					return new JSONResponse([
						'state' => ITotp::STATE_CREATED,
					]);
				}
				// no break
			default:
				throw new InvalidArgumentException('Invalid TOTP state');
		}
	}

	/**
	 * Update TOTP settings after TOTP has been enabled.
	 *
	 * @NoAdminRequired
	 * @PasswordConfirmationRequired
	 *
	 * @param int $tokenLength
	 * @param int $hashAlgorithm
	 * @return JSONResponse
	 */
	public function updateSettings(int $tokenLength, int $hashAlgorithm): JSONResponse {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new \Exception('user not available');
		}

		$this->totp->updateSettings($user, $tokenLength, $hashAlgorithm);
		return new JSONResponse([
			'tokenLength' => $tokenLength,
			'hashAlgorithm' => $hashAlgorithm,
		]);
	}

	public function getSettings(): JSONResponse {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new \Exception('user not available');
		}

		$settings = $this->totp->getSettings($user);
		return new JSONResponse($settings);
	}

	/**
	 * The user's cloud id, e.g. "christina@university.domain/owncloud"
	 *
	 * @return string
	 */
	private function getSecretName(): string {
		$productName = $this->defaults->getName();
		$user = $this->userSession->getUser();
		if ($user === null) {
			throw new RuntimeException("No user in this context");
		}
		$userName = $user->getCloudId();
		return rawurlencode("$productName:$userName");
	}

	/**
	 * The issuer, e.g. "Nextcloud"
	 *
	 * @return string
	 */
	private function getSecretIssuer(): string {
		$productName = $this->defaults->getName();
		return rawurlencode($productName);
	}

	/**
	 * FaviconUrl for FreeOTP
	 *
	 * @return string
	 */
	private function getFaviconUrl(): string {
		$baseUrl = $this->urlGenerator->getBaseUrl();
		$subPath = $this->urlGenerator->linkToRoute('theming.Icon.getFavicon', ['app' => 'core']);
		return $baseUrl . $subPath;
	}
}
