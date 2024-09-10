<?php

declare(strict_types = 1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author 2024 [ernolf] Raphael Gradenwitz <raphael.gradenwitz@googlemail.com>
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

namespace OCA\TwoFactorTOTP\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getSecret()
 * @method void setSecret(string $secret)
 * @method int getState()
 * @method void setState(int $state)
 * @method int getLastCounter()
 * @method void setLastCounter(int $counter)
 * @method int getAlgorithm()
 * @method void setAlgorithm(int $algorithm)
 * @method int getDigits()
 * @method void setDigits(int $length)
 * @method int getSeconds()
 * @method void setSeconds(int $seconds)
 */
class TotpSecret extends Entity {

	/** @var string */
	protected $userId;

	/** @var string */
	protected $secret;

	/** @var int */
	protected $state;

	/** @var int */
	protected $lastCounter;

	/** @var int */
	protected $algorithm;

	/** @var int */
	protected $digits;

	/** @var int */
	protected $seconds;

	public function __construct() {
		$this->addType('userId', 'string');
		$this->addType('secret', 'string');
		$this->addType('state', 'int');
		$this->addType('lastCounter', 'int');
		$this->addType('algorithm', 'int');
		$this->addType('digits', 'int');
		$this->addType('seconds', 'int');
	}
}
