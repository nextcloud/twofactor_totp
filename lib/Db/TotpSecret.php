<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
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

namespace OCA\TwoFactor_Totp\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getSecret()
 * @method void setSecret(string $secret)
 * @method boolean getVerified()
 * @method void setVerified(bool $verified)
 * @method string getLastValidatedKey()
 * @method void setLastValidatedKey(string $lastValidatedKey)
 */
class TotpSecret extends Entity {

	/** @var string */
	protected $userId;

	/** @var string */
	protected $secret;

	/** @var string */
	protected $lastValidatedKey;

	/** @var boolean */
	protected $verified;
}
