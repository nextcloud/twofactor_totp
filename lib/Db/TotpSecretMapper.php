<?php

declare(strict_types = 1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Db;

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
		$result = $qb->executeQuery();

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
