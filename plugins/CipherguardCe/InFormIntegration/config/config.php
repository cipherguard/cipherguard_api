<?php

use Cake\Core\Configure;

return [
    'cipherguard' => [
        'plugins' => [
            'inFormIntegration' => [
                'enabled' => Configure::read(
                    'cipherguard.plugins.inFormIntegration.enabled',
                    env('CIPHERGUARD_PLUGINS_IN_FORM_INTEGRATION_ENABLED', true)
                ),
                'version' => '1.0.0',
                'settingsVisibility' => [
                    'whiteListPublic' => [
                        'enabled',
                    ],
                ],
            ],
        ],
    ],
];
