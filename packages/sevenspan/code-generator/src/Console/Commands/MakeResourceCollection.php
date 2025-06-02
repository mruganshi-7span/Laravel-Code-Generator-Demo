<?php

namespace Sevenspan\CodeGenerator\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Sevenspan\CodeGenerator\Traits\FileManager;
use Sevenspan\CodeGenerator\Enums\CodeGeneratorFileType;


class MakeResourceCollection extends Command
{
    use FileManager;
    protected $signature = 'codegenerator:resource-collection {model : The name of the model for the resource collection}
                                                              {--overwrite : is overwriting this file is selected}';
    protected $description = 'Generate a resource collection class for a specified model.';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle()
    {
        $modelName = Str::studly($this->argument('model'));

        // Define the path for the resource collection file
        $resourceFilePath = app_path(config('code_generator.resource_path', 'http\Resources') . "/{$modelName}/Collection.php");

        $this->createDirectoryIfMissing(dirname($resourceFilePath));
        $content = $this->getReplacedContent($modelName);

        // Create or overwrite file and get log the status and message
        $this->createOrOverwriteFile(
            $resourceFilePath,
            $content,
            CodeGeneratorFileType::RESOURCE_COLLECTION
        );
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/resource-collection.stub';
    }

    /**
     * Get the variables to replace in the stub file.
     *
     * @param string $modelName
     * @return array
     */
    protected function getStubVariables($modelName): array
    {
        return [
            'namespace' => "App\\" . config('code_generator.resource_path', 'Resources') . "\\{$modelName}",
            'modelName' => $modelName,
            'resourceNamespace' => config('code_generator.resource_path', 'Http\Resources'),
        ];
    }

    /**
     * Generate the final content for the resource collection file.
     *
     * @param string $modelName
     * @return string
     */
    protected function getReplacedContent($modelName): string
    {
        $stubPath = $this->getStubPath();

        $content = file_get_contents($stubPath);

        $stubVariables = $this->getStubVariables($modelName);
        foreach ($stubVariables as $search => $replace) {
            $content = str_replace('{{ ' . $search . ' }}', $replace, $content);
        }

        return $content;
    }

    /**
     * @param string $path
     */
    protected function createDirectoryIfMissing($path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }
    }
}
