<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SARL (https://www.cipherguard.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SARL (https://www.cipherguard.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.com Cipherguard(tm)
 * @since         4.9.0
 */

namespace Cipherguard\Log\Test\TestCase\Controller\Resources;

use App\Test\Factory\ResourceFactory;
use Cipherguard\Log\Test\Factory\SecretAccessFactory;
use Cipherguard\Log\Test\Lib\LogIntegrationTestCase;

class ResourcesViewControllerLogTest extends LogIntegrationTestCase
{
    public function testResourcesViewController_Secret_Access()
    {
        $user = $this->logInAsUser();
        /** @var \App\Model\Entity\Resource $resource */
        $resource = ResourceFactory::make()
            ->withPermissionsFor([$user])
            ->withSecretsFor([$user])
            ->persist();
        $secret = $resource->secrets[0];
        $this->getJson("/resources/$resource->id.json?contain[secret]=1");
        $this->assertSuccess();
        $secretAccess = SecretAccessFactory::firstOrFail([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'secret_id' => $secret->id,
        ]);
        $this->assertNotNull($secretAccess);
    }
}
