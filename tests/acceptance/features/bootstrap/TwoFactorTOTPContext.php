<?php
/**
 * ownCloud
 *
 * @author Saugat Pachhai <saugat@jankaritech.com>
 * @copyright Copyright (c) 2019 Saugat Pachhai saugat@jankaritech.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\RawMinkContext;
use Page\PersonalSecuritySettingsPageWithTOTPEnabled;

require_once 'bootstrap.php';

/**
 * Context for two factor totp app
 */
class TwoFactorTOTPContext implements Context {
	/**
	 * @var PersonalSecuritySettingsPageWithTOTPEnabled
	 */
	private $personalSecuritySettingsPage;

	/**
	 * WebUIPersonalSecuritySettingsTOTPEnabledContext constructor.
	 *
	 * @param PersonalSecuritySettingsPageWithTOTPEnabled $personalSecuritySettingsPage
	 */
	public function __construct(
		PersonalSecuritySettingsPageWithTOTPEnabled $personalSecuritySettingsPage
	) {
		// $personalSecuritySettingsPage is private, therefore needs to be overridden
		$this->personalSecuritySettingsPage = $personalSecuritySettingsPage;
	}

	/**
	 * @Given /^the user has activated TOTP Second\-factor auth but not verified$/
	 *
	 * @return void
	 */
	public function theUserHasActivatedTOTPSecondFactorAuthButNotVerified() {
		$this->personalSecuritySettingsPage->activateTOTP();
	}
}
