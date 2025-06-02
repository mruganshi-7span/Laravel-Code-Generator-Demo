<?php

use Sevenspan\CodeGenerator\Http\Middleware\AuthorizeCodeGenerator;

return [
    /*
    |--------------------------------------------------------------------------
    | Route Path
    |--------------------------------------------------------------------------
    |
    | Defines the URI prefix for accessing code generator.
    | Example: If set to 'code-generator', routes will be accessible at
    | yourdomain.com/code-generator/...
    |
    */
    "route_path" => "code-generator",

    /*
    |--------------------------------------------------------------------------
    | Paths for Generated Files
    |--------------------------------------------------------------------------
    |
    | These paths specify where generated files will be saved within the `app` directory,
    | and they also determine the corresponding namespaces for those files.
    | For example, if model_path is set to 'Models', generated models will be placed in
    | app/Models and will have the namespace App\Models.
    |
    */

    "model_path" => "Models",
    "migration_path" => "Migrations",
    "factory_path" => "Factories",
    "notification_path" => "Notifications",
    "observer_path" => "Observers",
    "policy_path" => "Policies",
    "service_path" => "Services",
    "controller_path" => "Http\Controllers",
    'admin_controller_path' => 'Http\Controllers\Admin',
    "request_path" => "Http\Requests",
    "resource_path" => "Http\Resources",
    "trait_path" => "Traits",

    /*
    |--------------------------------------------------------------------------
    | Require Authentication in Production
    |--------------------------------------------------------------------------
    |
    | Set to true if you want to restrict access to the code generator
    | in production using authentication middleware.
    | This is recommended for security reasons in production environments.
    |
    */

    "require_auth_in_production" => false,

    /*
    |--------------------------------------------------------------------------
    | Middleware for Code Generator Routes
    |--------------------------------------------------------------------------
    |
    | Define the middleware that will be applied to all code generator routes.
    | You can add more middleware or remove existing ones as per your app's
    | requirements.
    |
    */

    "middleware" => [
        "web",
        AuthorizeCodeGenerator::class, // Custom middleware to authorize generator access
    ],

    'class_namespace' => 'Sevenspan\\CodeGenerator',

    /*
    |--------------------------------------------------------------------------
    |  Delete logs older than configured days
    |--------------------------------------------------------------------------
    */

    'log_retention_days' => env('CODE_GENERATOR_LOG_RETENTION_DAYS',2),
];
