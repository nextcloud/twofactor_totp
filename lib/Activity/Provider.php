<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Activity;

use OCA\TwoFactorEMail\AppInfo\Application;
use OCP\Activity\Exceptions\UnknownActivityException;
use OCP\Activity\IEvent;
use OCP\Activity\IProvider;
use OCP\IURLGenerator;
use OCP\L10N\IFactory as L10nFactory;
use ValueError;

final class Provider implements IProvider {
	public function __construct(
		private L10nFactory $l10n,
		private IURLGenerator $urlGenerator,
	) {
	}

	public function parse($language, IEvent $event, ?IEvent $previousEvent = null): IEvent {
		if ($event->getApp() !== Application::APP_ID) {
			throw new UnknownActivityException();
		}
		try {
			$notification = Notification::from($event->getSubject());
		} catch (ValueError $e) {
			throw new UnknownActivityException(previous: $e);
		}

		$l = $this->l10n->get(Application::APP_ID, $language);

		$event->setIcon($this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('core', 'actions/password.svg')));
		$event->setSubject($l->t($notification->getSubjectText()));

		return $event;
	}
}
