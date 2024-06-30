<?php

namespace Nguyenthanhworkspace\LaravelMultienv\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use Nguyenthanhworkspace\LaravelMultienv\Console\Commands\OptimizeCommand;
use Nguyenthanhworkspace\LaravelMultienv\Console\Commands\RouteCacheCommand;
use Nguyenthanhworkspace\LaravelMultienv\Console\Commands\RouteClearCommand;
use Nguyenthanhworkspace\LaravelMultienv\Console\Commands\ConfigCacheCommand;
use Nguyenthanhworkspace\LaravelMultienv\Console\Commands\ConfigClearCommand;
use Nguyenthanhworkspace\LaravelMultienv\Console\Commands\OptimizeClearCommand;

class ArtisanServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * The commands to be registered.
     *
     * @var array<string, string>
     */
    protected array $commands = [
        'ConfigCache' => ConfigCacheCommand::class,
        'ConfigClear' => ConfigClearCommand::class,
        'Optimize' => OptimizeCommand::class,
        'OptimizeClear' => OptimizeClearCommand::class,
        'RouteCache' => RouteCacheCommand::class,
        'RouteClear' => RouteClearCommand::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerCommands($this->commands);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides(): array
    {
        return array_values($this->commands);
    }

    /**
     * Register the given commands.
     *
     * @param array<string, string> $commands
     *
     * @return void
     */
    protected function registerCommands(array $commands): void
    {
        foreach (array_keys($commands) as $commandName) {
            $method = "register{$commandName}Command";

            if (method_exists($this, $method)) {
                $this->{$method}();

                continue;
            }

            $this->app->singleton($commandName);
        }

        $commands = array_values($commands);

        $this->commands($commands);
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerConfigCacheCommand(): void
    {
        $this->app->singleton(ConfigCacheCommand::class, function ($app) {
            return new ConfigCacheCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerConfigClearCommand(): void
    {
        $this->app->singleton(ConfigClearCommand::class, function ($app) {
            return new ConfigClearCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerRouteCacheCommand(): void
    {
        $this->app->singleton(RouteCacheCommand::class, function ($app) {
            return new RouteCacheCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerRouteClearCommand(): void
    {
        $this->app->singleton(RouteClearCommand::class, function ($app) {
            return new RouteClearCommand($app['files']);
        });
    }
}
