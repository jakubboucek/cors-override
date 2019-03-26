<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
    use Nette\StaticClass;


    /**
     * @return Nette\Routing\Router
     * @throws Nette\InvalidArgumentException
     */
    public static function createRouter(): Nette\Routing\Router
    {
        $router = new RouteList;
        $router[] = new Route('/fetch[!.<format json|html>]', [
            'presenter' => 'Fetch',
            'action' => 'run',
            'format'=> 'json'
        ]);
        return $router;
    }
}
