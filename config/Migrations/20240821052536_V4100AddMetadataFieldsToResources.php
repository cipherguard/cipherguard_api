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
// @codingStandardsIgnoreStart
use Cake\Core\Configure;
use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class V4100AddMetadataFieldsToResources extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $this
            ->table('resources')
            ->addColumn('metadata', 'text', [
                'default' => null,
                'limit' => MysqlAdapter::TEXT_MEDIUM,
                'null' => true,
                'after' => 'description',
            ])
            ->addColumn('metadata_key_id', 'uuid', [
                'default' => null,
                'null' => true,
                'after' => 'metadata',
                'encoding' => 'ascii',
                'collation' => 'ascii_general_ci',
            ])
            ->addColumn('metadata_key_type', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
                'after' => 'metadata_key_id',
            ])
            ->update();
    }
}
// @codingStandardsIgnoreEnd
