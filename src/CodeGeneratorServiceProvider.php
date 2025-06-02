<?php

namespace Mruganshi\CodeGenerator;

use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mruganshi\CodeGenerator\Http\Livewire\Index;
use Mruganshi\CodeGenerator\Http\Livewire\Logs;
use Mruganshi\CodeGenerator\Http\Livewire\RestApi;

class CodeGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * Bind services, merge configuration, and register commands.
     */
    public function register(): void
    {
        // Merge package config with app config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/code_generator.php',
            'code_generator'
        );

        // Register package artisan commands
        $this->commands([
            \Mruganshi\CodeGenerator\Console\Commands\MakeModel::class,
            \Mruganshi\CodeGenerator\Console\Commands\MakeController::class,
            \Mruganshi\CodeGenerator\Console\Commands\MakeMigration::class,
            \Mruganshi\CodeGenerator\Console\Commands\MakePolicy::class,
            \Mruganshi\CodeGenerator\Console\Commands\MakeObserver::class,
            \Mruganshi\CodeGenerator\Console\Commands\MakeFactory::class,
            \Mruganshi\CodeGenerator\Console\Commands\MakeService::class,
            \Mruganshi\CodeGenerator\Console\Commands\MakeNotification::class,
            \Mruganshi\CodeGenerator\Console\Commands\MakeRequest::class,
            \Mruganshi\CodeGenerator\Console\Commands\MakeResource::class,
            \Mruganshi\CodeGenerator\Console\Commands\MakeResourceCollection::class,
            \Mruganshi\CodeGenerator\Console\Commands\ClearLogs::class,
        ]);
    }


    public function boot(): void
    {
        // Define middleware group for the code generator routes
        Route::middlewareGroup(
            'codeGeneratorMiddleware',
            config('code_generator.middleware', [])
        );

        // Publish views from package
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('code-generator'),
        ], 'code-generator-views');

        // Publish config file
        $this->publishes([
            __DIR__ . '/../config/code_generator.php' => config_path('code_generator.php'),
        ], 'config');

        // Publish migration files
        $this->publishes([
            __DIR__ . '/Migrations' => database_path('migrations'),
        ], 'codegenerator-migrations');

        // Publish stub files
        $this->publishes([
            __DIR__ . '/stubs' => database_path('stubs'),
        ], 'stubs');

        // Load routes from package
        $this->loadRoutesFrom(__DIR__ . "/../routes/web.php");

        // Load migrations from package
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        // Load views from package
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'code-generator');

        // Register Livewire components
        Livewire::component('code-generator::index', Index::class);
        Livewire::component('code-generator::rest-api', RestApi::class);
        Livewire::component('code-generator::logs', Logs::class);
    }
}
