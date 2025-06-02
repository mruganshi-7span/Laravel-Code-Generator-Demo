<?php

namespace Sevenspan\CodeGenerator\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Sevenspan\CodeGenerator\Traits\FileManager;
use Sevenspan\CodeGenerator\Enums\CodeGeneratorFileType;

class MakeFactory extends Command
{
    use FileManager;
    protected $signature = 'codegenerator:factory {model : The name of the model for which the factory file will be generated.} 
                                                  {--fields= : A comma-separated list of fields with their types (e.g., name:string,id:integer).}
                                                  {--overwrite : is overwriting this file is selected}';

    protected $description = 'Generate a factory file for a given model with optional fields';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle()
    {
        $modelName = Str::studly($this->argument('model'));

        // Define the path for the factory file
        $factoryFilePath = base_path("database/" . config('code_generator.factory_path', 'Factories') . "/{$modelName}Factory.php");

        $this->createDirectoryIfMissing(dirname($factoryFilePath));

        // Parse fields from the --fields option
        $fields = $this->parseFieldsOption($this->option('fields'));

        $content = $this->getReplacedContent($modelName, $fields);

        // Create or overwrite file and get log the status and message
        $this->saveFile(
            $factoryFilePath,
            $content,
            CodeGeneratorFileType::FACTORY
        );
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/factory.stub';
    }

    /**
     * Parse the --fields option into an associative array.
     *
     * @param  string|null  $fieldsOption
     * @return array
     */
    protected function parseFieldsOption(?string $fieldsOption): array
    {
        $parsedFields = [];

        if (! $fieldsOption) {
            return $parsedFields;
        }

        foreach (explode(',', $fieldsOption) as $pair) {
            if (str_contains($pair, ':')) {
                [$name, $type] = explode(':', $pair);
                $parsedFields[trim($name)] = trim($type);
            }
        }

        return $parsedFields;
    }

    /**
     * Generate a factory field definition based on the column name and type.
     *
     * @param  string  $column
     * @param  string  $type
     * @return string
     */
    protected function getFactoryField(string $column, string $type): string
    {
        $fakerTypeMapping = [
            'string'   => "'{$column}' => \$this->faker->word",
            'text'     => "'{$column}' => \$this->faker->text",
            'integer'  => "'{$column}' => \$this->faker->numberBetween(1, 100)",
            'bigint'   => "'{$column}' => \$this->faker->randomNumber()",
            'boolean'  => "'{$column}' => \$this->faker->boolean",
            'datetime' => "'{$column}' => \$this->faker->dateTime()",
            'date'     => "'{$column}' => \$this->faker->date()",
            'time'     => "'{$column}' => \$this->faker->time()",
            'email'    => "'{$column}' => \$this->faker->unique()->safeEmail",
            'name'     => "'{$column}' => \$this->faker->name",
            'uuid'     => "'{$column}' => \$this->faker->uuid",
        ];

        return $fakerTypeMapping[$type] ?? "'{$column}' => null";
    }

    /**
     * Generate the factory fields as a string for the stub.
     *
     * @param  array  $fields
     * @return string
     */
    protected function generateFactoryFields(array $fields): string
    {
        $factoryFieldLines = [];

        foreach ($fields as $column => $type) {
            $factoryFieldLines[] = '      ' . $this->getFactoryField($column, $type) . ',';
        }

        return implode("\n", $factoryFieldLines);
    }

    /**
     * Get the variables to replace in the factory stub.
     *
     * @param  string  $modelName
     * @param  array  $fields
     * @return array
     */
    protected function getStubVariables(string $modelName, array $fields): array
    {
        return [
            'factoryNamespace'       => 'Database\\' . config('code_generator.factory_path', 'Factories'),
            'relatedModelNamespace'  => 'App\\' . config('code_generator.model_path', 'Models') . "\\" . $modelName,
            'factory'                => $modelName . "Factory",
            'fields'                 => $this->generateFactoryFields($fields),
        ];
    }

    /**
     * Replace the variables in the stub content with actual values.
     *
     * @param  string  $stubPath
     * @param  array  $stubVariables
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
     * Generate the final content for the factory file.
     *
     * @param  string  $modelName
     * @param  array  $fields
     * @return string
     */
    protected function getReplacedContent(string $modelName, array $fields): string
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables($modelName, $fields));
    }

    /**
     * @param string $path
     */
    protected function createDirectoryIfMissing(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }
    }
}
