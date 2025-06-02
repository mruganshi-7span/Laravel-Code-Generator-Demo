<?php

namespace Sevenspan\CodeGenerator\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Sevenspan\CodeGenerator\Traits\FileManager;
use Sevenspan\CodeGenerator\Enums\CodeGeneratorFileType;

class MakeNotification extends Command
{
    use FileManager;

    const INDENT = '    ';

    protected $signature = 'codegenerator:notification {className : Name of the notification class} 
                                                       {--model= : Related model name} 
                                                       {--data= : A comma-separated list of key-value pairs for notification data (e.g., key1:value1,key2:value2)} 
                                                       {--body= : The body content of the notification} 
                                                       {--subject= : The subject of the notification}
                                                       {--overwrite : is overwriting this file is selected}';

    protected $description = 'Generate a custom notification with optional data, body, and subject.';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle()
    {
        $notificationClass = Str::studly($this->argument('className'));

        // Define the path for the notification file
        $notificationFilePath = app_path(config('code_generator.notification_path', 'Notification') . "/{$notificationClass}.php");

        $this->createDirectoryIfMissing(dirname($notificationFilePath));

        $content = $this->getReplacedContent($notificationClass);

        // Create or overwrite file and get log the status and message
        $this->saveFile(
            $notificationFilePath,
            $content,
            CodeGeneratorFileType::NOTIFICATION
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
     * Generate the final content for the notification file.
     *
     * @param string $notificationClass
     * @return string
     */
    protected function getReplacedContent($notificationClass): string
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables($notificationClass));
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

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/notification.stub';
    }

    /**
     * Get the variables to replace in the stub file.
     *
     * @param string $notificationClass
     * @return array
     */
    protected function getStubVariables($notificationClass): array
    {
        // Parse the --data option into an array
        $dataOption = $this->option('data');
        $parsedData = $this->parseDataOption($dataOption);
        $relatedModel = $this->option('model');

        return [
            'namespace'              => 'App\\' . config('code_generator.notification_path', 'Notification'),
            'class'                  => $notificationClass,
            'model'                  => $relatedModel,
            'relatedModelNamespace'  => config('code_generator.model_path', 'Models') . '\\' . $relatedModel,
            'modelObject'            => '$' . (Str::camel($relatedModel)),
            'subject'                => $this->option('subject'),
            'body'                   => (string) $this->option('body'),
            'data'                   => $parsedData,
        ];
    }

    /**
     * Parse the --data option into an associative array.
     *
     * @param string|null $dataOption
     * @return string
     */
    protected function parseDataOption(?string $dataOption): string
    {
        if (! $dataOption) {
            return '';
        }

        $parsedData = [];

        // Parse each key-value pair from the --data option
        foreach (explode(',', $dataOption) as $pair) {
            if (str_contains($pair, ':')) {
                [$key, $value] = explode(':', $pair);
                $parsedData[] = "'$key' => '$value'";
            }
        }

        return '[' . implode(', ', $parsedData) . ']';
    }
}
