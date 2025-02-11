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
namespace Cipherguard\JwtAuthentication\Authenticator;

use App\Model\Entity\User;
use Cake\Http\ServerRequest;

interface JwtArmoredChallengeInterface
{
    /**
     * Create the data in the challenge returned on JWT authenticated login.
     * For non-standard JWT Authentication, e.g. when authenticating with MFA,
     * some additional fields may be added to the challenge.
     *
     * @param \Cake\Http\ServerRequest $request Server request.
     * @param \App\Model\Entity\User $user User successfully authenticated.
     * @param string $verifyToken Verify token provided by the client.
     * @return array
     */
    public function makeArmoredChallenge(ServerRequest $request, User $user, string $verifyToken): array;
}
