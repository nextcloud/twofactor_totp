<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * ownCloud - Two-factor TOTP
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

use Endroid\QrCode\QrCode;
use OCA\TwoFactor_Totp\Service\ITotp;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUserSession;

class SettingsController extends Controller {

	/** @var ITotp */
	private $totp;

	/** @var IUserSession */
	private $userSession;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IUserSession $userSession
	 * @param ITotp $totp
	 */
	public function __construct($appName, IRequest $request, IUserSession $userSession, ITotp $totp) {
		parent::__construct($appName, $request);
		$this->userSession = $userSession;
		$this->totp = $totp;
	}

	/**
	 * @NoAdminRequired
	 * @return JSONResponse
	 */
	public function state() {
		$user = $this->userSession->getUser();
		return [
			'enabled' => $this->totp->hasSecret($user),
		];
	}

	/**
	 * @NoAdminRequired
	 * @param bool $state
	 * @return JSONResponse
	 */
	public function enable($state) {
		$user = $this->userSession->getUser();
		if ($state) {
			$secret = $this->totp->createSecret($user);
			
			$qrCode = new QrCode();
			$qr = $qrCode->setText("otpauth://totp/ownCloud%20TOTP?secret=$secret")
				->setSize(150)
				->getDataUri();
			return [
				'enabled' => true,
				'secret' => $secret,
				'qr' => $qr,
			];
		}

		$this->totp->deleteSecret($user);
		return [
			'enabled' => false,
		];
	}

}
