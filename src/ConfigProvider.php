<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Firstphp\Tower;

use Firstphp\Tower\Facades\TowerFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                TowerInterface::class => TowerFactory::class
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for firstphp-tower',
                    'source' => __DIR__ . '/publish/tower.php',
                    'destination' => BASE_PATH . '/config/autoload/tower.php',
                ],
            ],
        ];
    }
}
