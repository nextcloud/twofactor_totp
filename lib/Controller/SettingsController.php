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

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use OCA\TwoFactor_Totp\Service\ITotp;
use OCP\AppFramework\Controller;
use OCP\Defaults;
use OCP\IRequest;
use OCP\IUserSession;

class SettingsController extends Controller {

	/** @var ITotp */
	private $totp;

	/** @var IUserSession */
	private $userSession;

	/** @var Defaults */
	private $defaults;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IUserSession $userSession
	 * @param ITotp $totp
	 * @param Defaults $defaults
	 */
	public function __construct($appName, IRequest $request, IUserSession $userSession, ITotp $totp, Defaults $defaults) {
		parent::__construct($appName, $request);
		$this->userSession = $userSession;
		$this->totp = $totp;
		$this->defaults = $defaults;
	}

	/**
	 * @NoAdminRequired
	 * @return array
	 */
	public function state() {
		$user = $this->userSession->getUser();
		return [
			'enabled' => $this->totp->hasSecret($user) && $this->totp->isVerified($user),
		];
	}

	/**
	 * @NoAdminRequired
	 * @param bool $state
	 * @return array
	 */
	public function enable($state) {
		$user = $this->userSession->getUser();
		if ($state) {
			$secret = $this->totp->createSecret($user);

			return [
				'enabled' => true,
				'secret' => $secret,
				'qr' => $this->generateBase64EncodedQrImage($secret)
			];
		}

		$this->totp->deleteSecret($user);
		return [
			'enabled' => false,
		];
	}

	/**
	 * @NoAdminRequired
	 * @param string $challenge
	 * @return array
	 */
	public function verifyNewSecret($challenge) {
		$user = $this->userSession->getUser();
		return [
			'verified' => $this->totp->verifySecret($user, $challenge)
		];
	}

	/**
	 * The user's cloud id, e.g. "christina@university.domain/owncloud"
	 *
	 * @return string
	 */
	private function getSecretName() {
		$productName = $this->defaults->getName();
		$userName = $this->userSession->getUser()->getCloudId();
		return \rawurlencode("$productName:$userName");
	}

	/**
	 * The issuer, e.g. "Nextcloud" or "ownCloud"
	 *
	 * @return string
	 */
	private function getSecretIssuer() {
		$productName = $this->defaults->getName();
		return \rawurlencode($productName);
	}

	/**
	 * Generate base64 encoded png image
	 *
	 * @param string $secret
	 * @return string
	 */
	private function generateBase64EncodedQrImage($secret) {
		$secretName = $this->getSecretName();
		$issuer = $this->getSecretIssuer();
		$data = "otpauth://totp/$secretName?secret=$secret&issuer=$issuer";
		$renderer = new ImageRenderer(
			new RendererStyle(170),
			new ImagickImageBackEnd()
		);
		$writer = new Writer($renderer);
		return 'data:image/png;base64,' . \base64_encode($writer->writeString($data));
	}
}
