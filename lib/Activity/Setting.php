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

namespace OCA\TwoFactorTOTP\Activity;

use OCP\Activity\ISetting;

class Setting implements ISetting {

	public function canChangeMail() {
		return false;
	}

	public function canChangeStream() {
		return false;
	}

	public function getIdentifier() {
		return 'twofactor_totp';
	}

	public function getName() {
		return 'TOTP 2FA';
	}

	public function getPriority() {
		return 10;
	}

	public function isDefaultEnabledMail() {
		return true;
	}

	public function isDefaultEnabledStream() {
		return true;
	}

}
