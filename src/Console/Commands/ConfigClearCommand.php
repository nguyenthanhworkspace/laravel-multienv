<?php

namespace Nguyenthanhworkspace\LaravelMultienv\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Nguyenthanhworkspace\LaravelMultienv\Console\Concerns\CommonOptions;
use Illuminate\Foundation\Console\ConfigClearCommand as LaravelConfigClearCommand;

#[AsCommand(name: 'config:clear')]
class ConfigClearCommand extends LaravelConfigClearCommand
{
    use CommonOptions;
}
