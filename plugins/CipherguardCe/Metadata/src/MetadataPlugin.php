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
namespace Cipherguard\Metadata;

use App\Service\Healthcheck\HealthcheckServiceCollector;
use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Cake\Event\EventManager;
use Cipherguard\Metadata\Command\GenerateDummyMetadataKeyCommand;
use Cipherguard\Metadata\Command\InsertDummyDataCommand;
use Cipherguard\Metadata\Command\MigrateAllItemsCommand;
use Cipherguard\Metadata\Command\MigrateFoldersCommand;
use Cipherguard\Metadata\Command\MigrateResourcesCommand;
use Cipherguard\Metadata\Command\UpdateMetadataTypesSettingsCommand;
use Cipherguard\Metadata\Event\MetadataFolderUpdateListener;
use Cipherguard\Metadata\Event\MetadataResourceIndexListener;
use Cipherguard\Metadata\Event\MetadataResourceUpdateListener;
use Cipherguard\Metadata\Event\MetadataUserDeleteSuccessListener;
use Cipherguard\Metadata\Event\SetupCompleteListener;
use Cipherguard\Metadata\Notification\Email\Redactor\MetadataEmailRedactorPool;
use Cipherguard\Metadata\Service\Healthcheck\ServerCanDecryptMetadataPrivateKeyHealthcheck;
use Cipherguard\Metadata\Service\Migration\MigrateAllV4FoldersToV5Service;
use Cipherguard\Metadata\Service\Migration\MigrateAllV4ResourcesToV5Service;
use Cipherguard\Metadata\Service\Migration\MigrateAllV4ToV5ServiceCollector;

class MetadataPlugin extends BasePlugin
{
    /**
     * @inheritDoc
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        parent::bootstrap($app);
        $this->attachListeners(EventManager::instance());
        // Add migrator services
        MigrateAllV4ToV5ServiceCollector::add([
            MigrateAllV4ResourcesToV5Service::class,
            MigrateAllV4FoldersToV5Service::class,
        ]);
    }

    /**
     * Attach the Locale related event listeners.
     *
     * @param \Cake\Event\EventManager $eventManager EventManager
     * @return void
     */
    public function attachListeners(EventManager $eventManager): void
    {
        $eventManager
            ->on(new MetadataEmailRedactorPool())
            ->on(new SetupCompleteListener())
            ->on(new MetadataUserDeleteSuccessListener())
            ->on(new MetadataResourceIndexListener())
            ->on(new MetadataResourceUpdateListener())
            ->on(new MetadataFolderUpdateListener());
    }

    /**
     * @inheritDoc
     */
    public function services(ContainerInterface $container): void
    {
        $container->add(ServerCanDecryptMetadataPrivateKeyHealthcheck::class);

        $container
            ->extend(HealthcheckServiceCollector::class)
            ->addMethodCall('addService', [ServerCanDecryptMetadataPrivateKeyHealthcheck::class]);
    }

    /**
     * @inheritDoc
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        // Alias commands
        $commands->add('cipherguard metadata generate_dummy_metadata_key', GenerateDummyMetadataKeyCommand::class);
        $commands->add('cipherguard metadata insert_dummy_data', InsertDummyDataCommand::class);
        $commands->add('cipherguard metadata update_metadata_types_settings', UpdateMetadataTypesSettingsCommand::class);
        // Migration commands
        $commands->add('cipherguard metadata migrate_resources', MigrateResourcesCommand::class);
        $commands->add('cipherguard metadata migrate_folders', MigrateFoldersCommand::class);
        $commands->add('cipherguard metadata migrate_all_items', MigrateAllItemsCommand::class);

        return $commands;
    }
}
