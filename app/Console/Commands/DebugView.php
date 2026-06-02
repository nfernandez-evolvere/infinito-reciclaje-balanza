<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\View\Compilers\BladeCompiler;

class DebugView extends Command
{
    protected $signature = 'debug:view {path}';

    public function handle(BladeCompiler $compiler)
    {
        $path = base_path($this->argument('path'));
        $compiler->compile($path);
        $out = $compiler->getCompiledPath($path);
        $this->line(file_get_contents($out));
    }
}
