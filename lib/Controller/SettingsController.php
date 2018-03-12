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

use Endroid\QrCode\QrCode;
use InvalidArgumentException;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Defaults;
use OCP\IRequest;
use OCP\IUserSession;
use function is_null;

class SettingsController extends Controller {

	/** @var ITotp */
	private $totp;

	/** @var IUserSession */
	private $userSession;

	/** @var Defaults */
	private $defaults;

	public function __construct(string $appName, IRequest $request, IUserSession $userSession, ITotp $totp, Defaults $defaults) {
		parent::__construct($appName, $request);
		$this->userSession = $userSession;
		$this->totp = $totp;
		$this->defaults = $defaults;
	}

	/**
	 * @NoAdminRequired
	 * @return JSONResponse
	 */
	public function state(): JSONResponse {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new Exception('user not available');
		}
		return new JSONResponse([
			'state' => $this->totp->hasSecret($user),
		]);
	}

	/**
	 * @NoAdminRequired
	 * @PasswordConfirmationRequired
	 * @param int $state
	 * @param string|null $key for verification
	 */
	public function enable(int $state, string $key = null): JSONResponse {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new Exception('user not available');
		}
		switch ($state) {
			case ITotp::STATE_DISABLED:
				$this->totp->deleteSecret($user);
				return new JSONResponse([
					'state' => ITotp::STATE_DISABLED,
				]);
			case ITotp::STATE_CREATED:
				$secret = $this->totp->createSecret($user);

				$qrCode = new QrCode();
				$secretName = $this->getSecretName();
				$issuer = $this->getSecretIssuer();
				$qr = $qrCode->setText("otpauth://totp/$secretName?secret=$secret&issuer=$issuer")
					->setSize(150)
					->writeDataUri();
				return new JSONResponse([
					'state' => ITotp::STATE_CREATED,
					'secret' => $secret,
					'qr' => $qr,
				]);
			case ITotp::STATE_ENABLED:
				$success = $this->totp->enable($user, $key);
				return new JSONResponse([
					'state' => $success ? ITotp::STATE_ENABLED : ITotp::STATE_CREATED,
				]);
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
		$userName = $this->userSession->getUser()->getCloudId();
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
