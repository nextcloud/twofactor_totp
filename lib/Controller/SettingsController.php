<?php

declare(strict_types = 1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Controller;

use OCA\TwoFactorEMail\Service\IStateManager;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Authentication\TwoFactorAuth\ALoginSetupController;
use OCP\IRequest;
use OCP\IUserSession;

class SettingsController extends ALoginSetupController {

	public function __construct(
		string                      $appName,
		IRequest                    $request,
		private IUserSession        $userSession,
		private IStateManager       $stateManager,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	#[PasswordConfirmationRequired]
	public function setState(bool $state): JSONResponse {
		$user = $this->userSession->getUser();
		if ($state) {
			if (empty($user->getEMailAddress())) {
				return new JSONResponse([
					'error' => 'no-email',
					'enabled' => false,
				]);
			} else {
				$this->stateManager->enable($user);
			}
		} else {
			$this->stateManager->disable($user);
		}
		return new JSONResponse([
			'enabled' => $state,
		]);
	}
}
