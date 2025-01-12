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
 * @since         3.1.0
 */
namespace Cipherguard\WebInstaller;

use App\Service\Healthcheck\HealthcheckServiceCollector;
use App\Service\Healthcheck\Ssl\IsRequestHttpsSslHealthcheck;
use Cake\Core\BasePlugin;
use Cake\Core\ContainerInterface;
use Cake\Http\MiddlewareQueue;
use Cipherguard\WebInstaller\Middleware\WebInstallerMiddleware;
use Cipherguard\WebInstaller\Service\Healthcheck\CipherguardConfigWritableWebInstallerHealthcheck;
use Cipherguard\WebInstaller\Service\Healthcheck\PrivateKeyWritableWebInstallerHealthcheck;
use Cipherguard\WebInstaller\Service\Healthcheck\PublicKeyWritableWebInstallerHealthcheck;
use Cipherguard\WebInstaller\Service\WebInstallerChangeConfigFolderPermissionService;

class WebInstallerPlugin extends BasePlugin
{
    /**
     * @inheritDoc
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        return $middlewareQueue->add(WebInstallerMiddleware::class);
    }

    /**
     * @inheritDoc
     */
    public function services(ContainerInterface $container): void
    {
        $container
            ->add(
                WebInstallerChangeConfigFolderPermissionService::class,
                WebInstallerChangeConfigFolderPermissionService::class
            )
            ->addArgument(CONFIG);

        $container->add(CipherguardConfigWritableWebInstallerHealthcheck::class);
        $container->add(PublicKeyWritableWebInstallerHealthcheck::class);
        $container->add(PrivateKeyWritableWebInstallerHealthcheck::class);

        $container
            ->extend(HealthcheckServiceCollector::class)
            ->addMethodCall('addService', [CipherguardConfigWritableWebInstallerHealthcheck::class])
            ->addMethodCall('addService', [PublicKeyWritableWebInstallerHealthcheck::class])
            ->addMethodCall('addService', [PrivateKeyWritableWebInstallerHealthcheck::class])
            ->addMethodCall('addService', [IsRequestHttpsSslHealthcheck::class]);
    }
}
