<?php

use Illuminate\Support\Facades\Route;

// Define the route for the code generator
// The route path is configurable via the 'route_path' option in the code_generator config file
Route::get(
    config("code_generator.route_path"),
    function () {
        return view('code-generator::livewire.index');
    }
)->middleware("codeGeneratorMiddleware")->name('code-generator.index');

// Define the route for logs
Route::get(
    'codegenerator/logs',
    function () {
        return view('code-generator::livewire.index');
    }
)->middleware("codeGeneratorMiddleware")->name('code-generator.logs');
