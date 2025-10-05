<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Provider;

use OCA\TwoFactorEMail\AppInfo\Application;
use OCP\Authentication\TwoFactorAuth\ILoginSetupProvider;
use OCP\IURLGenerator;
use OCP\Template\ITemplate;
use OCP\Template\ITemplateManager;

final class LoginSetup implements ILoginSetupProvider {

	public function __construct(
		private IURLGenerator $urlGenerator,
		private ITemplateManager $templateManager,
	) {
	}

	public function getBody(): ITemplate {
		$template = $this->templateManager->getTemplate(Application::APP_ID, 'LoginSetup');
		$template->assign('urlGenerator', $this->urlGenerator);
		return $template;
	}
}
