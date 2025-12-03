<?php

namespace Diffyne\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DiffyneInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'diffyne:install {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Install Diffyne package assets and configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Diffyne...');
        $this->newLine();

        $this->call('vendor:publish', [
            '--tag' => 'diffyne-config',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'diffyne-assets',
            '--force' => $this->option('force'),
        ]);

        $componentPath = app_path('Diffyne');
        if (! File::exists($componentPath)) {
            File::makeDirectory($componentPath, 0755, true);
            $this->info('✓ Created directory: app/Diffyne');
        }

        $viewPath = resource_path('views/diffyne');
        if (! File::exists($viewPath)) {
            File::makeDirectory($viewPath, 0755, true);
            $this->info('✓ Created directory: resources/views/diffyne');
        }

        if ($this->confirm('Would you like to create an example Counter component?', true)) {
            $this->call('make:diffyne', ['name' => 'Counter']);
            $this->createCounterExample();
        }

        $this->newLine();
        $this->info('🎉 Diffyne installed successfully!');
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Add <fg=cyan>@diffyneScripts</> to your layout before </body>');
        $this->line('  2. Create components with <fg=cyan>php artisan make:diffyne ComponentName</>');
        $this->line('  3. Use components in Blade with <fg=cyan>@diffyne(\'ComponentName\')</>');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Create an example Counter component implementation.
     */
    protected function createCounterExample(): void
    {
        $classPath = app_path('Diffyne/Counter.php');
        $viewPath = resource_path('views/diffyne/counter.blade.php');

        File::put($classPath, File::get(__DIR__.'/../../../stubs/counter.component.stub'));
        File::put($viewPath, File::get(__DIR__.'/../../../stubs/counter.view.stub'));

        $this->info('✓ Counter example component created');
    }
}
