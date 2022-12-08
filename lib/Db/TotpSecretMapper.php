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

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Mapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\IUser;

class TotpSecretMapper extends Mapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'twofactor_totp_secrets');
	}

	/**
	 * @param IUser $user
	 * @throws DoesNotExistException
	 * @return TotpSecret
	 */
	public function getSecret(IUser $user) {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();

		$qb->select('id', 'user_id', 'secret', 'verified', 'last_validated_key')
				->from('twofactor_totp_secrets')
				->where($qb->expr()->eq('user_id', $qb->createNamedParameter($user->getUID())));
		$result = $qb->execute();

		/* @phan-suppress-next-line PhanDeprecatedFunction */
		$row = $result->fetch();
		/* @phan-suppress-next-line PhanDeprecatedFunction */
		$result->closeCursor();
		if ($row === false) {
			throw new DoesNotExistException('Secret does not exist');
		}
		return TotpSecret::fromRow($row);
	}

	/**
	 * @param boolean $status
	 */
	public function setAllSecretsVerificationStatus($status) {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->update('twofactor_totp_secrets')
			->set('verified', $qb->createNamedParameter($status, IQueryBuilder::PARAM_BOOL))
			->execute();
	}

	/**
	 * @param string $uid
	 */
	public function deleteSecretsByUserId($uid) {
		$qb = $this->db->getQueryBuilder();
		$qb->delete('twofactor_totp_secrets')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($uid)))
			->execute();
	}

	public function getAllSecrets() {
		$qb = $this->db->getQueryBuilder();
		/* @phan-suppress-next-line PhanDeprecatedFunction */
		return $qb ->select('*')
			->from('twofactor_totp_secrets')
			->execute()
			->fetchAll();
	}
}
