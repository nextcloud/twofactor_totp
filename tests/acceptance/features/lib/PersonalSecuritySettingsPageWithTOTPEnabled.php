<?php
/**
 * ownCloud
 *
 * @author Saugat Pachhai <saugat@jankaritech.com>
 * @copyright Copyright (c) 2019 Saugat Pachhai saugat@jankaritech.com
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License,
 * as published by the Free Software Foundation;
 * either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace Page;

/**
 * Class PersonalSecuritySettingsPageWithTOTPEnabled
 *
 * @package Page
 */
class PersonalSecuritySettingsPageWithTOTPEnabled extends PersonalSecuritySettingsPage {
	private $activateTOTPLabelXpath = '//label[@for="totp-enabled"]';
	private $qrCodeImageXpath = '//div[@id="twofactor-totp-settings"]//img';

	/**
	 * Activate TOTP for the user
	 *
	 * @return void
	 */
	public function activateTOTP() {
		$label = $this->waitTillElementIsNotNull($this->activateTOTPLabelXpath);
		$this->assertElementNotNull(
			$label,
			__METHOD__ . " Label not found to activate TOTP"
		);
		$label->click();

		$this->waitTillElementIsNotNull($this->qrCodeImageXpath);
	}
}
