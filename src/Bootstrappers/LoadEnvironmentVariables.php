<?php

namespace Nguyenthanhworkspace\LaravelMultienv\Bootstrappers;

use Dotenv\Dotenv;
use Illuminate\Support\Env;
use Symfony\Component\Console\Input\ArgvInput;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as LaravelLoadEnvironmentVariables;


class LoadEnvironmentVariables extends LaravelLoadEnvironmentVariables
{
    /**
     * Domain .env file name in configs.
     */
    private string $domainEnvFilename = '';

    /**
     * Array of cache environment variables by domain.
     *
     * @var array<string, string>
     *
     * @example
     *  array:3 [
     *      "APP_CONFIG_CACHE" => "config-domain-tld.php"
     *      "APP_ROUTES_CACHE" => "routes-v7-domain-tld.php"
     *      "APP_EVENTS_CACHE" => "events-domain-tld.php"
     *  ]
     */
    private array $domainCacheEnvs = [];

    /**
     * Array of .env settings from the config folder.
     *
     * @var array<mixed>
     */
    private array $configEnvs = [];

    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     *
     * @return void
     */
    public function bootstrap(Application $app): void
    {
        $this->setConfigEnvs($app);
        $this->handleTenancy($app);
        $this->putEnvCaches($app);

        parent::bootstrap($app);
    }

    /**
     * Create a Dotenv instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     *
     * @return \Dotenv\Dotenv
     */
    protected function createDotenv($app)
    {
        /** @var string */
        $envsFolder = ($this->configEnvs['folder'] ?? 'envs');

        /** @var string */
        $envsCustomPath = $app->environmentPath().'/'.$envsFolder;

        putenv("DOMAIN_ENV_FILENAME={$this->domainEnvFilename}");

        return Dotenv::create(
            Env::getRepository(),
            [$app->environmentPath(), $envsCustomPath],
            [$app->environmentFile(), $this->domainEnvFilename], // @phpstan-ignore-line
            false
        );
    }

    /**
     * Configure the $configEnvs variable with the data from the config `.envs`.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     *
     * @return void
     */
    private function setConfigEnvs(Application $app): void
    {
        if (is_file($configPath = $app->configPath('envs.php'))) {
            $this->configEnvs = require $configPath;
        }
    }

    /**
     * Handles key environment variables and custom key `env`.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     *
     * @return void
     */
    private function handleTenancy(Application $app): void
    {
        if (empty($domain = $this->getTenancy($app))) {
            return; // @codeCoverageIgnore
        }

        // By default the `.env.<domain>` file will take precedence over all other `.env` files!
        $this->domainEnvFilename = '.env.'.$domain;

        /** @var array<string, array<string, string>> */
        $domainsCachesEnv = $this->configEnvs['domains'] ?? [];

        /** @var array<string, string>|null */
        $domainCacheEnvs = $domainsCachesEnv[$domain] ?? null;

        // When there is no configuration in `config('envs.domains.<your-domain>')`,
        // nothing should be done!

        if (!empty($domainCacheEnvs)) {
            // Only keys ending with the suffix of `_CACHE`
            $this->domainCacheEnvs = array_filter(
                $domainCacheEnvs,
                fn(string $envName) => str_ends_with($envName, '_CACHE'),
                ARRAY_FILTER_USE_KEY
            );

            $this->domainEnvFilename = $domainCacheEnvs['env'] ?? $this->domainEnvFilename;
        }
    }

    /**
     * Retrieves the value for the domain/subdomain `.env` file name.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     *
     * @return string
     */
    private function getTenancy(Application $app): string
    {
        $tenancy = '';

        $input = new ArgvInput();

        $isRunningWithArtisanCall = boolval(getenv('RUNNING_IN_ARTISAN_CALL'));

        if (($app->runningInConsole() || $isRunningWithArtisanCall) && $input->hasParameterOption('--tenants')) {
            $tenancy = $input->getParameterOption('--tenants');
        } elseif ($app->has('request')) {
            $tenancy = request()->getHost();
            $parts = explode('.', $tenancy);

            // Get the subdomain
            $tenancy = $parts[0];
        }

        putenv('RUNNING_IN_ARTISAN_CALL');

        return strval($tenancy);
    }

    /**
     * Add domain variables prefixed in putenv.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     *
     * @return void
     */
    private function putEnvCaches(Application $app): void
    {
        putenv('APP_CONFIG_CACHE');
        putenv('APP_ROUTES_CACHE');
        putenv('APP_EVENTS_CACHE');

        if (empty($domainCacheEnvs = $this->domainCacheEnvs)) {
            return;
        }

        foreach ($domainCacheEnvs as $cacheEnvName => $cacheEnvValue) {
            /** @example /laravel-app/bootstrap/cache/config-domain-tld.php */
            $cachedFilename = $app->bootstrapPath('cache/'.$cacheEnvValue);

            putenv("$cacheEnvName=$cachedFilename");
        }
    }
}
