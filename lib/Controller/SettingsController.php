<?php

declare(strict_types = 1);

/**
 * @author Nico Kluge <nico.kluge@klugecoded.com>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * Two-factor email
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

	/** @var IEMailService */
	private $emailService;

	/** @var IUserSession */
	private $userSession;

	/** @var ISecureRandom */
	private $secureRandom;

	public function __construct(string $appName, IRequest $request, IUserSession $userSession, IEMailService $emailService, ISecureRandom $secureRandom) {
		parent::__construct($appName, $request);
		$this->userSession = $userSession;
		$this->emailService = $emailService;
		$this->secureRandom = $secureRandom;
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
	public function enable(int $state, string $code = null): JSONResponse {
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
					throw new InvalidArgumentException("code is missing");
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
