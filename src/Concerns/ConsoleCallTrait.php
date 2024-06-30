<?php

namespace Nguyenthanhworkspace\LaravelMultienv\Concerns;

trait ConsoleCallTrait
{
    /**
     * Run an Artisan console command by name.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @param  \Symfony\Component\Console\Output\OutputInterface|null  $outputBuffer
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        putenv('RUNNING_IN_ARTISAN_CALL=true');

        $removeTenantFromArgv = array_filter($_SERVER['argv'] ?? [], function ($arg) {
            return ! str_starts_with((string) $arg, '--tenants');
        });

        $_SERVER['argv'] = $removeTenantFromArgv;

        if (! empty($domain = $parameters['--tenants'] ?? [])) {
            $_SERVER['argv'][] = "--tenants={$domain}";
        }

        return parent::call($command, $parameters, $outputBuffer);
    }
}
