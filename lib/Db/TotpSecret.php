<?php

declare(strict_types = 1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
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
 * @method int getLastCounter();
 * @method void setLastCounter(int $counter)
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

	public function __construct() {
		$this->addType('userId', 'string');
		$this->addType('secret', 'string');
		$this->addType('state', 'integer');
		$this->addType('lastCounter', 'integer');
	}
}
