<?php

namespace Sevenspan\CodeGenerator\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Sevenspan\CodeGenerator\Traits\FileManager;
use Sevenspan\CodeGenerator\Enums\CodeGeneratorFileType;

class MakeController extends Command
{
    use FileManager;

    private const INDENT = '    ';

    protected $signature = 'codegenerator:controller {model : The name of the model to associate with the controller} 
                                                     {--methods= : Comma-separated list of methods to include in the controller}  
                                                     {--service : Include a service file for the controller} 
                                                     {--resource : Include resource files for the controller} 
                                                     {--request : Include request files for the controller}
                                                     {--overwrite : is overwriting this file is selected}
                                                     {--adminCrud : is adminCRUD added}';

    protected $description = 'Generate a custom controller with optional methods and service injection';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }


    public function handle(): void
    {
        $modelName = $this->argument('model');
        $isAdminCrudIncluded = (bool) $this->option('adminCrud');

        // generate the normal controller
        $this->generateController($modelName, isAdminCrudIncluded: false);

        // if admin crud is selected, generate the admin controller
        if ($isAdminCrudIncluded) {
            $this->generateController($modelName, isAdminCrudIncluded: true);
        }
    }

    protected function generateController(string $modelName, bool $isAdminCrudIncluded = false): void
    {
        $controllerClassName = Str::studly($modelName) . 'Controller';

        // Determine controller path (normal or admin)
        $pathKey = $isAdminCrudIncluded ? 'admin_controller_path' : 'controller_path';
        $controllerPath = config("code_generator.{$pathKey}", $isAdminCrudIncluded ? 'Http/Controllers/Admin' : 'Http/Controllers');

        $fullPath = app_path("{$controllerPath}/{$controllerClassName}.php");
        $this->createDirectoryIfMissing(dirname($fullPath));

        // Generate content
        $content = $this->getReplacedContent($controllerClassName, $isAdminCrudIncluded);

        // Save controller file
        $this->saveFile($fullPath, $content, CodeGeneratorFileType::CONTROLLER);

        $this->appendApiRoute($controllerClassName, $isAdminCrudIncluded);
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/controller.stub';
    }

    /**
     * Get the replaced content for the controller file.
     */
    public function getReplacedContent(string $controllerClassName, bool $isAdminCrudIncluded = false): string
    {
        return $this->getStubContents(
            $this->getStubPath(),
            $this->getStubVariables($controllerClassName, $isAdminCrudIncluded),
            $isAdminCrudIncluded
        );
    }

    /**
     * Get the variables to replace in the stub file.
     */
    public function getStubVariables(string $controllerClassName, bool $isAdminCrudIncluded = false): array
    {
        $modelName = $this->argument('model') ? ucfirst($this->argument('model')) : '';

        return [
            'namespace' => $isAdminCrudIncluded ? config('code_generator.admin_controller_path', 'Http\Controllers\Admin') : config('code_generator.controller_path', 'Http\Controllers'),
            'class' => preg_replace('/Controller.*$/i', '', ucfirst($controllerClassName)),
            'className' => $controllerClassName,
            'relatedModelNamespace' => 'use App\\' . config('code_generator.model_path', 'Models') . '\\' . $modelName,
            'modelName' => $modelName,  // used in generating methods
        ];
    }

    /**
     * Inject additional use statements into the controller file.
     */
    protected function injectUseStatements(
        string $mainContent,
        bool $includeServiceFile,
        bool $includeRequestFile,
        bool $includeResourceFile,
        string $className
    ): string {
        $additionalUseStatements = [];

        // Add service file use statement
        $mainContent = str_replace(
            '{{ service }}',
            $includeServiceFile ? 'use App\\' . config('code_generator.service_path', 'Services') . '\\' . $className . 'Service;' : '',
            $mainContent
        );

        // Add request file use statement
        $mainContent = str_replace(
            '{{ request }}',
            $includeRequestFile ? 'use App\\' . config('code_generator.request_path', 'Http\Requests') . "\\{$className}\\Request as {$className}Request;" : '',
            $mainContent
        );

        // Add resource file use statements
        $includeResourceFile ? array_push(
            $additionalUseStatements,
            'use App\\' . config('code_generator.resource_path', 'Http\Resources') . "\\{$className}\\Resource;",
            'use App\\' . config('code_generator.resource_path', 'Http\Resources') . "\\{$className}\\Collection;"
        ) : null;

        $useInsert = implode(PHP_EOL, $additionalUseStatements);

        return str_replace(
            'use App\Http\Controllers\Controller;',
            'use App\Http\Controllers\Controller;' . PHP_EOL . $useInsert,
            $mainContent
        );
    }

    /**
     * Get the contents of the stub file with replaced variables.
     */
    public function getStubContents(string $mainStub, array $stubVariables = [], bool $isAdminCrudIncluded = false): string
    {
        $includeServiceFile = (bool) $this->option('service');
        $includeResourceFile = (bool) $this->option('resource');
        $includeRequestFile = (bool) $this->option('request');
        $mainContent = file_get_contents($mainStub);

        $className = $stubVariables['class'];
        $modelName = $stubVariables['modelName'];
        $singularInstance = lcfirst($className);
        $singularObj = '$' . $singularInstance . 'Obj';

        $methods = $isAdminCrudIncluded ?  ['index', 'store', 'show', 'update', 'destroy'] : explode(',', $this->option('methods') ?? '');
        $methods = $isAdminCrudIncluded ? ['index', 'store', 'show', 'update', 'destroy'] : explode(',', $this->option('methods') ?? '');
        // Replace stub variables in base content
        foreach ($stubVariables as $search => $replace) {
            $mainContent = str_replace('{{ ' . $search . ' }}', $replace, $mainContent);
        }

        // Replace service property and constructor
        $mainContent = str_replace(
            '{{ singularService }}',
            $includeServiceFile ? 'private $' . $singularInstance . 'Service;' : '',
            $mainContent
        );

        $mainContent = str_replace(
            '{{ serviceObj }}',
            $includeServiceFile ? '$this->' . $singularInstance . 'Service = new ' . $className . 'Service;' : '',
            $mainContent
        );

        // Conditionally inject use statements
        $mainContent = $this->injectUseStatements(
            $mainContent,
            $includeServiceFile,
            $includeRequestFile,
            $includeResourceFile,
            $className
        );

        // Append methods
        $methodContents = '';
        foreach ($methods as $method) {
            $methodStubPath = __DIR__ . "/../../stubs/controller.{$method}.stub";
            if (! file_exists($methodStubPath)) {
                continue;
            }

            $methodContent = file_get_contents($methodStubPath);
            $pluralVar = Str::plural($singularInstance);
            $classObject = "{$modelName} \${$singularInstance}";

            // Common replacements
            $methodContent = str_replace(
                '{{ requestName }}',
                $includeRequestFile ? "{$className}Request \$request" : 'Request $request',
                $methodContent
            );

            $methodContent = str_replace(
                '{{ updaterRequestName }}',
                $includeRequestFile ? "{$classObject}, {$className}Request \$request" : $classObject,
                $methodContent
            );

            $methodContent = str_replace('{{ classObject }}', $classObject, $methodContent);

            switch ($method) {
                case 'index':
                    $indexReturn = $includeResourceFile
                        ? "return \$this->collection(new Collection(\${$pluralVar}));"
                        : "return \$this->success(\${$pluralVar});";

                    $indexBody = "\${$pluralVar} = \$this->{$singularInstance}Service->collection(\$request->all());" . PHP_EOL .
                        self::INDENT . self::INDENT . $indexReturn;

                    $methodContent = str_replace('{{ indexMethod }}', $includeServiceFile ? $indexBody : '', $methodContent);
                    break;

                case 'store':
                    $validated = $includeRequestFile ? '$request->validated()' : '';
                    $storeBody = "{$singularObj} = \$this->{$singularInstance}Service->store({$validated});" . PHP_EOL .
                        self::INDENT . self::INDENT . "return \$this->success({$singularObj});";

                    $methodContent = str_replace('{{ storeMethod }}', $includeServiceFile ? $storeBody : '', $methodContent);
                    break;

                case 'show':
                    $id = $singularInstance . '->id';
                    $showBody = $includeServiceFile
                        ? "{$singularObj} = \$this->{$singularInstance}Service->resource(\${$id});" . PHP_EOL .
                        self::INDENT . self::INDENT . "return \$this->resource(new Resource({$singularObj}));"
                        : '';

                    $methodContent = str_replace('{{ showMethod }}', $includeServiceFile ? $showBody : '', $methodContent);
                    break;

                case 'update':
                    $validated = $includeRequestFile ? ' $request->validated()' : '';
                    $updateBody = "{$singularObj} = \$this->{$singularInstance}Service->update(\${$singularInstance},{$validated});" . PHP_EOL .
                        self::INDENT . self::INDENT . "return \$this->success({$singularObj});";

                    $methodContent = str_replace('{{ updateMethod }}', $includeServiceFile ? $updateBody : '', $methodContent);
                    break;

                case 'destroy':
                    $destroyBody = "\$result = \$this->{$singularInstance}Service->destroy(\${$singularInstance}->id);" . PHP_EOL .
                        self::INDENT . self::INDENT . 'return $this->success($result);';

                    $methodContent = str_replace('{{ destroyMethod }}', $includeServiceFile ? $destroyBody : '', $methodContent);
                    break;
            }

            $methodContents .= PHP_EOL . $methodContent . PHP_EOL;
        }

        return $mainContent . $methodContents . PHP_EOL . '}' . PHP_EOL;
    }

    protected function createDirectoryIfMissing(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    /**
     * Append API route for the model to the routes file.
     */
    protected function appendApiRoute(string $controllerClassName, bool $isAdminCrudIncluded = false): void
    {
        $resource = Str::plural(Str::kebab($this->argument('model')));
        $methodsArray = explode(',', $this->option('methods') ?? '');
        $methodCount = $isAdminCrudIncluded ? 5 : count($methodsArray);

        $apiPath = base_path($isAdminCrudIncluded ? 'routes/api-admin.php' : 'routes/api.php');
        $stubPath = __DIR__ . '/../../stubs/' . ($isAdminCrudIncluded ? 'api.admin.route.stub' : 'api.routes.stub');
        $controllerPath = config(
            'code_generator.' . ($isAdminCrudIncluded ? 'admin_controller_path' : 'controller_path'),
            $isAdminCrudIncluded ? 'Http\Controllers\Admin' : 'Http\Controllers'
        );

        $routeType = $methodCount === 5 ? 'apiResource' : 'resource';
        $routeOptions = $methodCount === 5 ? '' : "->only(['" . implode("', '", $methodsArray) . "'])";
        $routeEntry = "Route::{$routeType}('{$resource}', \\App\\{$controllerPath}\\{$controllerClassName}::class){$routeOptions};";

        // Load content (stub if file doesn't exist, else append)
        $baseContent = file_exists($apiPath)
            ? file_get_contents($apiPath)
            : file_get_contents($stubPath);

        $finalContent = rtrim($baseContent) . PHP_EOL . $routeEntry . PHP_EOL;

        file_put_contents($apiPath, $finalContent);
    }
}
