<?php

namespace Mruganshi\CodeGenerator\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RestApi extends Component
{
    // Data properties
    public array $relationData = [];
    public array $fieldsData = [];
    public array $notificationData = [];

    public array $tableNames = [];

    public array $modelNames = [];

    public $fieldNames = [];
    public $columnNames = [];
    public $baseFields = [];
    public $intermediateFields = [];


    public $generalError = '';
    public $errorMessage = "";
    public $successMessage = '';

    public $isForeignKey = false;
    public $foreignModelName = '';
    public $referencedColumn = '';
    public $onDeleteAction = '';
    public $onUpdateAction = '';

    // Modal visibility properties
    public $isAddRelModalOpen = false;
    public $isRelDeleteModalOpen = false;
    public $isRelEditModalOpen = false;
    public $isAddFieldModalOpen = false;
    public $isDeleteFieldModalOpen = false;
    public $isEditFieldModalOpen = false;
    public $isNotificationModalOpen = false;

    // Form inputs
    public $modelName;

    public $relations, $relationId, $fields, $fieldId;

    // Relationship form fields
    public $related_model, $relation_type, $intermediate_model, $foreign_key, $local_key, $intermediate_foreign_key, $intermediate_local_key;

    // Field properties
    public $data_type, $column_name, $column_validation;

    // Notification properties
    public $class_name, $data, $subject, $body;

    // Method checkboxes
    public $index = false;
    public $store = false;
    public $show = false;
    public $update = false;
    public $destroy = false;

    // File generation options
    public $modelFile = false;
    public $migrationFile = false;
    public $softDeleteFile = false;
    public $crudFile = false;
    public $serviceFile = false;
    public $notificationFile = false;
    public $resourceFile = false;
    public $requestFile = false;
    public $traitFiles = false;
    public $overwriteFiles = false;
    public $observerFile = false;
    public $factoryFile = false;
    public $policyFile = false;

    // Trait checkboxes
    public $BootModel = false;
    public $PaginationTrait = false;
    public $ResourceFilterable = false;
    public $HasUuid = false;
    public $HasUserAction = false;
    public $isGenerating = false;

    // Validation rules
    protected $rules = [
        'modelName' => 'required|regex:/^[A-Z][A_Za-z]+$/',
        'related_model' => 'required|regex:/^[A-Z][A-Za-z]+$/',
        'relation_type' => 'required',
        'intermediate_model' => 'required|different:modelName|different:related_model|regex:/^[A-Z][A-Za-z]+$/',
        'foreign_key' => 'required|string|regex:/^[a-z_]+$/',
        'local_key' => 'required|string|regex:/^[a-z_]+$/',

        'intermediate_foreign_key' => 'required|string|regex:/^[a-z_]+$/',
        'intermediate_local_key' => 'required|string|regex:/^[a-z_]+$/',

        'data_type' => 'required',
        'column_name' => 'required|regex:/^[a-z_]+$/',
        'column_validation' => 'required',
        'class_name' => 'required|regex:/^[A-Z][A-Za-z]+$/',
        'data' => 'required|regex:/^[A-Za-z0-9]+:[A-Za-z0-9]+(?:,[A-Za-z0-9]+:[A-Za-z0-9]+)*$/',
        'subject' => 'required|regex:/^[A-Za-z ]+$/',
        'body' => 'required|regex:/^[A-Za-z ]+$/',
        'foreignModelName' => 'required|regex:/^[a-z0-9_]+$/',
        'onDeleteAction' => 'nullable|in:restrict,cascade,set null,no action',
        'onUpdateAction' => 'nullable|in:restrict,cascade,set null,no action',
    ];

    // Custom validation messages
    public $messages = [
        'modelName.regex' => 'The Model Name must start with an uppercase letter and contain only letters.',
        'related_model.regex' => 'The Model Name must start with an uppercase letter and contain only letters.',
    ];

    // Initialize component
    public function render()
    {
        return view('code-generator::livewire.rest-api');
    }

    // Add updated method for foreign key checkbox
    public function updatedIsForeignKey($value)
    {
        if (!$value) {
            // Checkbox was unchecked - clear related fields
            $this->foreignModelName = '';
            $this->referencedColumn = '';
        }
    }

