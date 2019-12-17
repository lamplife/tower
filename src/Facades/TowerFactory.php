<?php

declare(strict_types = 1);

/**
 * Author: 狂奔的螞蟻 <www.firstphp.com>
 * Date: 2019/12/17
 * Time: 1:26 PM
 */

namespace Firstphp\Tower\Facades;

use Firstphp\Tower\TowerClient;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class TowerFactory
{


    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __invoke(ContainerInterface $container)
    {
        $contents = $container->get(ConfigInterface::class);
        $config = $contents->get("tower");
        return $container->make(TowerClient::class, compact('config'));
    }

}