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

namespace Cipherguard\Metadata\Test\TestCase\Controller;

use App\Service\OpenPGP\OpenPGPCommonUserOperationsTrait;
use App\Test\Factory\GpgkeyFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCaseV5;
use App\Utility\OpenPGP\OpenPGPBackendFactory;
use Cake\I18n\FrozenTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cipherguard\Metadata\Test\Factory\MetadataSessionKeyFactory;
use Cipherguard\Metadata\Test\Utility\GpgMetadataKeysTestTrait;

/**
 * @uses \Cipherguard\Metadata\Controller\MetadataSessionKeyDeleteController
 */
class MetadataSessionKeyUpdateControllerTest extends AppIntegrationTestCaseV5
{
    use LocatorAwareTrait;
    use GpgMetadataKeysTestTrait;
    use OpenPGPCommonUserOperationsTrait;

    public function testMetadataSessionKeyUpdateController_Success(): void
    {
        $key = GpgkeyFactory::make()->withAdaKey();
        $user = UserFactory::make()->with('Gpgkeys', $key)->active()->persist();
        $sessionKey = MetadataSessionKeyFactory::make()->withUser($user)->persist();
        $sessionKey = MetadataSessionKeyFactory::get($sessionKey->get('id')); // needed to get exact modified time
        $sessionKeyId = $sessionKey->get('id');
        $oldModified = new FrozenTime($sessionKey->get('modified'));
        $gpg = OpenPGPBackendFactory::get();
        $gpg = $this->setEncryptKeyWithUserKey($gpg, $user->get('gpgkey'));
        $msg = $gpg->encrypt(MetadataSessionKeyFactory::getCleartextDataJson());
        $data = [
            'modified' => $oldModified,
            'data' => $msg,
        ];

        $this->logInAs($user);
        $this->postJson("/metadata/session-keys/{$sessionKeyId}.json", $data);
        $this->assertSuccess();
    }

    public function testMetadataSessionKeyUpdateController_Error_EmptyData(): void
    {
        $key = GpgkeyFactory::make()->withAdaKey();
        $user = UserFactory::make()->with('Gpgkeys', $key)->active()->persist();
        $sessionKey = MetadataSessionKeyFactory::make()->withUser($user)->persist();
        $sessionKey = MetadataSessionKeyFactory::get($sessionKey->get('id')); // needed to get exact modified time
        $sessionKeyId = $sessionKey->get('id');
        $data = [];

        $this->logInAs($user);
        $this->postJson("/metadata/session-keys/{$sessionKeyId}.json", $data);
        $this->assertError(400);
    }

    public function testMetadataSessionKeyUpdateController_Error_AuthenticationRequired(): void
    {
        $makiSessionKey = MetadataSessionKeyFactory::make()->withMakiSessionKey()->persist();
        $sessionKeyId = $makiSessionKey->get('id');
        $this->postJson("/metadata/session-keys/{$sessionKeyId}.json", []);
        $this->assertAuthenticationError();
    }

    public function testMetadataSessionKeyUpdateController_Error_NotJson(): void
    {
        $makiSessionKey = MetadataSessionKeyFactory::make()->withMakiSessionKey()->persist();
        $this->logInAs($makiSessionKey->get('user'));
        $sessionKeyId = $makiSessionKey->get('id');
        $this->post("/metadata/session-keys/{$sessionKeyId}", []);
        $this->assertResponseCode(404);
    }
}
