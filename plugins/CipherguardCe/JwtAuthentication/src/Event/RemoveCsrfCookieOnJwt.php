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
 * @since         3.3.0
 */
namespace Cipherguard\JwtAuthentication\Event;

use App\Middleware\ContainerAwareMiddlewareTrait;
use App\Middleware\CsrfProtectionMiddleware;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Cake\Http\Cookie\Cookie;
use Cipherguard\JwtAuthentication\Service\Middleware\JwtRequestDetectionService;

class RemoveCsrfCookieOnJwt implements EventListenerInterface
{
    use ContainerAwareMiddlewareTrait;

    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Controller.initialize' => 'removeCsrfCookieOnJwt',
        ];
    }

    /**
     * When a user logs in, set the session ID as the access token generated.
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function removeCsrfCookieOnJwt(EventInterface $event): void
    {
        /** @var \Cake\Controller\Controller $controller */
        $controller = $event->getSubject();
        $response = $controller->getResponse();
        $request = $controller->getRequest();
        $isCsrfRequired = Configure::read(CsrfProtectionMiddleware::CIPHERGUARD_SECURITY_CSRF_PROTECTION_ACTIVE_CONFIG);

        $service = new JwtRequestDetectionService($request);
        if ($service->useJwtAuthentication() && $isCsrfRequired !== true) {
            $controller->setResponse(
                $response->withExpiredCookie(new Cookie('csrfToken'))
            );
        }
    }
}
