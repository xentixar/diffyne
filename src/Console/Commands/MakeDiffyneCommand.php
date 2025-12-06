<?php

namespace Diffyne\Console\Commands;

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
        if (! is_string($name)) {
            $this->error('Component name is required');

            return self::FAILURE;
        }

        $className = $this->getClassName($name);
        $baseNamespace = config('diffyne.component_namespace', 'App\\Diffyne');
        $namespace = $this->getFullNamespace($baseNamespace);
        $viewPath = config('diffyne.view_path', resource_path('views/diffyne'));

        // Create component class
        $classPath = $this->getClassPath($className, $baseNamespace);

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
        $this->line('Usage in Blade:');
        $this->line("  <fg=cyan>@diffyne('{$name}')</>");
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Get the class name from component name.
     */
    protected function getClassName(string $name): string
    {
        // Handle nested paths (e.g., "Forms/LoginForm" -> "LoginForm")
        $parts = explode('/', $name);
        $className = end($parts);

        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $className)));
    }

    /**
     * Get the view name from component name.
     */
    protected function getViewName(string $name): string
    {
        // Convert to kebab-case and preserve path separators
        $parts = explode('/', $name);
        $parts = array_map(function ($part) {
            return strtolower(str_replace(' ', '-', preg_replace('/(?<!^)[A-Z]/', '-$0', $part)));
        }, $parts);

        return implode('/', $parts);
    }

    /**
     * Get the class file path.
     */
    protected function getClassPath(string $className, string $namespace): string
    {
        $name = $this->argument('name');
        if (! is_string($name)) {
            $name = '';
        }
        $relativePath = str_replace('\\', '/', str_replace('App\\', '', $namespace));

        // Handle nested paths
        $parts = explode('/', $name);
        if (count($parts) > 1) {
            // Remove the class name from parts to get the subdirectory
            array_pop($parts);
            $subPath = implode('/', $parts);

            return app_path("{$relativePath}/{$subPath}/{$className}.php");
        }

        return app_path("{$relativePath}/{$className}.php");
    }

    /**
     * Get the full namespace including subdirectories.
     */
    protected function getFullNamespace(string $baseNamespace): string
    {
        $name = $this->argument('name');
        if (! is_string($name)) {
            return $baseNamespace;
        }
        $parts = explode('/', $name);

        if (count($parts) > 1) {
            // Remove the class name from parts
            array_pop($parts);
            $subNamespace = implode('\\', $parts);

            return $baseNamespace.'\\'.$subNamespace;
        }

        return $baseNamespace;
    }

    /**
     * Get the class stub content.
     */
    protected function getClassStub(string $className, string $namespace): string
    {
        $stub = File::get(__DIR__.'/../../../stubs/component.stub');

        return str_replace(
            ['DummyNamespace', 'DummyClass'],
            [$namespace, $className],
            $stub
        );
    }

    /**
     * Get the view stub content.
     */
    protected function getViewStub(string $className): string
    {
        $stub = File::get(__DIR__.'/../../../stubs/view.stub');

        return str_replace('DummyClass', $className, $stub);
    }
}
