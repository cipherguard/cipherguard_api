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
namespace Cipherguard\Metadata\Service;

use Cake\Core\Configure;
use Cipherguard\Metadata\Model\Dto\MetadataResourceDto;

class MetadataResourcesRenderService
{
    /**
     * @param array $resource resource to render
     * @param bool $isV5 is resource with V5 metadata
     * @return array
     */
    public function renderResource(array $resource, bool $isV5): array
    {
        if ($isV5) {
            $fieldsToRemove = MetadataResourceDto::V4_META_PROPS;
        } else {
            $fieldsToRemove = MetadataResourceDto::V5_META_PROPS;
        }

        foreach ($fieldsToRemove as $fieldToRemove) {
            unset($resource[$fieldToRemove]);
        }

        return $resource;
    }

    /**
     * @param array $resources resources to render
     * @return array
     */
    public function renderResources(array $resources): array
    {
        $isV5Enabled = Configure::read('cipherguard.v5.enabled');
        foreach ($resources as $i => &$resource) {
            // For performance reason, the detection of a v5 resource is made on the
            // presence of metadata
            $isResourceV5 = !empty($resource[MetadataResourceDto::METADATA]);
            if ($isResourceV5 && !$isV5Enabled) {
                unset($resources[$i]);
            } else {
                $resource = $this->renderResource($resource, $isResourceV5);
            }
        }

        return $resources;
    }
}
