<?php

declare(strict_types = 1);

/**
 * @author Nico Kluge <nico.kluge@klugecoded.com>
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

namespace OCA\TwoFactorEMail\Db;

use OCP\AppFramework\Db\Entity;
use OCP\IUser;

/**
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string|null getEmail()
 * @method void setEmail(string|null $email)
 * @method int getState()
 * @method void setState(int $state)
 * @method string|null getAuthCode()
 * @method void setAuthCode(string|null $authCode)
 */
class TwoFactorEMail extends Entity {

	/** @var string */
	protected $userId;

	/** @var string|null */
	protected $email;

	/** @var int */
	protected $state;

	/** @var string|null */
	protected $authCode;

	public function __construct() {
		$this->addType('userId', 'string');
		$this->addType('email', 'string');
		$this->addType('state', 'int');
		$this->addType('authCode', 'string');
	}

	/**
	 * @param IUser $user
	 * @return string|null
	 */
	public function getEMailAddress(IUser $user): ?string {
		return $this->email ?? $user->getEMailAddress();
	}
}
