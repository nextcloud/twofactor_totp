<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Service;

use OCP\Security\ISecureRandom;

class NumericalCodeGenerator implements ICodeGenerator {
	public function __construct(
		private ISecureRandom $secureRandom,
	) {
	}

	public function generateChallengeCode(): string {
		return $this->secureRandom->generate(6, ISecureRandom::CHAR_DIGITS);
	}
}
