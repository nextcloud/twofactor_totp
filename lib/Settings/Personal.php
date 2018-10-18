<?php

declare(strict_types=1);

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

namespace OCA\TwoFactorTOTP\Settings;

use OCP\Authentication\TwoFactorAuth\IPersonalProviderSettings;
use OCP\Template;

class Personal implements IPersonalProviderSettings {

	/** @var int */
	private $state;

	public function __construct(int $state) {
		$this->state = $state;
	}

	public function getBody(): Template {
		$template = new Template('twofactor_totp', 'personal');
		$template->assign('state', $this->state);
		return $template;
	}
}
