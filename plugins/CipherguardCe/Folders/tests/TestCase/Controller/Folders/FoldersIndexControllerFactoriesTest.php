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
 * @since         4.9.1
 */

namespace Cipherguard\Folders\Test\TestCase\Controller\Folders;

use Cipherguard\Folders\Test\Factory\FolderFactory;
use Cipherguard\Folders\Test\Lib\FoldersIntegrationTestCase;

/**
 * @uses \Cipherguard\Folders\Controller\Folders\FoldersIndexController
 */
class FoldersIndexControllerFactoriesTest extends FoldersIntegrationTestCase
{
    public function testFoldersIndexController_FilterBySearchSuccess()
    {
        $user = $this->logInAsUser();
        FolderFactory::make(['foo', 'FOO', 'bar'])->withPermissionsFor([$user])->persist();

        $this->getJson('/folders.json?filter[search]=O');
        $this->assertSuccess();
        $this->assertEquals(2, count($this->_responseJsonBody));
        $this->assertNotContains('bar', $this->_responseJsonBody);

        $this->getJson('/folders.json?filter[search]=o');
        $this->assertSuccess();
        $this->assertEquals(2, count($this->_responseJsonBody));
        $this->assertNotContains('bar', $this->_responseJsonBody);
    }
}
