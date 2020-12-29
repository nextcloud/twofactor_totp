<?php

declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @copyright Copyright (c) 2016 Christoph Wurst <christoph@winzerhof-wurst.at>
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
use OCP\IURLGenerator;
use OCP\L10N\IFactory as L10nFactory;

class Provider implements IProvider {

	/** @var L10nFactory */
	private $l10n;

	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(L10nFactory $l10n, IURLGenerator $urlGenerator) {
		$this->urlGenerator = $urlGenerator;
		$this->l10n = $l10n;
	}

	public function parse($language, IEvent $event, IEvent $previousEvent = null): IEvent {
		if ($event->getApp() !== 'twofactor_totp') {
			throw new InvalidArgumentException();
		}

		$l = $this->l10n->get('twofactor_totp', $language);

		$event->setIcon($this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('core', 'actions/password.svg')));
		switch ($event->getSubject()) {
			case 'totp_enabled_subject':
				$event->setSubject($l->t('You enabled TOTP two-factor authentication for your account'));
				break;
			case 'totp_disabled_subject':
				$event->setSubject($l->t('You disabled TOTP two-factor authentication for your account'));
				break;
			case 'totp_disabled_by_admin':
				$event->setSubject($l->t('TOTP two-factor authentication disabled by an admin'));
				break;
		}
		return $event;
	}
}
