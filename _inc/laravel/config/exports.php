<?php

return [
	/*
    |--------------------------------------------------------------------------
    | Python Binary Path
    |--------------------------------------------------------------------------
    |
    | The path to the Python binary used for executing export scripts.
    | Can be overridden by the PYTHON_BINARY environment variable.
    |
    */
	'python_binary' => env('PYTHON_BINARY', 'python3'),

	/*
    |--------------------------------------------------------------------------
    | Python Scripts Directory
    |--------------------------------------------------------------------------
    |
    | The directory containing Python export scripts.
    | Relative to the app/Exports directory.
    |
    */
	'python_scripts_dir' => env('PYTHON_SCRIPTS_DIR', 'py'),

	/*
    |--------------------------------------------------------------------------
    | Export Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for a Python export to complete.
    |
    */
	'timeout' => env('EXPORT_TIMEOUT', 60),

	/*
    |--------------------------------------------------------------------------
    | Export Storage Path
    |--------------------------------------------------------------------------
    |
    | The directory where exported files will be saved.
    |
    */
	'storage_path' => env('EXPORT_STORAGE_PATH', storage_path('exports')),

	/*
    |--------------------------------------------------------------------------
    | Default Export Format
    |--------------------------------------------------------------------------
    |
    | The default format for exports when not specified.
    | Supported: 'xlsx', 'pdf', 'docx'
    |
    */
	'default_format' => env('EXPORT_DEFAULT_FORMAT', 'xlsx'),

	/*
    |--------------------------------------------------------------------------
    | Enable Python Exports
    |--------------------------------------------------------------------------
    |
    | Toggle to enable/disable Python-based exports.
    | When disabled, falls back to PHP-based exports.
    |
    */
	'python_enabled' => env('PYTHON_EXPORTS_ENABLED', true),

	/*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, additional debugging information is logged.
    |
    */
	'debug' => env('EXPORT_DEBUG', false),
];
