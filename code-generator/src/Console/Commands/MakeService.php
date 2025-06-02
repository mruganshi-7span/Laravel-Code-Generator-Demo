<?php

namespace Sevenspan\CodeGenerator\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Sevenspan\CodeGenerator\Traits\FileManager;
use Sevenspan\CodeGenerator\Enums\CodeGeneratorFileType;

class MakeService extends Command
{
    use FileManager;
    const INDENT = '    ';
    protected $signature = 'codegenerator:service {model : The name of the service class to generate.}
                                                  {--overwrite : is overwriting this file is selected}';
    protected $description = 'Create a new service class with predefined methods for resource';
    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle()
    {
        $serviceClass = Str::studly($this->argument('model'));

        // Define the path for the service file
        $serviceFilePath = app_path(config('code_generator.service_path', 'Services') . "/{$serviceClass}Service.php");

        $this->createDirectoryIfMissing(dirname($serviceFilePath));

        $content = $this->getReplacedContent($serviceClass);

        // Create or overwrite file and get log the status and message 
        $this->saveFile(
            $serviceFilePath,
            $content,
            CodeGeneratorFileType::SERVICE
        );
    }

    /**
     * Get the contents of the stub file with replaced variables.
     *
     * @param string $stubPath
     * @param array $stubVariables
     * @return string
     */
    protected function getStubContents(string $stubPath, array $stubVariables): string
    {
        $content = file_get_contents($stubPath);
        foreach ($stubVariables as $search => $replace) {
            $content = str_replace('{{ ' . $search . ' }}', $replace, $content);
        }

        return $content;
    }

    /**
     * Generate the final content for the service file.
     *
     * @param string $serviceClass
     * @return string
     */
    protected function getReplacedContent(string $serviceClass): string
    {
        return $this->getStubContents(
            $this->getStubPath(),
            $this->getStubVariables($serviceClass)
        );
    }

    /**
     * @param string $path
     */
    protected function createDirectoryIfMissing(string $path): void
    {
        // Create the directory if it doesn't exist
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/service.stub';
    }

    /**
     * Get the variables to replace in the stub file.
     *
     * @param string $serviceClass
     * @return array
     */
    protected function getStubVariables(string $serviceClass): array
    {
        $modelName = Str::studly($serviceClass);
        $modelVariable = Str::camel($serviceClass);
        $modelInstance = $modelVariable . 'Model';

        return [
            'serviceClassNamespace' => 'App\\' . config('code_generator.service_path', 'Services'),
            'relatedModelNamespace' => config('code_generator.model_path', 'Models') . "\\{$modelName}",
            'serviceClass'          => "{$modelName}Service",
            'modelObject'           => "private {$modelName} \${$modelInstance}",
            'resourceMethod'        => $this->getResourceMethod($modelInstance),
            'collectionMethod'      => $this->getCollectionMethod($modelVariable, $modelInstance),
            'storeMethod'           => $this->getStoreMethod($modelVariable, $modelInstance),
            'updateMethod'          => $this->getUpdateMethod($modelVariable),
            'deleteMethod'          => $this->getDeleteMethod($modelVariable),
        ];
    }

    /**
     * Generate the resource method for the service.
     *
     * @param string $modelInstance
     * @return string
     */
    protected function getResourceMethod(string $modelInstance): string
    {
        $query = '$query';

        return "{$query} = \$this->{$modelInstance}->getQB();" . PHP_EOL .
            self::INDENT . "if (is_numeric(\$id)) {" . PHP_EOL .
            self::INDENT . self::INDENT . "{$query} = {$query}->whereId(\$id);" . PHP_EOL .
            self::INDENT . "} else {" . PHP_EOL .
            self::INDENT . self::INDENT . "{$query} = {$query}->whereUuid(\$id);" . PHP_EOL .
            self::INDENT . "}" . PHP_EOL .
            self::INDENT . "return {$query}->firstOrFail();";
    }

    /**
     * Generate the collection method for the service.
     *
     * @param string $modelVar
     * @param string $modelInstance
     * @return string
     */
    protected function getCollectionMethod(string $modelVar, string $modelInstance): string
    {
        $query = '$query';

        return "{$query} = \$this->{$modelInstance}->getQB();" . PHP_EOL .
            self::INDENT . "return (isset(\$inputs['limit']) && \$inputs['limit'] != -1) ? {$query}->paginate(\$inputs['limit']) : {$query}->get();";
    }

    /**
     * Generate the store method for the service.
     *
     * @param string $modelVar
     * @param string $modelInstance
     * @return string
     */
    protected function getStoreMethod(string $modelVar, string $modelInstance): string
    {
        $modelVariable = '$' . $modelVar;

        return "{$modelVariable} = \$this->{$modelInstance}->create(\$inputs);" . PHP_EOL .
            self::INDENT . "return {$modelVariable};";
    }

    /**
     * Generate the update method for the service.
     *
     * @param string $modelVar
     * @return string
     */
    protected function getUpdateMethod(string $modelVar): string
    {
        $modelVariable = '$' . $modelVar;

        return "{$modelVariable} = \$this->resource(\$id);" . PHP_EOL .
            self::INDENT . "{$modelVariable}->update(\$inputs);" . PHP_EOL .
            self::INDENT . "{$modelVariable} = \$this->resource({$modelVariable}->id);" . PHP_EOL .
            self::INDENT . "return {$modelVariable};";
    }

    /**
     * Generate the delete method for the service.
     *
     * @param string $modelVar
     * @return string
     */
    protected function getDeleteMethod(string $modelVar): string
    {
        $modelVariable = '$' . $modelVar;

        return "{$modelVariable} = \$this->resource(\$id, \$inputs);" . PHP_EOL .
            self::INDENT . "{$modelVariable}->delete();" . PHP_EOL .
            self::INDENT . "\$data['message'] = __('deleteAccountSuccessMessage');" . PHP_EOL .
            self::INDENT . "return \$data;";
    }
}
