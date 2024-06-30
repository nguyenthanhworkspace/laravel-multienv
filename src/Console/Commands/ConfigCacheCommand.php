<?php

namespace Nguyenthanhworkspace\LaravelMultienv\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Nguyenthanhworkspace\LaravelMultienv\Console\Concerns\CommonOptions;
use Illuminate\Foundation\Console\ConfigCacheCommand as LaravelConfigCacheCommand;

#[AsCommand(name: 'config:cache')]
class ConfigCacheCommand extends LaravelConfigCacheCommand
{
    use CommonOptions;

    /**
     * Call another console command without output.
     *
     * @param \Symfony\Component\Console\Command\Command|string $command
     * @param array<mixed> $arguments
     *
     * @return int
     */
    public function callSilent($command, array $arguments = [])
    {
        $arguments = array_merge($arguments, ['--tenants' => $this->option('tenants')]);

        return parent::call($command, $arguments);
    }
}
