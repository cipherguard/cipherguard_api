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
namespace App\Test\Lib;

use Cake\Core\Configure;
use Cipherguard\Metadata\MetadataPlugin;
use Cipherguard\Metadata\Service\MetadataKeysSettingsGetService;
use Cipherguard\Metadata\Service\MetadataTypesSettingsGetService;

abstract class AppTestCaseV5 extends AppTestCase
{
    /**
     * @var bool $isV5Enabled
     */
    protected $isV5Enabled;

    /**
     * Setup.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->isV5Enabled = Configure::read('cipherguard.v5.enabled');
        if (!$this->isV5Enabled) {
            Configure::write('cipherguard.v5.enabled', true);
        }
        $this->enableFeaturePlugin(MetadataPlugin::class);
    }

    /**
     * Tear down
     */
    public function tearDown(): void
    {
        if (!$this->isV5Enabled) {
            Configure::write('cipherguard.v5.enabled', false);
        }
        MetadataTypesSettingsGetService::clear();
        MetadataKeysSettingsGetService::clear();
        parent::tearDown();
    }
}
