<?php

declare(strict_types = 1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Controller;

use InvalidArgumentException;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCP\AppFramework\Http\Attribute\BruteForceProtection;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Authentication\TwoFactorAuth\ALoginSetupController;
use OCP\Defaults;
use OCP\IRequest;
use OCP\IUserSession;
use RuntimeException;
use function is_null;

class SettingsController extends ALoginSetupController {

	public function __construct(
		string $appName,
		IRequest $request,
		private IUserSession $userSession,
		private ITotp $totp,
		private Defaults $defaults,
	) {
		parent::__construct($appName, $request);
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
		]);
	}

	/**
	 * @NoAdminRequired
	 * @PasswordConfirmationRequired
	 *
	 * @param int $state
	 * @param string|null $code for verification
	 */
	#[BruteForceProtection('totp_enable')]
	public function enable(int $state, ?string $code = null): JSONResponse {
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
				$qrUrl = "otpauth://totp/$secretName?secret=$secret&issuer=$issuer";
				return new JSONResponse([
					'state' => ITotp::STATE_CREATED,
					'secret' => $secret,
					'qrUrl' => $qrUrl,
				]);
			case ITotp::STATE_ENABLED:
				if ($code === null) {
					throw new InvalidArgumentException('code is missing');
				}
				$success = $this->totp->enable($user, $code);
				$response = new JSONResponse([
					'state' => $success ? ITotp::STATE_ENABLED : ITotp::STATE_CREATED,
				]);
				if (!$success) {
					$response->throttle();
				}
				return $response;
			default:
				throw new InvalidArgumentException('Invalid TOTP state');
		}
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
			throw new RuntimeException('No user in this context');
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
}
