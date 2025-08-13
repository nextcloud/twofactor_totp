<?php

declare(strict_types = 1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Controller;

use Exception;
use InvalidArgumentException;
use OCA\TwoFactorEMail\Service\IEMailService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Authentication\TwoFactorAuth\ALoginSetupController;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Security\ISecureRandom;
use function is_null;

class SettingsController extends ALoginSetupController {

	public function __construct(
		string $appName,
		IRequest $request,
		private IUserSession $userSession,
		private IEMailService $emailService,
		private ISecureRandom $secureRandom,
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
			'state' => $this->emailService->isEnabled($user) ? IEMailService::STATE_ENABLED : IEMailService::STATE_DISABLED,
		]);
	}

	/**
	 * @NoAdminRequired
	 * @PasswordConfirmationRequired
	 *
	 * @param int $state
	 * @param string|null $code for verification
	 */
	public function enable(int $state, ?string $code = null): JSONResponse {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new \Exception('user not available');
		}
		switch ($state) {
			case IEMailService::STATE_DISABLED:
				$this->emailService->deleteTwoFactorEMail($user);
				return new JSONResponse([
					'state' => IEMailService::STATE_DISABLED,
				]);
			case IEMailService::STATE_CREATED:
				$authenticationCode = $this->secureRandom->generate(6, ISecureRandom::CHAR_DIGITS);
				$dbTwoFactorEMail = $this->emailService->createTwoFactorEMail($user);
				$targetEMailAddress = $this->emailService->setAndSendAuthCode($user, $authenticationCode);
				return new JSONResponse([
					'state' => $targetEMailAddress ? IEMailService::STATE_CREATED : IEMailService::STATE_DISABLED,
					'email' => $targetEMailAddress,
				]);
			case IEMailService::STATE_ENABLED:
				if ($code === null) {
					throw new InvalidArgumentException('code is missing');
				}
				$success = $this->emailService->enable($user, $code);
				return new JSONResponse([
					'state' => $success ? IEMailService::STATE_ENABLED : IEMailService::STATE_CREATED,
				]);
			default:
				throw new InvalidArgumentException('Invalid email state');
		}
	}
}
