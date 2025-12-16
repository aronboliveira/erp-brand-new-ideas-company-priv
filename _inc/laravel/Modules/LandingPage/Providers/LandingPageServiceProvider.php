<?php

namespace Modules\LandingPage\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Modules\LandingPage\Providers\RouteServiceProvider;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

class LandingPageServiceProvider extends ServiceProvider
{
    protected string $moduleName     = 'LandingPage';
    protected string $moduleNameLower = 'landingpage';
    protected ConsoleOutput $output;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->output = new ConsoleOutput();
    }

    public function boot(): void
    {
        $tag = __METHOD__;
        Log::debug("{$tag} start");
        $this->output->writeln("{$tag} start");
        try {
            $this->registerTranslations();
            $this->registerConfig();
            $this->registerViews();
            $migrationsPath = module_path($this->moduleName, 'Database/Migrations');
            if (!is_dir($migrationsPath) || empty(glob("$migrationsPath/*.php"))) {
                Log::warning("{$this->moduleName} migrations missing at {$migrationsPath}");
                $this->output->writeln("Warning: migrations directory empty or missing at {$migrationsPath}");
            }
            $this->output->writeln("<question>Loading migrations from {$migrationsPath}...</question>");
            $this->loadMigrationsFrom($migrationsPath);
            $this->output->writeln("<question>Loading routes from " . module_path($this->moduleName, 'Routes/web.php') . "...</question>");
            $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/web.php'));
            Log::debug("{$tag} completed");
            $this->output->writeln("{$tag} completed");
        } catch (Throwable $e) {
            Log::critical("{$tag} failed", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            $this->output->writeln("{$tag} failed: {$e->getMessage()}");
            throw $e;
        }
    }

    public function register(): void
    {
        $tag = __METHOD__;
        Log::debug("{$tag} start");
        $this->output->writeln("{$tag} start");
        try {
            $this->app->register(RouteServiceProvider::class);
            Log::debug("{$tag} completed");
            $this->output->writeln("{$tag} completed");
        } catch (Throwable $e) {
            Log::critical("{$tag} failed", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            $this->output->writeln("{$tag} failed: {$e->getMessage()}");
            throw $e;
        }
    }

    public function provides(): array
    {
        $tag = __METHOD__;
        Log::debug("{$tag} start");
        $this->output->writeln("{$tag} start");
        try {
            $services = [];
            Log::debug("{$tag} completed");
            $this->output->writeln("{$tag} completed");
            return $services;
        } catch (Throwable $e) {
            Log::critical("{$tag} failed", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            $this->output->writeln("{$tag} failed: {$e->getMessage()}");
            throw $e;
        }
    }

    protected function registerConfig(): void
    {
        $tag = __METHOD__;
        Log::debug("{$tag} start");
        $this->output->writeln("{$tag} start");
        try {
            $configPath = module_path($this->moduleName, 'Config/config.php');
            if (!file_exists($configPath)) {
                Log::warning("Config missing for {$this->moduleName} at {$configPath}");
                $this->output->writeln("Warning: config file missing at {$configPath}");
            }
            $this->publishes([
                $configPath => config_path("{$this->moduleNameLower}.php")
            ], 'config');
            $this->mergeConfigFrom($configPath, $this->moduleNameLower);
            Log::debug("{$tag} completed");
            $this->output->writeln("{$tag} completed");
        } catch (Throwable $e) {
            Log::critical("{$tag} failed", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            $this->output->writeln("{$tag} failed: {$e->getMessage()}");
            throw $e;
        }
    }

    protected function registerViews(): void
    {
        $tag = __METHOD__;
        Log::debug("{$tag} start");
        $this->output->writeln("{$tag} start");

        try {
            $viewPath  = resource_path("views/modules/{$this->moduleNameLower}");
            $sourcePath = module_path($this->moduleName, 'Resources/views');
            if (!is_dir($sourcePath) || count(scandir($sourcePath)) <= 2) {
                Log::warning("Views missing for {$this->moduleName} at {$sourcePath}");
                $this->output->writeln("Warning: no view files in {$sourcePath}");
            }
            $this->publishes([
                $sourcePath => $viewPath
            ], ['views', "{$this->moduleNameLower}-module-views"]);
            $paths = array_merge($this->getViewPaths(), [$sourcePath]);
            if (empty($paths)) {
                Log::warning("No view paths for {$this->moduleName}");
                $this->output->writeln("Warning: no view.paths configured for module");
            }
            $this->loadViewsFrom($paths, $this->moduleNameLower);
            Log::debug("{$tag} completed");
            $this->output->writeln("{$tag} completed");
        } catch (Throwable $e) {
            Log::critical("{$tag} failed", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            $this->output->writeln("{$tag} failed: {$e->getMessage()}");
            throw $e;
        }
    }

    protected function registerTranslations(): void
    {
        $tag = __METHOD__;
        Log::debug("{$tag} start");
        $this->output->writeln("{$tag} start");
        try {
            $langPath   = resource_path("lang/modules/{$this->moduleNameLower}");
            $fallback   = module_path($this->moduleName, 'Resources/lang');
            $stdFallback = resource_path('lang');
            if (is_dir($langPath) && count(scandir($langPath)) > 2) {
                $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
                $this->loadJsonTranslationsFrom($langPath);
            } elseif (is_dir($fallback) && count(scandir($fallback)) > 2) {
                $this->loadTranslationsFrom($fallback, $this->moduleNameLower);
                $this->loadJsonTranslationsFrom($fallback);
            } elseif (is_dir($stdFallback) && count(scandir($stdFallback)) > 2) {
                $this->loadJsonTranslationsFrom($stdFallback);
                $this->loadTranslationsFrom($stdFallback, $this->moduleNameLower);
            } else {
                Log::warning("Translations missing for {$this->moduleName} in any of: {$langPath}, {$fallback}, {$stdFallback}");
                $this->output->writeln("Warning: no translations found in configured paths");
            }
            Log::debug("{$tag} completed");
            $this->output->writeln("{$tag} completed");
        } catch (Throwable $e) {
            Log::critical("{$tag} failed", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            $this->output->writeln("{$tag} failed: {$e->getMessage()}");
            throw $e;
        }
    }

    private function getViewPaths(): array
    {
        $tag = __METHOD__;
        Log::debug("{$tag} start");
        $this->output->writeln("{$tag} start");
        try {
            $paths     = [];
            $configured = (array) config('view.paths', []);
            if (empty($configured)) {
                Log::warning('No view.paths configured');
                $this->output->writeln("Warning: no view.paths in config");
            }
            foreach ($configured as $path) {
                $target = "{$path}/modules/{$this->moduleNameLower}";
                if (is_dir($target))
                    $paths[] = $target;
            }
            Log::debug("{$tag} completed");
            $this->output->writeln("{$tag} completed");
            return $paths;
        } catch (Throwable $e) {
            Log::critical("{$tag} failed", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            $this->output->writeln("{$tag} failed: {$e->getMessage()}");
            return [];
        }
    }
}
