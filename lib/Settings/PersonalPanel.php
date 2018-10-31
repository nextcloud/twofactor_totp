<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
 * @license AGPL-3.0
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

namespace OCA\TwoFactor_Totp\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\Template;

class PersonalPanel implements ISettings {

	/**
	 * The panel controller method that returns a template to the UI
	 *
	 * @since 10.0
	 * @return TemplateResponse | Template
	 */
	public function getPanel() {
		return new \OCP\Template('twofactor_totp', 'personal');
	}

	/**
	 * A string to identify the section in the UI / HTML and URL
	 *
	 * @since 10.0
	 * @return string
	 */
	public function getSectionID() {
		return 'security';
	}

	/**
	 * The number used to order the section in the UI.
	 *
	 * @since 10.0
	 * @return int between 0 and 100, with 100 being the highest priority
	 */
	public function getPriority() {
		return 50;
	}
}
