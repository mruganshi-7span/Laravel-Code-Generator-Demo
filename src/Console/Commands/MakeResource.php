<?php

namespace Mruganshi\CodeGenerator\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Mruganshi\CodeGenerator\Traits\FileManager;
use Mruganshi\CodeGenerator\Enums\CodeGeneratorFileType;

class MakeResource extends Command
{
    use FileManager;

    protected $signature = 'codegenerator:resource {model : The name of the model for the resource}
                                                   {--overwrite : is overwriting this file is selected}';

    protected $description = 'Generate a resource class for a specified model.';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle()
    {
        $modelName = Str::studly($this->argument('model'));

        // Define the path for the resource file
        $resourceFilePath = app_path(config('code_generator.resource_path', 'Resources') . "/{$modelName}/Resource.php");

        $this->createDirectoryIfMissing(dirname($resourceFilePath));

        $content = $this->getReplacedContent($modelName);

        // Create or overwrite file and get log the status and message
        $this->saveFile(
            $resourceFilePath,
            $content,
            CodeGeneratorFileType::RESOURCE
        );
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/resource.stub';
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
            'namespace'       => "App\\" . config('code_generator.resource_path', 'Resources') . "\\{$modelName}",
            'class'           => 'Resource',
            'modelName'       => $modelName,
            'modelNamespace'  => "use App\\" . config('code_generator.model_path', 'Models') . "\\{$modelName};",
        ];
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
     * Generate the final content for the resource file.
     *
     * @param string $modelName
     * @return string
     */
    protected function getReplacedContent($modelName): string
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables($modelName));
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
