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

namespace Cipherguard\PasswordExpiry\Test\TestCase\Controller\Users;

use App\Test\Factory\ResourceFactory;
use App\Test\Factory\RoleFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use App\Test\Lib\Model\EmailQueueTrait;
use Cipherguard\Log\Test\Factory\SecretAccessFactory;
use Cipherguard\PasswordExpiry\PasswordExpiryPlugin;
use Cipherguard\PasswordExpiry\Test\Factory\PasswordExpirySettingFactory;

class PasswordExpiryUsersDeleteControllerTest extends AppIntegrationTestCase
{
    use EmailQueueTrait;

    public function setUp(): void
    {
        parent::setUp();
        RoleFactory::make()->guest()->persist();
        PasswordExpirySettingFactory::make()->persist();
        $this->enableFeaturePlugin(PasswordExpiryPlugin::class);
    }

    public function testPasswordExpiryUsersDeleteController_Success_Expire_Resources(): void
    {
        [$owner, $userToDelete] = UserFactory::make(2)->user()->persist();

        [$resourceSharedViewed, $resourceSharedNotViewed] = ResourceFactory::make(2)
            ->withPermissionsFor([$userToDelete, $owner])
            ->withSecretsFor([$userToDelete, $owner])
            ->persist();
        SecretAccessFactory::make()
            ->withUsers(UserFactory::make($userToDelete))
            ->withResources(ResourceFactory::make($resourceSharedViewed))
            ->persist();

        $this->logInAsAdmin();
        $this->deleteJson('/users/' . $userToDelete->id . '.json');
        $this->assertSuccess();

        $userDeleted = UserFactory::get($userToDelete->id);
        $this->assertTrue($userDeleted->isDeleted());
        $resourceSharedViewed = ResourceFactory::get($resourceSharedViewed->id);
        $resourceSharedNotViewed = ResourceFactory::get($resourceSharedNotViewed->id);
        $this->assertTrue($resourceSharedViewed->isExpired());
        $this->assertFalse($resourceSharedNotViewed->isExpired());
        // The owner should be notified about the expired resource
        $this->assertEmailQueueCount(1);
        $this->assertEmailInBatchContains(
            'Access for users to your shared passwords have been revoked.',
            $owner->username
        );
    }
}
