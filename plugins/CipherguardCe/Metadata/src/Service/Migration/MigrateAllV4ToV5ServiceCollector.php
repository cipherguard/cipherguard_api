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
 * @since         4.10.0
 */
namespace Cipherguard\Metadata\Service\Migration;

class MigrateAllV4ToV5ServiceCollector
{
    private static array $services = [];

    /**
     * Add new migrator service to collector.
     *
     * @param array|string $services Service(s) to add.
     * @return void
     */
    public static function add($services): void
    {
        if (is_string($services)) {
            $services = [$services];
        }

        foreach ($services as $service) {
            self::$services[] = $service;
        }
    }

    /**
     * @return string[]
     */
    public static function get(): array
    {
        return self::$services;
    }

    /**
     * @return void
     */
    public static function clear(): void
    {
        self::$services = [];
    }
}
