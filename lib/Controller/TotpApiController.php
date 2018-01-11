<?php

/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
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

namespace OCA\TwoFactor_Totp\Controller;

use OCA\TwoFactor_Totp\Exception\NoTotpSecretFoundException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\ILogger;
use \OCP\IRequest;
use \OCP\IUserManager;
use OCA\TwoFactor_Totp\Service\ITotp;

class TotpApiController extends OCSController {

	/** @var ITotp */
	private $totp;

	/** @var IUserManager */
	private $userManager;

	/** @var ILogger */
	private $logger;

	public function __construct(
		$appName,
		IRequest $request,
		ITotp $totp,
		IUserManager $userManager,
		ILogger $logger
	) {
		parent::__construct($appName, $request);
		$this->totp = $totp;
		$this->userManager = $userManager;
		$this->logger = $logger;
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 *
	 * @param string $uid
	 * @param string $key 6 digits numeric time-based one time password.
	 * @return DataResponse
	 */
	public function validateKey($uid, $key) {
		$user = $this->userManager->get($uid);
		if ($user !== null) {
			try {
				return new DataResponse(['data' => ['result' => $this->totp->validateKey($user, $key)]]);
			} catch (NoTotpSecretFoundException $e) {
				$this->logger->logException($e);
			}
		}
		return new DataResponse(
			[
				'statuscode' => 404,
				'data' => ['result' => false]
			],
			Http::STATUS_NOT_FOUND
		);
	}
}
