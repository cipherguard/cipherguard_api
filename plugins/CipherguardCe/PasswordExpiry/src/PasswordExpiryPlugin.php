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
 * @since         4.5.0
 */
namespace Cipherguard\PasswordExpiry;

use App\Service\Resources\PasswordExpiryValidationServiceInterface;
use App\Service\Resources\ResourcesExpireResourcesServiceInterface;
use App\Utility\Application\FeaturePluginAwareTrait;
use Cake\Core\BasePlugin;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Cipherguard\EmailDigest\Utility\Digest\DigestTemplateRegistry;
use Cipherguard\PasswordExpiry\Event\PasswordExpiryResourceMarkedAsExpiredEventListener;
use Cipherguard\PasswordExpiry\Notification\DigestTemplate\PasswordExpiryPasswordMarkedExpiredDigestTemplate;
use Cipherguard\PasswordExpiry\Notification\Email\PasswordExpiryRedactorPool;
use Cipherguard\PasswordExpiry\Notification\NotificationSettings\PasswordExpiryNotificationSettingsDefinition;
use Cipherguard\PasswordExpiry\Service\Resources\PasswordExpiryExpireResourcesService;
use Cipherguard\PasswordExpiry\Service\Resources\PasswordExpiryValidationService;
use Cipherguard\PasswordExpiry\Service\Settings\PasswordExpiryGetSettingsService;
use Cipherguard\PasswordExpiry\Service\Settings\PasswordExpiryGetSettingsServiceInterface;
use Cipherguard\PasswordExpiry\Service\Settings\PasswordExpirySetSettingsService;
use Cipherguard\PasswordExpiry\Service\Settings\PasswordExpirySetSettingsServiceInterface;

class PasswordExpiryPlugin extends BasePlugin
{
    use FeaturePluginAwareTrait;

    /**
     * @inheritDoc
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        parent::bootstrap($app);

        // Register email redactors and listen to user disabling/deleting
        $app->getEventManager()
            ->on(new PasswordExpiryResourceMarkedAsExpiredEventListener())
            ->on(new PasswordExpiryNotificationSettingsDefinition())
            ->on(new PasswordExpiryRedactorPool());

        DigestTemplateRegistry::getInstance()->addTemplate(
            new PasswordExpiryPasswordMarkedExpiredDigestTemplate(),
        );
    }

    /**
     * @inheritDoc
     */
    public function services(ContainerInterface $container): void
    {
        $container
            ->add(PasswordExpiryGetSettingsServiceInterface::class)
            ->setConcrete(PasswordExpiryGetSettingsService::class);
        $container
            ->add(PasswordExpirySetSettingsServiceInterface::class)
            ->setConcrete(PasswordExpirySetSettingsService::class);
        $container
            ->extend(PasswordExpiryValidationServiceInterface::class)
            ->setConcrete(PasswordExpiryValidationService::class)
            ->addArgument(PasswordExpiryGetSettingsServiceInterface::class);
        $container
            ->extend(ResourcesExpireResourcesServiceInterface::class)
            ->setConcrete(PasswordExpiryExpireResourcesService::class)
            ->addArgument(PasswordExpiryValidationServiceInterface::class);
    }
}
