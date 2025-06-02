<?php

namespace Sevenspan\CodeGenerator\Models;

use Illuminate\Database\Eloquent\Model;
use Sevenspan\CodeGenerator\Enums\CodeGeneratorFileLogStatus;

class CodeGeneratorFileLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'code_generator_file_logs';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'file_type',    // Type of the file (e.g., Controller, Model, etc.)
        'file_path',    // Path where the file is generated
        'status',       // Status of the file generation (e.g., success, error)
        'message',      // Optional message or description
        'is_overwrite', // Indicates if the file was overwritten
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => CodeGeneratorFileLogStatus::class, // Cast status to enum
        'is_overwrite' => 'boolean',                   // Ensure is_overwrite is boolean
    ];
}