    // Add mount method to restore state of form
    public function mount()
    {
        $this->loadMigrationTableNames();
        $this->loadModelNames();
    }

    // Live validation for form fields
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }


    // Update notification file checkbox state and open modal if checked
    public function updatedNotificationFile(): void
    {
        if ($this->notificationFile) {
            $this->isNotificationModalOpen = true;
        }
    }

    public function validateFieldsAndMethods()
    {
        $this->errorMessage = "";

        // Check if any file that requires fields is selected
        $requiresFields = $this->modelFile || $this->migrationFile || $this->requestFile || $this->factoryFile;

        // If fields are required but none are added
        if ($requiresFields && empty($this->fieldsData)) {
            $this->errorMessage = "Please add at least one field for the selected file types.";
            return false;
        }

        // Check for methods
        if (!($this->index || $this->store || $this->show || $this->destroy || $this->update)) {
            $this->errorMessage = "Please select at least one method.";
            return false;
        }

        return true;
    }

    // Open delete modal
    public function openDeleteModal($id): void
    {
        $this->relationId = $id;
        $this->isRelDeleteModalOpen = true;
    }

    // Delete relation in table
    public function deleteRelation(): void
    {
        $this->relationData = array_filter($this->relationData, function ($relation) {
            return $relation['id'] !== $this->relationId;
        });
        $this->isRelDeleteModalOpen = false;
    }

    // Open edit relation modal
    public function openEditRelationModal($relationId): void
    {
        $this->relationId = $relationId;
        $this->isRelEditModalOpen = true;
        $relation = collect($this->relationData)->firstWhere('id', $relationId);
        if ($relation) {
            $this->fill($relation);
        }
    }

    // Resets modal form fields
    public function resetModal()
    {
        $this->reset([
            'related_model',
            'relation_type',
            'intermediate_model',
            'foreign_key',
            'local_key',
            'data_type',
            'isForeignKey',
            'column_name',
            'column_validation',
            'fieldId',
            'isForeignKey',
            'foreignModelName',
            'referencedColumn',
            'intermediate_foreign_key',
            'intermediate_local_key'
        ]);
        $this->resetErrorBag();
    }

    // Save relation data
    public function saveRelation(): void
    {
        $rules = [
            'related_model' => $this->rules['related_model'],
            'relation_type' => $this->rules['relation_type'],
            'foreign_key' => $this->rules['foreign_key'],
            'local_key' => $this->rules['local_key'],
        ];

        $isThroughRelation = in_array($this->relation_type, ['Has One Through', 'Has Many Through']);

        // Add intermediate model rules only for through relations
        if ($isThroughRelation) {
            $rules['intermediate_model'] = $this->rules['intermediate_model'];
            $rules['intermediate_foreign_key'] = $this->rules['intermediate_foreign_key'];
            $rules['intermediate_local_key'] = $this->rules['intermediate_local_key'];
        }

        $this->validate($rules);

        if (
            $this->foreign_key === $this->local_key &&
            $this->related_model === $this->modelName
        ) {
            $this->addError('local_key', 'Foreign key and local key cannot be the same as base model for self-relation.');
            return;
        }

        //// Custom Logic Validation for "Through" Relationships
        if ($isThroughRelation) {
            if ($this->foreign_key === $this->intermediate_foreign_key) {
                $this->addError('intermediate_foreign_key', 'The "Foreign Key on Intermediate Model" must be different from the "Foreign Key on Related Model"');
            }
        }

        $relationData = [
            'related_model' => $this->related_model,
            'relation_type' => $this->relation_type,
            'foreign_key' => $this->foreign_key,
            'local_key' => $this->local_key,
            'intermediate_model' => $isThroughRelation ? $this->intermediate_model : '',
            'intermediate_foreign_key' => $isThroughRelation ? $this->intermediate_foreign_key : '',
            'intermediate_local_key' => $isThroughRelation ? $this->intermediate_local_key : '',
        ];

        // Check for duplicates
        foreach ($this->relationData as $existing) {
            if (
                $existing['related_model'] === $this->related_model &&
                $existing['relation_type'] === $this->relation_type &&
                $existing['foreign_key'] === $this->foreign_key &&
                $existing['local_key'] === $this->local_key &&
                (!isset($existing['intermediate_model']) || $existing['intermediate_model'] === $this->intermediate_model)
            ) {
                $this->addError('related_model', 'This exact relation already exists.');
                return;
            }
        }

        // Update or add relation
        if ($this->relationId) {
            foreach ($this->relationData as &$relation) {
                if ($relation['id'] === $this->relationId) {
                    $relation = array_merge(['id' => $this->relationId], $relationData);
                    break;
                }
            }
            unset($relation);  // break reference
        } else {
            $this->relationData[] = ['id' => Str::random(8)] + $relationData;
        }
        $this->isAddRelModalOpen = false;
        $this->isRelEditModalOpen = false;
        $this->reset(['related_model', 'relation_type', 'intermediate_model', 'foreign_key', 'local_key', 'intermediate_foreign_key', 'intermediate_local_key']);
        $this->relationId = null;
    }

    // Open Edit Field Modal
    public function openEditFieldModal($fieldId): void
    {
        $this->fieldId = $fieldId;
        $field = collect($this->fieldsData)->firstWhere('id', $fieldId);

        if ($field) {
            $this->column_name = $field['column_name'] ?? '';
            $this->data_type = $field['data_type'] ?? '';
            $this->column_validation = $field['column_validation'] ?? '';
            $this->isForeignKey = (bool) ($field['isForeignKey'] ?? false);
            $this->foreignModelName = $field['foreignModelName'] ?? '';
            $this->referencedColumn = $field['referencedColumn'] ?? '';
        }

        $this->isEditFieldModalOpen = true;
    }

    // Opens delete  Field Modal
    public function openDeleteFieldModal($id): void
    {
        $this->fieldId = $id;
        $this->isDeleteFieldModalOpen = true;
    }

    // Deletes field from table
    public function deleteField(): void
    {
        $this->fieldsData = array_filter($this->fieldsData, function ($field) {
            return $field['id'] !== $this->fieldId;
        });
        $this->isDeleteFieldModalOpen = false;
    }

    // Save Fields Data
    public function saveField(): void
    {
        // Check for duplicate column name, excluding the current edited field by ID
        $columnExists = false;
        foreach ($this->fieldsData as $field) {
            if (
                $field['column_name'] === $this->column_name &&
                (!$this->fieldId || $field['id'] !== $this->fieldId)
            ) {
                $columnExists = true;
                break;
            }
        }

        if ($columnExists) {
            $this->addError('column_name', 'You have already taken this column');
            return;
        }

        $rulesToValidate = [
            'data_type' => $this->rules['data_type'],
            'column_name' => $this->rules['column_name'],
            'column_validation' => $this->rules['column_validation'],
        ];

        if ($this->isForeignKey) {
            $rulesToValidate['foreignModelName'] = $this->rules['foreignModelName'];
            $rulesToValidate['referencedColumn'] = $this->rules['local_key'];
            $rulesToValidate['onDeleteAction'] = $this->rules['onDeleteAction'];
            $rulesToValidate['onUpdateAction'] = $this->rules['onUpdateAction'];
        }

        $this->validate($rulesToValidate);


        $fieldData = [
            'data_type' => $this->data_type,
            'column_name' => $this->column_name,
            'column_validation' => $this->column_validation,
            'isForeignKey' => $this->isForeignKey ?? false,
            'foreignModelName' => $this->foreignModelName,
            'referencedColumn' => $this->referencedColumn,
            'onDeleteAction' => $this->onDeleteAction,
            'onUpdateAction' => $this->onUpdateAction,
        ];

        // Update existing field or add new one
        if ($this->fieldId) {
            foreach ($this->fieldsData as &$field) {
                if ($field['id'] === $this->fieldId) {
                    $field = ['id' => $this->fieldId] + $fieldData;
                    break;
                }
            }
            unset($field); // break reference
        } else {
            $this->fieldsData[] = array_merge(['id' => Str::random(8)], $fieldData);
        }
        $this->isAddFieldModalOpen = false;
        $this->isEditFieldModalOpen = false;
        $this->fieldId = null;
        $this->reset(['column_name', 'data_type', 'column_validation', 'isForeignKey', 'foreignModelName', 'referencedColumn']);
    }

    // Save notification data
    public function saveNotification(): void
    {
        $this->validate([
            'class_name' => $this->rules['class_name'],
            'data' => $this->rules['data'],
            'subject' => $this->rules['subject'],
            'body' => $this->rules['body'],
        ]);

        // Store notification data
        $this->notificationData = [
            [
                'class_name' => $this->class_name,
                'data' => $this->data,
                'subject' => $this->subject,
                'body' => $this->body,
            ]
        ];

        $this->isNotificationModalOpen = false;
        $this->reset(['class_name', 'data', 'subject', 'body']);
    }

    //Validate inputs before generation 
    private function validateInputs(): bool
    {
        // Validate model name
        $this->validate(['modelName' => $this->rules['modelName']]);

        // Check if model exists and overwrite is not checked
        $modelPath = app_path('Models/' . $this->modelName . '.php');
        if (File::exists($modelPath) && !$this->overwriteFiles) {
            $this->errorMessage = "Model {$this->modelName} already exists if you want to overwrite it check the 'Overwrite Files' option";
            session()->flash('error', $this->errorMessage);
            $this->dispatch('show-toast', ['message' => $this->errorMessage, 'type' => 'error']);
            return false;
        }

        // Check if notification file is selected but no notification data is provided
        if ($this->notificationFile && empty($this->notificationData)) {
            $this->errorMessage = "Please add notification data before generating files.";
            session()->flash('error', $this->errorMessage);
            $this->dispatch('show-toast', ['message' => $this->errorMessage, 'type' => 'error']);
            return false;
        }

        // Check fields and methods validation
        if (!$this->validateFieldsAndMethods()) {
            session()->flash('error', $this->errorMessage);
            return false;
        }

        return true;
    }

    // Save Form and generate files
    public function save(): void
    {
        try {
            // Validate all inputs first
            if (!$this->validateInputs()) {
                return;
            }
            // Generate files
            $this->generateFiles();
            session()->flash('success', 'Files generated Successfully!');

            // Reset form
            $this->reset();
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            session()->flash('error', $e->getMessage());
            $this->dispatch('show-toast', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    // Generate all selected files
    private function generateFiles(): void
    {
        $selectedTraits = array_filter([
            'ApiResponser',
            'BaseModel',
            $this->BootModel ? 'BootModel' : null,
            $this->PaginationTrait ? 'PaginationTrait' : null,
            $this->ResourceFilterable ? 'ResourceFilterable' : null,
            $this->HasUuid ? 'HasUuid' : null,
            $this->HasUserAction ? 'HasUserAction' : null,
        ]);

        $modelName = $this->modelName;

        // Prepare selected methods
        $selectedMethods = array_filter([
            $this->index ? 'index' : null,
            $this->store ? 'store' : null,
            $this->show ? 'show' : null,
            $this->update ? 'update' : null,
            $this->destroy ? 'destroy' : null,
        ]);

        // Prepare files config for generation
        $files = [
            'model' => $this->modelFile,
            'migration' => $this->migrationFile,
            'softDelete' => $this->softDeleteFile,
            'adminCRUDFile' => $this->crudFile,
            'service' => $this->serviceFile,
            'notification' => $this->notificationFile,
            'resource' => $this->resourceFile,
            'request' => $this->requestFile,
            'traits' => $this->traitFiles,
            'observer' => $this->observerFile,
            'policy' => $this->policyFile,
            'factory' => $this->factoryFile,
        ];

        // Format field and relation strings
        $fieldString = collect($this->fieldsData)->pluck('column_name')->implode(', ');

        // Generate files based on flags
        if ($files['model']) {
            $this->generateModel($modelName, $fieldString, $this->relationData, $selectedMethods, $files['softDelete'], $files['factory'], $selectedTraits, $this->overwriteFiles);
        }

        if ($files['migration']) {
            $this->generateMigration($modelName, $this->fieldsData, $files['softDelete'], $this->overwriteFiles);
        }

        $this->generateController($modelName, $selectedMethods, $files['service'], $files['resource'], $files['request'], $this->overwriteFiles, $files['adminCRUDFile']);

        if ($files['policy']) {
            $this->generatePolicy($modelName, $this->overwriteFiles);
        }

        if ($files['observer']) {
            $this->generateObserver($modelName, $this->overwriteFiles);
        }

        if ($files['service']) {
            $this->generateService($modelName, $this->overwriteFiles);
        }

        if ($files['notification']) {
            $this->generateNotification($modelName, $this->overwriteFiles);
        }

        if ($files['resource']) {
            $this->generateResource($modelName, $this->overwriteFiles);
        }

        if ($files['request']) {
            $this->generateRequest($modelName, $this->fieldsData, $this->overwriteFiles);
        }

        if ($files['factory']) {
            $this->generateFactory($modelName, $this->fieldsData, $this->overwriteFiles);
        }

        if ($selectedTraits) {
            $this->copyTraits($selectedTraits);
        }
    }

    /**
     * HELPER METHODS FOR FILE GENERATION
     */

    // Generate model file
    private function generateModel($modelName, $fieldString, $relations, $selectedMethods, $softDelete, $factory, $selectedTraits, $overwrite)
    {
        Artisan::call('codegenerator:model', [
            'model' => $modelName,
            '--fields' => $fieldString,
            '--relations' => $relations,
            '--methods' => implode(',', $selectedMethods),
            '--softDelete' => $softDelete,
            '--factory' => $factory,
            '--traits' => implode(',', $selectedTraits),
            '--overwrite' => $overwrite
        ]);
    }

    //Generate migration file
    private function generateMigration($modelName, $fields, $softDelete, $overwrite)
    {
        Artisan::call('codegenerator:migration', [
            'model' => $modelName,
            '--fields' => $fields,
            '--softdelete' => $softDelete,
            '--overwrite' => $overwrite
        ]);
    }

    // Generate controller file
    private function generateController($modelName, $selectedMethods, $service, $resource, $request, $overwrite, $adminCrud)
    {
        Artisan::call('codegenerator:controller', [
            'model' => $modelName,
            '--methods' => implode(',', $selectedMethods),
            '--service' => $service,
            '--resource' => $resource,
            '--request' => $request,
            '--overwrite' => $overwrite,
            '--adminCrud' => $adminCrud,
        ]);
    }

    // Generate policy file
    private function generatePolicy($modelName, $overwrite)
    {
        Artisan::call('codegenerator:policy', [
            'model' => $modelName,
            '--overwrite' => $overwrite
        ]);
    }

    // Generate observer file
    private function generateObserver($modelName, $overwrite)
    {
        Artisan::call('codegenerator:observer', [
            'model' => $modelName,
            '--overwrite' => $overwrite
        ]);
    }

    // Generate service file
    private function generateService($modelName, $overwrite)
    {
        Artisan::call('codegenerator:service', [
            'model' => $modelName,
            '--overwrite' => $overwrite
        ]);
    }

    //Generate notification file
    private function generateNotification($modelName, $overwrite)
    {
        $notificationData = !empty($this->notificationData) ? $this->notificationData[0] : [];

        Artisan::call('codegenerator:notification', [
            'className' => $notificationData['class_name'] ?? $modelName . 'Notification',
            '--model' => $modelName,
            '--data' => $notificationData['data'] ?? '',
            '--body' => $notificationData['body'] ?? '',
            '--subject' => $notificationData['subject'] ?? '',
            '--overwrite' => $overwrite
        ]);
    }

    // Generate resource file
    private function generateResource($modelName, $overwrite)
    {
        Artisan::call('codegenerator:resource', [
            'model' => $modelName,
            '--overwrite' => $overwrite
        ]);
    }

    // Generate request file
    private function generateRequest($modelName, $fields, $overwrite)
    {
        $ruleString = implode(',', array_map(function ($field) {
            return $field['column_name'] . ':' . $field['column_validation'];
        }, $fields));

        Artisan::call('codegenerator:request', [
            'model' => $modelName,
            '--rules' => $ruleString,
            '--overwrite' => $overwrite
        ]);
    }

    //Generate factory file
    private function generateFactory($modelName, $fields, $overwrite)
    {
        $fieldString = implode(',', array_map(function ($field) {
            return $field['column_name'] . ':' . $field['data_type'];
        }, $fields));

        Artisan::call('codegenerator:factory', [
            'model' => $modelName,
            '--fields' => $fieldString,
            '--overwrite' => $overwrite
        ]);
    }

    //Copy traits to application
    private function copyTraits($selectedTraits)
    {
        $source = __DIR__ . '/../../TraitsLibrary/Traits';
        $destination = app_path(config('code_generator.trait_path', 'Traits'));

        if (!File::exists($source)) {
            Log::warning('Traits source folder not found: ' . $source);
            return;
        }

        File::ensureDirectoryExists($destination);

        foreach ($selectedTraits as $trait) {
            $fileName = $trait . '.php';
            $sourceFile = $source . DIRECTORY_SEPARATOR . $fileName;
            $destinationFile = $destination . DIRECTORY_SEPARATOR . $fileName;

            if (!File::exists($sourceFile)) {
                Log::warning("Trait file not found in source: $fileName");
                continue;
            }

            if (File::exists($destinationFile)) {
                Log::info("Trait $fileName already exists in destination, skipping.");
                continue;
            }

            File::copy($sourceFile, $destinationFile);
            Log::info("Trait $fileName copied to app/Traits.");
        }
    }

    //Load migration table names from the migrations directory
    public function loadMigrationTableNames()
    {
        $migrationPath = database_path('migrations');
        $files = File::exists($migrationPath) ? File::files($migrationPath) : [];

        $this->tableNames = collect($files)->map(function ($file) {
            if (preg_match('/create_(.*?)_table/', $file->getFilename(), $matches)) {
                return $matches[1];
            }
            return null;
        })->filter()->unique()->values()->toArray();
    }

    // Update field names based on foreign model name
    public function updatedForeignModelName($value)
    {
        if ($value && Schema::hasTable($value)) {
            $this->fieldNames = Schema::getColumnListing($value);
        } else {
            $this->fieldNames = [];
        }
    }

    // Add this method to load model names from migrations
    private function loadModelNames()
    {
        $migrationPath = database_path('migrations');
        if (File::exists($migrationPath)) {
            $files = File::files($migrationPath);
            $this->modelNames = collect($files)
                ->map(function ($file) {
                    // Extract table name from migration file
                    if (preg_match('/create_(.*?)_table/', $file->getFilename(), $matches)) {
                        $tableName = $matches[1];
                        // Convert table name to model name (e.g., user_profiles -> UserProfile)
                        return Str::studly(Str::singular($tableName));
                    }
                    return null;
                })->filter()->unique()->values()->toArray();
        }
    }

    // Add this method to load field names when related model changes
    public function updatedRelatedModel($value)
    {
        if ($value) {
            // Convert model name to table name (plural, snake_case)
            $tableName = Str::plural(Str::snake($value));

            // Get column names from the database schema
            if (Schema::hasTable($tableName)) {
                $this->columnNames = Schema::getColumnListing($tableName);
            } else {
                $this->columnNames = [];
            }
        } else {
            $this->columnNames = [];
        }
    }

    // Add this method to load intermediate fields when intermediate model changes
    public function updatedIntermediateModel($value)
    {
        if ($value) {
            // Convert model name to table name (plural, snake_case)
            $tableName = Str::plural(Str::snake($value));

            // Get column names from the database schema
            if (Schema::hasTable($tableName)) {
                $this->intermediateFields = Schema::getColumnListing($tableName);
            } else {
                $this->intermediateFields = [];
            }
        } else {
            $this->intermediateFields = [];
        }
    }

    // Add this method to handle relation type changes
    public function updatedRelationType($value)
    {
        // If the relation type is not a "through" relation, clear intermediate fields
        if (!in_array($value, ['Has One Through', 'Has Many Through'])) {
            $this->intermediate_model = '';
            $this->intermediate_foreign_key = '';
            $this->intermediate_local_key = '';
            $this->intermediateFields = [];
        }
    }
}
