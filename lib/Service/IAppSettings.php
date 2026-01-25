<?php

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Service;

interface IAppSettings {
	/**
	 * How long shall a stored 2FA code be valid.
	 *
	 * @return int seconds of validity
	 */
	public function getCodeValidSeconds(): int;

	/**
	 * How many e-mails may be sent during a certain period.
	 *
	 * @return int number of attempts allowed
	 */
	public function getSendRateLimitAttempts(): int;
	/**
	 * Period in which the defined amount of e-mails may be sent.
	 *
	 * @return int seconds of sliding window
	 */
	public function getSendRateLimitPeriodSeconds(): int;
}
