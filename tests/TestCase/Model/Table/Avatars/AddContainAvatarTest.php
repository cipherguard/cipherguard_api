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
 * @since         3.4.0
 */

namespace App\Test\TestCase\Model\Table\Avatars;

use App\Model\Table\AvatarsTable;
use App\Test\Factory\UserFactory;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

/**
 * AddContainAvatarTest Class
 */
class AddContainAvatarTest extends TestCase
{
    use LocatorAwareTrait;
    use TruncateDirtyTables;

    private ?Table $Users = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->Users = $this->fetchTable('Users');
        $this->loadRoutes();
    }

    public function enableHydration(): array
    {
        return [[true], [false]];
    }

    /**
     * @dataProvider enableHydration
     */
    public function testAvatarsTableAddContainAvatar_Should_Not_Retrieve_Avatar_Data(bool $isHydrationEnabled)
    {
        $user = UserFactory::make()->withAvatar()->user()->persist();

        /** @var \App\Model\Entity\User $retrievedUser */
        $retrievedUser = $this->Users->find()
            ->where(['Users.id' => $user->id])
            ->contain([
                'Profiles' => AvatarsTable::addContainAvatar(),
            ])
            ->enableHydration($isHydrationEnabled)
            ->firstOrFail();

        $this->assertSame($user->profile->avatar->id, $retrievedUser['profile']['avatar']['id']);
        $this->assertNotNull($user->profile->avatar->data);
        $this->assertNull($retrievedUser['profile']['avatar']['data'] ?? null);
    }

    /**
     * @dataProvider enableHydration
     */
    public function testAvatarsTableAddContainAvatar_On_Empty_Avatar(bool $isHydrationEnabled)
    {
        $user = UserFactory::make()->user()->persist();

        /** @var \App\Model\Entity\User $retrievedUser */
        $retrievedUser = $this->Users->find()
            ->where(['Users.id' => $user->id])
            ->contain([
                'Profiles' => AvatarsTable::addContainAvatar(),
            ])
            ->enableHydration($isHydrationEnabled)
            ->firstOrFail();

        $this->assertNull($user->profile->avatar->id ?? null);
        $this->assertNull($user->profile->avatar->data ?? null);
        $this->assertNull($retrievedUser['profile']['avatar']['id'] ?? null);
        $this->assertNull($retrievedUser['profile']['avatar']['data'] ?? null);
        if ($isHydrationEnabled) {
            $this->assertIsObject($retrievedUser['profile']['avatar']['url']);
        } else {
            $this->assertIsArray($retrievedUser['profile']['avatar']['url']);
        }
    }
}
