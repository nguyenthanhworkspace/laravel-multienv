<?php

namespace Nguyenthanhworkspace\LaravelMultienv\Console\Commands;

use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\RouteCollectionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Nguyenthanhworkspace\LaravelMultienv\Console\Concerns\CommonOptions;
use Illuminate\Foundation\Console\RouteCacheCommand as FoundationRouteCacheCommand;

#[AsCommand(name: 'route:cache')]
class RouteCacheCommand extends FoundationRouteCacheCommand
{
    use CommonOptions;

    /**
     * Boot a fresh copy of the application and get the routes.
     *
     * @return \Illuminate\Routing\RouteCollection
     */
    protected function getFreshApplicationRoutes()
    {
        /** @var string */
        $tenants = strval($this->option('tenants'));

        if (empty($tenants)) {
            return parent::getFreshApplicationRoutes();
        }

        $routes = $this->newAppRoutes();

        /** @var \Illuminate\Routing\RouteCollection */
        $routesTenant = $this->laravel->build(RouteCollection::class);

        /** @var \Illuminate\Routing\Route $route */
        foreach ($routes as $route) {
            if (str_contains($route->getDomain() ?? '', strtolower($tenants))) {
                $routesTenant->add($route);
            }
        }

        if (count($routesTenant) > 0) {
            $routes = $routesTenant;

            app('router')->setRoutes($routes);
        }

        return $routes;
    }

    /**
     * Creating a new route object to store only those filtered by the tenants.
     *
     * @return \Illuminate\Routing\RouteCollection
     */
    private function newAppRoutes(): RouteCollection
    {
        /** @var \Illuminate\Contracts\Foundation\Application */
        $newApp = $this->getFreshApplication();

        /** @var \Illuminate\Routing\Router */
        $router = $newApp->make('router');

        /** @var \Illuminate\Routing\RouteCollection */
        $routes = $router->getRoutes();

        $routes = tap($routes, function (RouteCollectionInterface $routes): void {
            $routes->refreshNameLookups();
            $routes->refreshActionLookups();
        });

        return $routes;
    }
}
