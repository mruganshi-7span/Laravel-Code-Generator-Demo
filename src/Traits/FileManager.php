<?php

namespace Sevenspan\CodeGenerator\Traits;

use Illuminate\Support\Facades\File;
use Sevenspan\CodeGenerator\Enums\CodeGeneratorFileType;
use Sevenspan\CodeGenerator\Models\CodeGeneratorFileLog;
use Sevenspan\CodeGenerator\Enums\CodeGeneratorFileLogStatus;

/**
 * Trait FileManager
 * 
 * This trait provides file management functionality for code generator commands,
 * including creating, overwriting, and logging file operations.
 */
trait FileManager
{
    /**
     * Create or overwrite a file based on overwrite option.
     *
     * @param  string  $filePath  
     * @param  string  $contents 
     * @param  CodeGeneratorFileType  $fileType  The type of file being created/modified
     * @return void
     */
    public function saveFile(string $filePath, string $contents, CodeGeneratorFileType $fileType): void
    {
        // Check if the file already exists
        $fileExists = File::exists($filePath);
        $shouldOverwrite = $this->option('overwrite');
        $isOverwrite = false;

        if ($fileExists) {
            if ($shouldOverwrite) {
                // Overwrite existing file if overwrite option is provided
                File::put($filePath, $contents);
                $logMessage = "{$fileType->value} file was overwritten successfully";
                $logStatus = CodeGeneratorFileLogStatus::SUCCESS;
                $isOverwrite = true;
                $this->info($logMessage);
            } else {
                // Skip overwriting if overwrite option is not provided
                $logMessage = "{$fileType->value} file already exists";
                $logStatus = CodeGeneratorFileLogStatus::ERROR;
                $this->warn($logMessage);
            }
        } else {
            // Create new file if it doesn't exist
            File::put($filePath, $contents);
            $logMessage = "{$fileType->value} file has been created successfully";
            $logStatus = CodeGeneratorFileLogStatus::SUCCESS;
            $this->info($logMessage);
        }

        CodeGeneratorFileLog::create([
            'file_type' => $fileType->value,
            'file_path' => $filePath,
            'status' => $logStatus,
            'message' => $logMessage,
            'is_overwrite' => $isOverwrite,
        ]);
    }
}
