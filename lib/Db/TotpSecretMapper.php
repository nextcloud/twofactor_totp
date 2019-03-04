<?php

declare(strict_types = 1);

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

namespace OCA\TwoFactorTOTP\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\IUser;

class TotpSecretMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'twofactor_totp_secrets');
	}

	/**
	 * @param IUser $user
	 * @throws DoesNotExistException
	 * @return TotpSecret
	 */
	public function getSecret(IUser $user): TotpSecret {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();

		$qb->select('id', 'user_id', 'secret', 'state', 'last_counter')
			->from($this->getTableName())
			->from('twofactor_totp_secrets')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($user->getUID())));
		$result = $qb->execute();

		$row = $result->fetch();
		$result->closeCursor();
		if ($row === false) {
			throw new DoesNotExistException('Secret does not exist');
		}
		return TotpSecret::fromRow($row);
	}

}
