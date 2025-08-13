<?php

declare(strict_types = 1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
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
		$this->addType('state', 'integer');
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
