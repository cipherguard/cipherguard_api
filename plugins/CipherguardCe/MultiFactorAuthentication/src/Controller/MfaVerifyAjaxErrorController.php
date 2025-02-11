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
 * @since         2.4.0
 */
namespace Cipherguard\MultiFactorAuthentication\Controller;

use Cake\Event\EventInterface;

class MfaVerifyAjaxErrorController extends MfaController
{
    /**
     * @inheritDoc
     */
    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);
        $this->_invalidateMfaCookie();
    }

    /**
     * @throw ForbiddenException
     * @return void
     */
    public function get()
    {
        $providers = $this->mfaSettings->getProvidersVerifyUrls();
        // Use AppController:error instead of exception to avoid logging the error
        $this->error(__('MFA authentication is required.'), [
            'mfa_providers' => array_keys($providers),
            /** @deprecated on v4 */
            'providers' => $providers,
        ], 403);
    }
}
