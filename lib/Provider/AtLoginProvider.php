<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorTOTP\Provider;

use OCA\TwoFactorTOTP\AppInfo\Application;
use OCP\Authentication\TwoFactorAuth\ILoginSetupProvider;
use OCP\IURLGenerator;
use OCP\Template;

class AtLoginProvider implements ILoginSetupProvider {

	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(IURLGenerator $urlGenerator) {
		$this->urlGenerator = $urlGenerator;
	}

	public function getBody(): Template {
		$template = new Template(Application::APP_ID, 'loginsetup');
		$template->assign('urlGenerator', $this->urlGenerator);
		return $template;
	}
}
