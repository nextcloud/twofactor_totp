<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * ownCloud - Two-factor TOTP
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

namespace OCA\TwoFactorTotp\Service;

use OCA\TwoFactorTotp\Exception\TotpSecretAlreadySet;
use OCP\IUser;

interface ITotp {

    /**
     * @param IUser $user
     */
    public function getSecret(IUser $user);

    /**
     * @param IUser $user
     * @throws TotpSecretAlreadySet
     */
    public function createSecret(IUser $user);
    
    /**
     * @param IUser $user
     * @param string $key
     */
    public function validateSecret(IUser $user, $key);
    
}
