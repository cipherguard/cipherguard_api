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
 * @since         3.11.0
 */

namespace Cipherguard\MultiFactorAuthentication\Model\Dto;

use App\Model\Entity\AuthenticationToken;

/**
 * Class MfaDuoAuthenticationRequestDto
 */
class MfaDuoAuthenticationRequestDto
{
    /**
     * @var \App\Model\Entity\AuthenticationToken|null $authenticationToken
     */
    public $authenticationToken;

    /**
     * @var string|null duoAuthenticationUrl
     */
    public $duoAuthenticationUrl;

    /**
     * Construct the Dto based on array as source.
     *
     * @param \App\Model\Entity\AuthenticationToken $authenticationToken The data source array to extract the information from.
     * @param string $duoAuthenticationUrl The duo authentication url.
     */
    public function __construct(AuthenticationToken $authenticationToken, string $duoAuthenticationUrl)
    {
        $this->authenticationToken = $authenticationToken;
        $this->duoAuthenticationUrl = $duoAuthenticationUrl;
    }
}
