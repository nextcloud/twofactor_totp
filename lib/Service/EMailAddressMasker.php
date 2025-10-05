<?php

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace OCA\TwoFactorEMail\Service;

final class EMailAddressMasker implements IEMailAddressMasker {
	public function maskForUI(string $emailAddress): string {
		if (!preg_match('/^([^@\s]+)@([^@\s]+)$/', $emailAddress, $m)) {
			return $emailAddress;
		}

		$local = $m[1];
		$domain = $m[2];

		$firstChar = mb_strlen($local) > 0 ? mb_substr($local, 0, 1) : '*';
		$domainParts = explode('.', $domain);

		if (count($domainParts) === 1) {
			return $firstChar . '*@*';
		}

		$tld = $domainParts[count($domainParts) - 1];
		return $firstChar . '*@*.' . $tld;
	}
}
