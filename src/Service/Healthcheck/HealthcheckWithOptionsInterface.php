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
 * @since         4.7.0
 */

namespace App\Service\Healthcheck;

interface HealthcheckWithOptionsInterface
{
    /**
     * Set additional options for the health check.
     *
     * @param array $options Options to set.
     * @return void
     */
    public function setOptions(array $options): void;

    /**
     * Returns additional options for the health check.
     *
     * @return array
     */
    public function getOptions(): array;
}
