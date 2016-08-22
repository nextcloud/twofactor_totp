<?php

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

namespace OCA\TwoFactor_Totp\Controller;

use Endroid\QrCode\QrCode;
use OCA\TwoFactor_Totp\Service\ITotp;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Defaults;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;

class SettingsController extends Controller {

	/** @var ITotp */
	private $totp;

	/** @var IUserSession */
	private $userSession;

	/** @var Defaults */
	private $defaults;

	/** @var IURLGenerator */
	private $urlGenerator;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IUserSession $userSession
	 * @param ITotp $totp
	 * @param Defaults $defaults
	 * @param IURLGenerator $urlGenerator
	 */
	public function __construct($appName, IRequest $request, IUserSession $userSession, ITotp $totp, Defaults $defaults, IURLGenerator $urlGenerator) {
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
			$secretName = $this->getSecretName();
			$issuer = $this->getSecretIssuer();
			$qr = $qrCode->setText("otpauth://totp/$secretName?secret=$secret&issuer=$issuer")
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

	private function getSecretName() {
		$userName = $this->userSession->getUser()->getCloudId();
		return rawurlencode($userName);
	}

	private function getSecretIssuer() {
		$productName = $this->defaults->getName();
		$url = $this->urlGenerator->getAbsoluteURL('/');
		return rawurlencode("$url ($productName)");
	}

}
