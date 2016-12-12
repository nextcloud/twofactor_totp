<?php

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

namespace OCA\TwoFactorTOTP\Activity;

use InvalidArgumentException;
use OCP\Activity\IEvent;
use OCP\Activity\IProvider;
use OCP\ILogger;
use OCP\L10N\IFactory as L10nFactory;

class Provider implements IProvider {

	/** @var L10nFactory */
	private $l10n;

	/** @var ILogger */
	private $logger;

	public function __construct(L10nFactory $l10n, ILogger $logger) {
		$this->logger = $logger;
		$this->l10n = $l10n;
	}

	public function parse($language, IEvent $event, IEvent $previousEvent = null) {
		if ($event->getApp() !== 'twofactor_totp') {
			throw new InvalidArgumentException();
		}

		$l = $this->l10n->get('twofactor_totp', $language);

		switch ($event->getSubject()) {
			case 'totp_enabled_subject':
				$event->setSubject($l->t('TOTP enabled'));
				$event->setMessage($l->t('You enabled TOTP two-factor authentication for your account.'));
				break;
			case 'totp_disabled_subject':
				$event->setSubject($l->t('TOTP disabled'));
				$event->setMessage($l->t('You disabled TOTP two-factor authentication for your account.'));
				break;
			case 'totp_success_subject':
				$event->setSubject($l->t('TOTP code used'));
				$event->setMessage($l->t('A TOTP code was used to log into your account.'));
				break;
			case 'totp_error_subject':
				$event->setSubject($l->t('Invalid TOTP code used'));
				$event->setMessage($l->t('An invalid TOTP code was used to log into your account.'));
				break;
		}
		return $event;
	}

}
