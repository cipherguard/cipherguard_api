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

namespace Cipherguard\JwtAuthentication\Error\Exception\RefreshToken;

use Cipherguard\JwtAuthentication\Error\Exception\AbstractJwtAttackException;
use Throwable;

/**
 * Exception raised when the refresh token is not associated to any user.
 */
class RefreshTokenNotFoundException extends AbstractJwtAttackException
{
    /**
     * @inheritDoc
     */
    public function __construct(?string $message = null, ?int $code = null, ?Throwable $previous = null)
    {
        if (empty($message)) {
            $message = __('No active refresh token matching the request could be found.');
        }
        parent::__construct($message, $code, $previous);
    }
}
