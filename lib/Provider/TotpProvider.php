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

namespace OCA\TwoFactorTOTP\Provider;

use OCA\TwoFactorTOTP\Service\ITotp;
use OCP\Authentication\TwoFactorAuth\IProvider;
use OCP\IL10N;
use OCP\IUser;
use OCP\Template;

class TotpProvider implements IProvider {

    /** @var ITotp */
    private $totp;

    /** @var IL10N */
    private $l10n;

    private $type = 0;

    /**
     * @param Totp $totp
     * @param IL10N $l10n
     */
    public function __construct(ITotp $totp, IL10N $l10n) {
        $this->totp = $totp;
        $this->l10n = $l10n;

        $this->defaults = new \OCP\Defaults(); // @TODO: Get current OC Defaults
        $this->type = $type = \OCP\Config::getAppValue('twofactor_totp', 'totp_type', 0);
    }

    /**
     * Get unique identifier of this 2FA provider
     *
     * @return string
     */
    public function getId() {
        return 'totp';
    }

    /**
     * Get the display name for selecting the 2FA provider
     *
     * @return string
     */
    public function getDisplayName() {
        return 'TOTP (Google Authenticator)';
    }

    /**
     * Get the description for selecting the 2FA provider
     *
     * @return string
     */
    public function getDescription() {
        return $this->l10n->t('Authenticate with a TOTP app');
    }

    /**
     * Get the template for rending the 2FA provider view
     *
     * @param IUser $user
     * @return Template
     */
    public function getTemplate(IUser $user) {
        $tmpl = new Template('twofactor_totp', 'challenge');
        if(!$this->totp->hasSecret($user)){
            $tmpl->assign('new_totp_user', 1);

            $secret = $this->totp->createSecret($user);
            $qrCode = new \Endroid\QrCode\QrCode();
            $secretName = $this->getSecretName($user);
            $issuer = $this->getSecretIssuer();
            $qr = $qrCode->setText("otpauth://totp/$secretName?secret=$secret&issuer=$issuer")
                ->setSize(150)
                ->getDataUri();

            $tmpl->assign('secret', $secret);
            $tmpl->assign('qr_src', $qr);
        }
        return $tmpl;
    }

    /**
     * Verify the given challenge
     *
     * @param IUser $user
     * @param string $challenge
     */
    public function verifyChallenge(IUser $user, $challenge) {
        return $this->totp->validateSecret($user, $challenge);
    }

    /**
     * Decides whether 2FA is enabled for the given user
     *
     * @param IUser $user
     * @return boolean
     */
    public function isTwoFactorAuthEnabledForUser(IUser $user) {
        if($this->isTwoFactorAuthForcedForUser($user)) return true;
        return $this->totp->hasSecret($user);
    }

    /**
	 * Decides whether 2FA is forces for the fiven user
	 *
     * @param IUser $user
     * @return boolean
	 */
    public function isTwoFactorAuthForcedForUser(IUser $user){
        $type = $this->type;

        if($type == 1 || $type == 2){
            $list = $this->getUserList();
            $inList = in_array($user->getDisplayName(), $list);

            if($type == 1 && $inList) return true;
            if($type == 2 && !$inList) return true;
        }

        return false;
    }

    /**
     * Returns an array of users who are forced to use 2FA
     *
     * @return Array
     */
    private function getUserList(){
        // @TODO: Get Users from DB (+ get users in groups)
        return [];
    }

    /**
	 * The user's cloud id, e.g. "christina@university.domain/owncloud"
	 *
	 * @return string
	 */
	private function getSecretName($user) {
		$productName = $this->defaults->getName();
		$userName = $user->getCloudId();
		return rawurlencode("$productName:$userName");
	}

	/**
	 * The issuer, e.g. "Nextcloud" or "ownCloud"
	 *
	 * @return string
	 */
	private function getSecretIssuer() {
		$productName = $this->defaults->getName();
		return rawurlencode($productName);
	}
}
