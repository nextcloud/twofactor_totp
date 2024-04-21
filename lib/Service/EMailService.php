<?php

declare(strict_types=1);

/**
 * @author Nico Kluge <nico.kluge@klugecoded.com>
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

namespace OCA\TwoFactorEMail\Service;

use OCA\TwoFactorEMail\Db\TwoFactorEMail;
use OCA\TwoFactorEMail\Db\TwoFactorEMailMapper;
use OCA\TwoFactorEMail\Event\DisabledByAdmin;
use OCA\TwoFactorEMail\Event\StateChanged;
use OCA\TwoFactorEMail\Exception\NoTwoFactorEMailFoundException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Defaults;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IL10N;
use OCP\IUser;
use OCP\Mail\IMailer;
use Psr\Log\LoggerInterface;

class EMailService implements IEMailService {

	/** @var TwoFactorEMailMapper */
	private $twoFactorEMailMapper;

	/** @var IEventDispatcher */
	private $eventDispatcher;

	/** @var LoggerInterface */
	private $logger;

	/** @var IL10N */
	private $l10n;

	/** @var IMailer */
	private $mailer;

	/** @var Defaults */
	private $defaults;

	public function __construct(TwoFactorEMailMapper $twoFactorEMailMapper,
								IEventDispatcher     $eventDispatcher,
								LoggerInterface      $logger,
								IL10N                $l10n,
								IMailer              $mailer,
								Defaults             $defaults
	) {
		$this->twoFactorEMailMapper = $twoFactorEMailMapper;
		$this->eventDispatcher = $eventDispatcher;
		$this->logger = $logger;
		$this->l10n = $l10n;
		$this->mailer = $mailer;
		$this->defaults = $defaults;
	}

	public function isEnabled(IUser $user): bool {
		try {
			$dbTwoFactorEmail = $this->twoFactorEMailMapper->getTwoFactorEMail($user);
			return IEMailService::STATE_ENABLED === (int)$dbTwoFactorEmail->getState();
		} catch (DoesNotExistException $ex) {
			return false;
		}
	}

	/**
	 * @param IUser $user
	 */
	public function createTwoFactorEMail(IUser $user, string $email = null): TwoFactorEMail {
		try {
			// Delete existing one
			$dbTwoFactorEmail = $this->twoFactorEMailMapper->getTwoFactorEMail($user);
			$this->twoFactorEMailMapper->delete($dbTwoFactorEmail);
		} catch (DoesNotExistException $ex) {
			// Ignore
		}

		$dbTwoFactorEMail = new TwoFactorEMail();
		$dbTwoFactorEMail->setUserId($user->getUID());
		$dbTwoFactorEMail->setEmail($email);
		$dbTwoFactorEMail->setState(IEMailService::STATE_CREATED);

		$this->twoFactorEMailMapper->insert($dbTwoFactorEMail);
		return $dbTwoFactorEMail;
	}

	public function enable(IUser $user, string $key): bool {
		if (!$this->validateTwoFactorEMail($user, $key)) {
			return false;
		}
		$dbTwoFactorEMail = $this->twoFactorEMailMapper->getTwoFactorEMail($user);
		$dbTwoFactorEMail->setState(IEMailService::STATE_ENABLED);
		$this->twoFactorEMailMapper->update($dbTwoFactorEMail);

		$this->eventDispatcher->dispatch(StateChanged::class, new StateChanged($user, true));

		return true;
	}

	public function deleteTwoFactorEMail(IUser $user, bool $byAdmin = false): void {
		try {
			// TODO: execute DELETE sql in mapper instead
			$dbTwoFactorEMail = $this->twoFactorEMailMapper->getTwoFactorEMail($user);
			$this->twoFactorEMailMapper->delete($dbTwoFactorEMail);
		} catch (DoesNotExistException $ex) {
			// Ignore
		}

		if ($byAdmin) {
			$this->eventDispatcher->dispatch(DisabledByAdmin::class, new DisabledByAdmin($user));
		} else {
			$this->eventDispatcher->dispatch(StateChanged::class, new StateChanged($user, false));
		}
	}

	public function validateTwoFactorEMail(IUser $user, string $key): bool {
		try {
			$dbTwoFactorEMail = $this->twoFactorEMailMapper->getTwoFactorEMail($user);
		} catch (DoesNotExistException $ex) {
			throw new NoTwoFactorEMailFoundException();
		}

		return $key === $dbTwoFactorEMail->getAuthCode();
	}

	public function setAndSendAuthCode(IUser $user, string $authenticationCode): string {
		$dbTwoFactorEMail = $this->twoFactorEMailMapper->getTwoFactorEMail($user);

		$dbTwoFactorEMail->setAuthCode($authenticationCode);
		$this->twoFactorEMailMapper->update($dbTwoFactorEMail);

		$email = $dbTwoFactorEMail->getEMailAddress($user);

		if ($email === null) {
			return '';
		}

		$this->logger->debug("sending email message to $email, code: $authenticationCode");

		$template = $this->mailer->createEMailTemplate('twofactor_email.send');
		$user_at_cloud = $user->getDisplayName() . " @ " . $this->defaults->getName();
		$template->setSubject($this->l10n->t('Login attempt for %s', [$user_at_cloud]));
		$template->addHeader();
		$template->addHeading($this->l10n->t('Your two-factor authentication code is: %s', [$authenticationCode]));
		$template->addBodyText($this->l10n->t('If you tried to login, please enter that code on %s. If you did not, somebody else did and knows your your email address or username – and your password!', [$this->defaults->getName()]));
		$template->addFooter();

		$message = $this->mailer->createMessage();
		$message->setTo([ $email => $user->getDisplayName() ]);
		$message->useTemplate($template);

		try {
			$this->mailer->send($message);
		} catch (\Exception $e) {
			$this->logger->error("failed sending email message to $email, code: $authenticationCode");
			throw $e;
		}

		return $email;
	}
}
