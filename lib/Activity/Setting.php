<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Activity;

use OCP\Activity\ISetting;
use OCP\IL10N;
use Override;

class Setting implements ISetting {

	public function __construct(
		private IL10N $l10n,
	) {
	}

	#[Override]
	public function canChangeMail(): bool {
		return false;
	}

	#[Override]
	public function canChangeStream(): bool {
		return false;
	}

	#[Override]
	public function getIdentifier(): string {
		return 'twofactor_totp';
	}

	#[Override]
	public function getName(): string {
		return $this->l10n->t('TOTP (Authenticator app)');
	}

	#[Override]
	public function getPriority(): int {
		return 10;
	}

	#[Override]
	public function isDefaultEnabledMail(): bool {
		return true;
	}

	#[Override]
	public function isDefaultEnabledStream(): bool {
		return true;
	}
}
