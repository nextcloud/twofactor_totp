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

use Doctrine\DBAL\Statement;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\IUser;

/**
 * @template-extends QBMapper<TotpSecret>
 */
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
		/** @var Statement $result */
		$result = $qb->execute();

		$row = $result->fetch();
		$result->closeCursor();
		if ($row === false) {
			throw new DoesNotExistException('Secret does not exist');
		}
		return TotpSecret::fromRow($row);
	}

	/**
	 * @param string $uid
	 * @throws Exception
	 */
	public function deleteSecretByUserId(string $uid): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($uid)));
		$qb->executeStatement();
	}
}
