<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SA (https://www.cipherguard.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.com Cipherguard(tm)
 * @since         3.3.0
 */

namespace Cipherguard\JwtAuthentication\Notification\Email\Redactor;

use App\Notification\Email\AbstractSubscribedEmailRedactorPool;

class JwtAuthenticationEmailRedactorPool extends AbstractSubscribedEmailRedactorPool
{
    /**
     * Return true if the redactor pool is enabled
     *
     * @return bool
     */
    private function isJwtAuthRedactorEnabled(): bool
    {
        // TODO: Complete this logic with settings when implemented.
        return true;
    }

    /**
     * @return \App\Notification\Email\SubscribedEmailRedactorInterface[]
     */
    public function getSubscribedRedactors()
    {
        $redactors = [];

        if ($this->isJwtAuthRedactorEnabled()) {
            $redactors[] = new JwtAuthenticationAttackEmailRedactor();
        }

        return $redactors;
    }
}
