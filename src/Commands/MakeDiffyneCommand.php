<?php

namespace Diffyne\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeDiffyneCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:diffyne {name : The name of the component}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new Diffyne component';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $className = $this->getClassName($name);
        $namespace = config('diffyne.component_namespace', 'App\\Diffyne');
        $viewPath = config('diffyne.view_path', resource_path('views/diffyne'));

        // Create component class
        $classPath = $this->getClassPath($className, $namespace);
        
        if (File::exists($classPath)) {
            $this->error("Component [{$className}] already exists!");
            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($classPath));
        File::put($classPath, $this->getClassStub($className, $namespace));

        $this->info("Component class created: {$classPath}");

        // Create component view
        $viewName = $this->getViewName($name);
        $viewFilePath = "{$viewPath}/{$viewName}.blade.php";

        if (File::exists($viewFilePath)) {
            $this->warn("View already exists: {$viewFilePath}");
        } else {
            File::ensureDirectoryExists(dirname($viewFilePath));
            File::put($viewFilePath, $this->getViewStub($className));
            $this->info("Component view created: {$viewFilePath}");
        }

        $this->newLine();
        $this->line("Component <fg=green>{$className}</> created successfully!");
        $this->newLine();
        $this->line("Usage in Blade:");
        $this->line("  <fg=cyan>@diffyne('{$name}')</>");
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Get the class name from component name.
     */
    protected function getClassName(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
    }

    /**
     * Get the view name from component name.
     */
    protected function getViewName(string $name): string
    {
        return strtolower(str_replace(' ', '-', preg_replace('/(?<!^)[A-Z]/', '-$0', $name)));
    }

    /**
     * Get the class file path.
     */
    protected function getClassPath(string $className, string $namespace): string
    {
        $relativePath = str_replace('\\', '/', str_replace('App\\', '', $namespace));
        return app_path("{$relativePath}/{$className}.php");
    }

    /**
     * Get the class stub content.
     */
    protected function getClassStub(string $className, string $namespace): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Diffyne\Component;
use Illuminate\View\View;

class {$className} extends Component
{
    /**
     * Component public properties.
     */
    // public string \$message = 'Hello from Diffyne!';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        //
    }

    /**
     * Render the component.
     */
    public function render(): View|string
    {
        return view(\$this->view());
    }
}

PHP;
    }

    /**
     * Get the view stub content.
     */
    protected function getViewStub(string $className): string
    {
        return <<<HTML
<div>
    <h2>Welcome to {$className}</h2>
    <p>Edit this component in resources/views/diffyne</p>
    
    {{-- Example button with click action --}}
    {{-- <button diffyne:click="methodName">Click Me</button> --}}
    
    {{-- Example input with model binding --}}
    {{-- <input type="text" diffyne:model="propertyName"> --}}
</div>

HTML;
    }
}
