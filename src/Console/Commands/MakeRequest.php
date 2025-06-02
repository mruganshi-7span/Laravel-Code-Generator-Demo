<?php

namespace Mruganshi\CodeGenerator\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Mruganshi\CodeGenerator\Traits\FileManager;
use Mruganshi\CodeGenerator\Enums\CodeGeneratorFileType;

class MakeRequest extends Command
{
    use FileManager;

    private const INDENT = '    ';

    protected $signature = 'codegenerator:request  {model : The related model for the observer.}
                                                   {--rules= :comma seperated list of rules (e.g, Name:required,email:nullable )} 
                                                   {--overwrite : is overwriting this file is selected}';

    protected $description = 'Generate a custom form request with validation rules';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle()
    {
        $relatedModelName = Str::studly($this->argument('model'));

        // Define the path for the request file
        $requestFilePath = app_path(config('code_generator.request_path', 'Requests') . "/{$relatedModelName}" . "/Request.php");
        $this->createDirectoryIfMissing(dirname($requestFilePath));

        $content = $this->getReplacedContent($relatedModelName);

        // Create or overwrite file and get log the status and message
        $this->saveFile(
            $requestFilePath,
            $content,
            CodeGeneratorFileType::REQUEST
        );
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/request.stub';
    }

    /**
     * Generate validation rules fields from command options.
     *
     * @return string
     */
    protected function getValidationFields(): string
    {
        $rules = $this->option('rules');

        if (!$rules) return '';

        $fields = explode(',', $rules);
        $lines = [];

        foreach ($fields as $field) {
            [$name, $rule] = explode(':', $field);
            $lines[] = self::INDENT . self::INDENT . "'" . $name . "' => '" . $rule . "',";
        }

        return implode("\n", $lines);
    }

    /**
     * Get the variables to replace in the stub file.
     *
     * @param string $relatedModelName
     * @return array
     */
    protected function getStubVariables($relatedModelName): array
    {
        $relatedModel = $this->argument('model');
        return [
            'namespace'        => 'App\\' . config('code_generator.request_path', 'Http\Requests') . '\\' . $relatedModel,
            'class'            => 'Request',
            'validationFields' => $this->getValidationFields(),
        ];
    }

    /**
     * Replace stub variables with actual content.
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
     * Generate the final content for the request file.
     *
     * @param string $relatedModelName
     * @return string
     */
    protected function getReplacedContent($relatedModelName): string
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables($relatedModelName));
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
