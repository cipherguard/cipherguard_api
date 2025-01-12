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
namespace Cipherguard\Metadata\Test\TestCase\Service\Healthcheck;

use App\Service\Healthcheck\HealthcheckServiceCollector;
use App\Test\Lib\AppTestCaseV5;
use App\Utility\OpenPGP\OpenPGPBackendFactory;
use Cipherguard\Metadata\Service\Healthcheck\ServerCanDecryptMetadataPrivateKeyHealthcheck;
use Cipherguard\Metadata\Test\Factory\MetadataKeyFactory;
use Cipherguard\Metadata\Test\Factory\MetadataPrivateKeyFactory;

/**
 * @covers \Cipherguard\Metadata\Service\Healthcheck\ServerCanDecryptMetadataPrivateKeyHealthcheck
 */
class ServerCanDecryptMetadataPrivateKeyHealthcheckTest extends AppTestCaseV5
{
    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        OpenPGPBackendFactory::reset();

        parent::tearDown();
    }

    public function testMetadataServerCanDecryptMetadataPrivateKeyHealthcheck_Success(): void
    {
        MetadataPrivateKeyFactory::make()->withMetadataKey()->withServerPrivateKey()->persist();

        $service = new ServerCanDecryptMetadataPrivateKeyHealthcheck();
        $service->check();

        $this->assertTrue($service->isPassed());
        $this->assertSame(HealthcheckServiceCollector::DOMAIN_METADATA, $service->domain());
        $this->assertSame(HealthcheckServiceCollector::DOMAIN_METADATA, $service->cliOption());
        $this->assertSame(HealthcheckServiceCollector::LEVEL_ERROR, $service->level());
        $this->assertSame('canDecryptMetadataPrivateKey', $service->getLegacyArrayKey());
    }

    public function testMetadataServerCanDecryptMetadataPrivateKeyHealthcheck_Error_UnableToDecrypt(): void
    {
        // Set a message that is not for server
        $msg = file_get_contents(FIXTURES . DS . 'OpenPGP' . DS . 'Messages' . DS . 'ada_for_betty_signed.msg');
        MetadataPrivateKeyFactory::make(['user_id' => null, 'data' => $msg])->withMetadataKey()->persist();

        $service = new ServerCanDecryptMetadataPrivateKeyHealthcheck();
        $service->check();

        $this->assertFalse($service->isPassed());
        $this->assertStringContainsString('Unable to decrypt the metadata private key data', $service->getFailureMessage());
    }

    public function testMetadataServerCanDecryptMetadataPrivateKeyHealthcheck_Error_NoPrivateKeys(): void
    {
        $service = new ServerCanDecryptMetadataPrivateKeyHealthcheck();
        $service->check();

        $this->assertFalse($service->isPassed());
        $this->assertStringContainsString('No server metadata private key found', $service->getFailureMessage());
    }

    public function testMetadataServerCanDecryptMetadataPrivateKeyHealthcheck_Error_RelatedMetadataKeyIsDeleted(): void
    {
        MetadataPrivateKeyFactory::make()
            ->with('MetadataKeys', MetadataKeyFactory::make()->deleted()->withServerKey()->withCreatorAndModifier())
            ->withServerPrivateKey()
            ->persist();

        $service = new ServerCanDecryptMetadataPrivateKeyHealthcheck();
        $service->check();

        $this->assertFalse($service->isPassed());
        $this->assertStringContainsString('No server metadata private key found', $service->getFailureMessage());
    }
}
