<?php

namespace Nguyenthanhworkspace\LaravelMultienv\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Nguyenthanhworkspace\LaravelMultienv\Console\Concerns\CommonOptions;
use Illuminate\Foundation\Console\RouteClearCommand as LaravelRouteClearCommand;

#[AsCommand(name: 'route:clear')]
class RouteClearCommand extends LaravelRouteClearCommand
{
    use CommonOptions;
}
