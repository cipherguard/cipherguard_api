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
 * @since         4.1.0
 */

namespace Cipherguard\Rbacs\Test\TestCase\Controller\Rbacs;

use App\Test\Factory\RoleFactory;
use Cipherguard\Rbacs\Service\Rbacs\RbacsInsertDefaultsService;
use Cipherguard\Rbacs\Service\UiActions\UiActionsInsertDefaultsService;
use Cipherguard\Rbacs\Test\Lib\RbacsIntegrationTestCase;

/**
 * Cipherguard\Rbacs\Controller\Rbacs\RbacsViewController Test Case
 *
 * @uses \Cipherguard\Rbacs\Controller\Rbacs\RbacsViewController
 */
class RbacsViewControllerTest extends RbacsIntegrationTestCase
{
    /**
     * Assert there is no RBACs for guests, everything is denied
     */
    public function testRbacsViewController_Success_AsGuest(): void
    {
        RoleFactory::make()->guest()->persist();
        $this->getJson('/rbacs/me.json');
        $this->assertSuccess();
        $this->assertEquals(0, count($this->_responseJsonBody));
    }

    /**
     * Assert there is No RBACs for admin, everything is allowed
     */
    public function testRbacsViewController_Success_AsAdmin(): void
    {
        $this->logInAsAdmin();
        $this->getJson('/rbacs/me.json');
        $this->assertSuccess();
        $this->assertEquals(0, count($this->_responseJsonBody));
    }

    /**
     * Assert there is no RBACs for users by default
     */
    public function testRbacsViewController_Success_AsUser_Default(): void
    {
        $this->logInAsUser();
        $this->getJson('/rbacs/me.json');
        $this->assertSuccess();
        $this->assertEquals(0, count($this->_responseJsonBody));
    }

    /**
     * Assert there is some RBACs for users when set
     */
    public function testRbacsViewController_Success_AsUser_Updated(): void
    {
        RoleFactory::make()->user()->persist();
        RoleFactory::make()->admin()->persist();
        (new UiActionsInsertDefaultsService())->insertDefaultsIfNotExist();
        (new RbacsInsertDefaultsService())->allowAllUiActionsForUsers();
        $this->logInAsUser();
        $this->getJson('/rbacs/me.json');
        $this->assertSuccess();
        $this->assertTrue(count($this->_responseJsonBody) > 1);
    }

    /**
     * Check that calling url without JSON extension throws a 404
     */
    public function testRbacsViewController_Error_NotJson(): void
    {
        $this->logInAsUser();
        $this->get('/rbacs/me');
        $this->assertResponseCode(404);
    }
}
