<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Activity;

use InvalidArgumentException;
use OCA\TwoFactorEMail\AppInfo\Application;
use OCP\Activity\Exceptions\UnknownActivityException;
use OCP\Activity\IEvent;
use OCP\Activity\IProvider;
use OCP\IURLGenerator;
use OCP\L10N\IFactory as L10nFactory;

class Provider implements IProvider {

	public function __construct(
		private L10nFactory $l10n,
		private IURLGenerator $urlGenerator,
	) {
	}

	public function parse($language, IEvent $event, ?IEvent $previousEvent = null): IEvent {
		if ($event->getApp() !== Application::APP_ID) {
			throw new UnknownActivityException();
		}

		$l = $this->l10n->get(Application::APP_ID, $language);

		$event->setIcon($this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('core', 'actions/password.svg')));
		switch ($event->getSubject()) {
			case 'twofactor_email_enabled_subject':
				$event->setSubject($l->t('You enabled e-mail two-factor authentication for your account'));
				break;
			case 'twofactor_email_disabled_subject':
				$event->setSubject($l->t('You disabled e-mail two-factor authentication for your account'));
				break;
			case 'twofactor_email_disabled_by_admin':
				$event->setSubject($l->t('E-mail two-factor authentication disabled by an admin'));
				break;
			default:
				throw new UnknownActivityException();
		}
		return $event;
	}
}
