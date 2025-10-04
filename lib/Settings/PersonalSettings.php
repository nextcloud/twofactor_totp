<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Settings;

use OCA\TwoFactorEMail\AppInfo\Application;
use OCP\Authentication\TwoFactorAuth\IPersonalProviderSettings;
use OCP\Template\ITemplate;
use OCP\Template\ITemplateManager;

final class PersonalSettings implements IPersonalProviderSettings {

	public function __construct(
		private ITemplateManager $templateManager,
	) {
	}
	public function getBody(): ITemplate {
		return $this->templateManager->getTemplate(Application::APP_ID, 'PersonalSettings');
	}
}
