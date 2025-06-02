<?php

namespace Mruganshi\CodeGenerator\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Mruganshi\CodeGenerator\Traits\FileManager;
use Mruganshi\CodeGenerator\Enums\CodeGeneratorFileType;

class MakeModel extends Command
{
    use FileManager;

    private const INDENT = '    ';

    protected $signature = 'codegenerator:model {model : The name of the model} 
                                                {--fields= : Comma-separated fields (e.g., name,age)} 
                                                {--relations=* : Model relationships with their foreign key and local key (e.g., Post:hasMany:user_id:id,User:belongsTo:post_id:id)} 
                                                {--methods= : Comma-separated list of controller methods to generate api routes (e.g., index,show,store,update,destroy)}
                                                {--softDelete : Include soft delete} 
                                                {--factory : if factory file is included}
                                                {--traits= : Comma-separated traits to include in the model}
                                                {--overwrite : is overwriting this file is selected}';

    protected $description = 'Generate a custom Eloquent model with optional fields, relations, soft deletes, and traits.';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $modelClass = Str::studly($this->argument('model'));
        $modelFilePath = app_path(config('code_generator.model_path', 'Models') . "/{$modelClass}.php");

        $this->createDirectoryIfMissing(dirname($modelFilePath));
        $content = $this->getReplacedContent($modelClass);

        // Create or overwrite file and get log the status and message
        $this->saveFile(
            $modelFilePath,
            $content,
            CodeGeneratorFileType::MODEL
        );
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/model.stub';
    }

    /**
     * Generate the final content for the model file.
     */
    protected function getReplacedContent(string $modelClass): string
    {
        $stub = file_get_contents($this->getStubPath());
        $variables = $this->getStubVariables($modelClass);

        foreach ($variables as $key => $value) {
            $stub = str_replace('{{ ' . $key . ' }}', $value, $stub);
        }

        return $stub;
    }

    /**
     * Get the variables to replace in the stub file.
     */
    protected function getStubVariables(string $modelClass): array
    {
        $isSoftDeleteIncluded = $this->option('softDelete');
        $hiddenFields = ["'created_at'", "'updated_at'"];
        if ($isSoftDeleteIncluded) {
            $hiddenFields[] = "'deleted_at'";
        }
        $traitInfo = $this->getTraitInfo();
        $relationMethods = $this->getRelations();
        $relatedModelImports = $this->getRelatedModels();

        return [
            'namespace' => 'App\\' . config('code_generator.model_path', 'Models'),
            'class' => $modelClass,
            'traitNamespaces' => $traitInfo['uses'],
            'traits' => $traitInfo['apply'],
            'relatedModelNamespaces' => ! empty($relatedModelImports) ? implode("\n", array_map(fn($model) => "use App\\Models\\$model;", $relatedModelImports)) : '',
            'relation' => $relationMethods,
            'fillableFields' => $this->getFillableFields($this->option('fields')),
            'deletedAt' => $isSoftDeleteIncluded ? "'deleted_at' => 'datetime'," : '',
            'deletedBy' => $isSoftDeleteIncluded ? "'deleted_by'," : '',
            'hiddenFields' => implode(', ', $hiddenFields),
        ];
    }

    /**
     * Prepare fillable fields for the model.
     *
     * @param  string|null  $fieldsOption
     */
    protected function getFillableFields($fieldsOption): string
    {
        $fillableFields = '';
        if ($fieldsOption) {
            $fields = explode(',', $fieldsOption);
            $fieldNames = [];

            foreach ($fields as $field) {
                $fieldName = explode(':', $field)[0];
                $fieldNames[] = "'" . trim($fieldName) . "',";
            }

            $fillableFields = implode(",\n        ", $fieldNames);
        }
        return $fillableFields;
    }

    /**
     * Get trait information for the model.
     */
    protected function getTraitInfo(): array
    {
        $softDeleteIncluded = $this->option('softDelete');
        $isFactoryIncluded = $this->option('factory');

        $traitUseStatements = [];
        $traitNames = [];

        // Add HasFactory trait if factory file is included
        if ($isFactoryIncluded) {
            $traitUseStatements[] = 'use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;';
            $traitNames[] = 'HasFactory';
        }

        // Add SoftDeletes trait if soft delete is included
        if ($softDeleteIncluded) {
            $traitUseStatements[] = 'use Illuminate\\Database\\Eloquent\\SoftDeletes;';
            $traitNames[] = 'SoftDeletes';
        }

        // Add custom traits if specified
        $customTraits = $this->option('traits');
        if ($customTraits) {
            foreach (explode(',', $customTraits) as $trait) {
                $trait = trim($trait);
                $traitUseStatements[] = 'use App\\' . config('code_generator.trait_path', 'Traits') . "\\$trait;";
                $traitNames[] = $trait;
            }
        }

        return [
            'uses' => implode("\n", $traitUseStatements),
            'apply' => empty($traitNames) ? '' : 'use ' . implode(', ', $traitNames) . ';',
        ];
    }

    /**
     * Generate relation methods for the model.
     */
    protected function getRelations(): string
    {
        $relations = $this->option('relations');
        if (!$relations) {
            return '';
        }

        $relationMap = [
            'One to One' => 'hasOne',
            'One to Many' => 'hasMany',
            'Many to One' => 'belongsTo',
            'Many to Many' => 'belongsToMany',
            'Has One Through' => 'hasOneThrough',
            'Has Many Through' => 'hasManyThrough',
            'One To One (Polymorphic)' => 'morphOne',
            'One To Many (Polymorphic)' => 'morphMany',
            'Many To Many (Polymorphic)' => 'morphToMany',
        ];

        $methods = [];

        foreach ($relations as $relation) {
            $methodName = Str::camel(Str::plural($relation['related_model']));
            $relationType = $relationMap[$relation['relation_type']];

            $method = self::INDENT . 'public function ' . $methodName . '()' . PHP_EOL . self::INDENT . '{' . PHP_EOL . self::INDENT . self::INDENT . 'return $this->' . $relationType . '(';

            if (in_array($relationType, ['hasOneThrough', 'hasManyThrough'])) {
                $args = [
                    $relation['related_model'] . '::class',
                    $relation['intermediate_model'] . '::class',
                    "'{$relation['intermediate_foreign_key']}'",
                    "'{$relation['foreign_key']}'",
                    "'{$relation['local_key']}'",
                    "'{$relation['intermediate_local_key']}'",
                ];
            } else {
                $args = [$relation['related_model'] . '::class'];

                if (! empty($relation['foreign_key'])) {
                    $args[] = "'{$relation['foreign_key']}'";
                }

                if (! empty($relation['local_key'])) {
                    $args[] = "'{$relation['local_key']}'";
                }
            }

            $method .= implode(', ', $args) . ');' . PHP_EOL;
            $method .= self::INDENT . '}' . PHP_EOL;

            $methods[] = $method;
        }

        return rtrim(implode(PHP_EOL, $methods));
    }

    /**
     * Get related models for imports.
     */
    protected function getRelatedModels(): array
    {
        $relations = $this->option('relations');
        if (!$relations) {
            return [];
        }

        $models = [];

        // Extract model names from relations
        foreach ($relations as $relation) {
            if (!is_array($relation) || empty($relation['related_model'])) {
                continue;
            }
            $models[] = Str::studly($relation['related_model']);
        }

        return array_unique($models);
    }

    /**
     * Create the directory if it does not exist.
     */
    protected function createDirectoryIfMissing(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }
}
