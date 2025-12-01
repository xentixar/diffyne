<?php

namespace Diffyne\Commands;

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
        if (!File::exists($componentPath)) {
            File::makeDirectory($componentPath, 0755, true);
            $this->info("✓ Created directory: app/Diffyne");
        }

        $viewPath = resource_path('views/diffyne');
        if (!File::exists($viewPath)) {
            File::makeDirectory($viewPath, 0755, true);
            $this->info("✓ Created directory: resources/views/diffyne");
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

        File::put($classPath, $this->getCounterClassContent());

        File::put($viewPath, $this->getCounterViewContent());

        $this->info('✓ Counter example component created');
    }

    /**
     * Get Counter class content.
     */
    protected function getCounterClassContent(): string
    {
        return <<<'PHP'
<?php

namespace App\Diffyne;

use Diffyne\Component;
use Illuminate\View\View;

class Counter extends Component
{
    /**
     * The counter value.
     */
    public int $count = 0;

    /**
     * Increment the counter.
     */
    public function increment(): void
    {
        $this->count++;
    }

    /**
     * Decrement the counter.
     */
    public function decrement(): void
    {
        $this->count--;
    }

    /**
     * Reset the counter.
     */
    public function reset(): void
    {
        $this->count = 0;
    }

    /**
     * Render the component.
     */
    public function render(): View|string
    {
        return view($this->view());
    }
}

PHP;
    }

    /**
     * Get Counter view content.
     */
    protected function getCounterViewContent(): string
    {
        return <<<'HTML'
<div class="p-6 max-w-sm mx-auto bg-white rounded-xl shadow-lg">
    <h2 class="text-2xl font-bold mb-4">Counter Example</h2>
    
    <div class="text-center mb-4">
        <div class="text-6xl font-bold text-blue-600">{{ $count }}</div>
    </div>
    
    <div class="flex gap-2">
        <button 
            diffyne:click="decrement"
            class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded"
            diffyne:loading.class="opacity-50"
        >
            -
        </button>
        
        <button 
            diffyne:click="reset"
            class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded"
        >
            Reset
        </button>
        
        <button 
            diffyne:click="increment"
            class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded"
            diffyne:loading.class="opacity-50"
        >
            +
        </button>
    </div>
</div>

HTML;
    }
}
