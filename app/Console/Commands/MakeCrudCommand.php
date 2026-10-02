<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

#[Signature('make:crud {name}')]
#[Description('Generate complete CRUD files (Model, Request, Controller, Views) and register route')]
class MakeCrudCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        $modelName            = Str::studly($name);
        $controllerName       = "{$modelName}Controller";
        $requestName          = "{$modelName}Request";
        $tableName            = Str::snake($name);
        $viewFolder           = strtolower(Str::kebab($name));
        $routePath            = strtolower(Str::kebab($name));
        $modelVariable        = Str::camel(Str::singular($name));
        $alpineComponent      = Str::camel($name) . 'Page';
        $modalAlpineComponent = Str::camel(Str::singular($name)) . 'Modal';
        $modalId              = Str::camel(Str::singular($name)) . 'Modal';
        $title                = Str::title(str_replace('-', ' ', $viewFolder));

        // Read migration to extract table headers, columns, fillable, and validation rules
        $migrationData   = $this->parseMigrationColumns($tableName);
        $tableHeaders    = $migrationData['headers'];
        $tableColumns    = $migrationData['columns'];
        $fillable        = $migrationData['fillable'];
        $validationRules = $migrationData['validationRules'];

        // Mapping seluruh file yang akan di-generate
        $files = [
            // 1. Model
            app_path("Models/{$modelName}.php") => [
                'stub' => base_path('stubs/crud/model.stub'),
                'replace' => [
                    '{{ class }}'     => $modelName,
                    '{{ tableName }}' => $tableName,
                    '{{ fillable }}'  => $fillable,
                ],
            ],

            // 2. Request
            app_path("Http/Requests/{$requestName}.php") => [
                'stub' => base_path('stubs/crud/request.stub'),
                'replace' => [
                    '{{ class }}'           => $requestName,
                    '{{ tableName }}'       => $tableName,
                    '{{ validationRules }}' => $validationRules,
                ],
            ],

            // 3. Controller
            app_path("Http/Controllers/{$controllerName}.php") => [
                'stub' => base_path('stubs/crud/controller.stub'),
                'replace' => [
                    '{{ class }}'         => $controllerName,
                    '{{ model }}'         => $modelName,
                    '{{ request }}'       => $requestName,
                    '{{ viewFolder }}'    => $viewFolder,
                    '{{ modelVariable }}' => $modelVariable,
                ],
            ],

            // 4. View Index
            resource_path("views/{$viewFolder}/index.blade.php") => [
                'stub' => base_path('stubs/crud/index.blade.stub'),
                'replace' => [
                    '{{ viewFolder }}'      => $viewFolder,
                    '{{ model }}'           => $modelName,
                    '{{ alpineComponent }}' => $alpineComponent,
                    '{{ title }}'           => $title,
                    '{{ tableHeaders }}'    => $tableHeaders,
                    '{{ tableColumns }}'    => $tableColumns,
                ],
            ],

            // 5. View Form Partial
            resource_path("views/{$viewFolder}/partials/form.blade.php") => [
                'stub' => base_path('stubs/crud/form.blade.stub'),
                'replace' => [
                    '{{ viewFolder }}'           => $viewFolder,
                    '{{ title }}'                => $title,
                    '{{ modalId }}'              => $modalId,
                    '{{ modalAlpineComponent }}' => $modalAlpineComponent,
                ],
            ],
        ];

        // Generate file-file CRUD
        foreach ($files as $targetPath => $config) {
            $this->generateFile($config['stub'], $targetPath, $config['replace']);
        }

        // Otomatis tambahkan Route
        $this->addRoute($routePath, $controllerName);

        $this->info("Proses pembuatan CRUD untuk {$modelName} selesai!");
    }

    /**
     * Parse migration file based on table name to extract headers, Datatable columns, fillable attributes, and validation rules.
     */
    private function parseMigrationColumns(string $tableName): array
    {
        $migrationFiles = File::files(database_path('migrations'));
        $targetFile = null;

        foreach ($migrationFiles as $file) {
            if (Str::contains($file->getFilename(), "create_{$tableName}_table")) {
                $targetFile = $file->getPathname();
                break;
            }
        }

        if (!$targetFile) {
            return [
                'headers'         => '<th class="py-3 text-secondary">Name</th>',
                'columns'         => "{\n                            data: 'name',\n                            name: 'name',\n                            orderable: true,\n                            render: data => `<span class=\"fw-semibold text-dark\">\${data ?? '-'}</span>`\n                        }",
                'fillable'        => "'name',",
                'validationRules' => "'name' => 'required|string|max:255',",
            ];
        }

        $content = File::get($targetFile);
        $ignoredColumns = ['id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'remember_token'];

        // Split baris untuk analisa per kolom (tipe data & modifier seperti unique, nullable)
        $lines = explode("\n", $content);

        $headers         = [];
        $columns         = [];
        $fillable        = [];
        $validationRules = [];

        foreach ($lines as $line) {
            if (preg_match('/\$table->(\w+)\([\'"](\w+)[\'"](?:,\s*(\d+))?\)/', $line, $match)) {
                $type   = $match[1];
                $column = $match[2];
                $length = $match[3] ?? null;

                if (in_array($column, $ignoredColumns)) {
                    continue;
                }

                $fillable[] = "'{$column}'";

                // 1. Build Table Headers
                $label = Str::title(str_replace('_', ' ', $column));
                $headers[] = "<th class=\"py-3 text-secondary\">{$label}</th>";

                // 2. Build Datatable JS Columns
                if ($type === 'boolean') {
                    $columns[] = "{\n                            data: '{$column}',\n                            name: '{$column}',\n                            orderable: true,\n                            render: data => data ? `<span class=\"badge bg-success-subtle text-success border px-2 py-1\">Active</span>` : `<span class=\"badge bg-danger-subtle text-danger border px-2 py-1\">Inactive</span>`\n                        }";
                } else {
                    $columns[] = "{\n                            data: '{$column}',\n                            name: '{$column}',\n                            orderable: true,\n                            render: data => `<span class=\"fw-semibold text-dark\">\${data ?? '-'}</span>`\n                        }";
                }

                // 3. Build Validation Rules
                $rules = [];
                $isNullable = Str::contains($line, 'nullable()');
                $isUnique   = Str::contains($line, 'unique()');

                $rules[] = $isNullable ? 'nullable' : 'required';

                switch ($type) {
                    case 'string':
                        $max = $length ?? '255';
                        $rules[] = 'string';
                        $rules[] = "max:{$max}";
                        break;
                    case 'text':
                        $rules[] = 'string';
                        break;
                    case 'integer':
                    case 'bigInteger':
                    case 'smallInteger':
                        $rules[] = 'integer';
                        break;
                    case 'boolean':
                        $rules[] = 'boolean';
                        break;
                    case 'date':
                    case 'dateTime':
                    case 'timestamp':
                        $rules[] = 'date';
                        break;
                    case 'decimal':
                    case 'float':
                    case 'double':
                        $rules[] = 'numeric';
                        break;
                    default:
                        $rules[] = 'string';
                        break;
                }

                if ($isUnique) {
                    $rules[] = "unique:{$tableName},{$column},' . \$id";
                    $validationRules[] = "'{$column}' => '" . implode('\vert{}', array_slice($rules, 0, -1)) . "|' . \"{$rules[count($rules) - 1]}\"";
                } else {
                    $ruleString = implode('|', $rules);
                    $validationRules[] = "'{$column}' => '{$ruleString}'";
                }
            }
        }

        if (empty($headers)) {
            return [
                'headers'         => '<th class="py-3 text-secondary">Name</th>',
                'columns'         => "{\n                            data: 'name',\n                            name: 'name',\n                            orderable: true,\n                            render: data => `<span class=\"fw-semibold text-dark\">\${data ?? '-'}</span>`\n                        }",
                'fillable'        => "'name',",
                'validationRules' => "'name' => 'required|string|max:255',",
            ];
        }

        return [
            'headers'         => implode("\n                ", $headers),
            'columns'         => implode(",\n                        ", $columns),
            'fillable'        => implode(",\n    ", $fillable) . ',',
            'validationRules' => implode(",\n            ", $validationRules) . ',',
        ];
    }

    /**
     * Helper untuk membuat file dari stub.
     */
    private function generateFile(string $stubPath, string $targetPath, array $replacements): void
    {
        if (File::exists($targetPath)) {
            $relativePath = str_replace(base_path() . '/', '', $targetPath);
            $this->components->warn("Skipped: File {$relativePath} sudah ada.");
            return;
        }

        if (!File::exists($stubPath)) {
            $this->error("File stub tidak ditemukan: {$stubPath}");
            return;
        }

        $content = File::get($stubPath);

        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        File::ensureDirectoryExists(dirname($targetPath));
        File::put($targetPath, $content);

        $this->components->info("Created: " . str_replace(base_path() . '/', '', $targetPath));
    }

    /**
     * Helper untuk menyisipkan Route::resource ke routes/web.php
     */
    private function addRoute(string $routePath, string $controllerName): void
    {
        $webRoutePath = base_path('routes/web.php');

        if (!File::exists($webRoutePath)) {
            return;
        }

        $routeContent = File::get($webRoutePath);
        $routeDefinition = "Route::resource('{$routePath}', {$controllerName}::class);";
        $useStatement = "use App\Http\Controllers\\{$controllerName};";

        if (Str::contains($routeContent, $routeDefinition)) {
            $this->components->warn("Skipped: Route untuk '{$routePath}' sudah ada di web.php.");
            return;
        }

        if (!Str::contains($routeContent, $useStatement)) {
            $routeContent = preg_replace(
                '/<\?php\s*/',
                "<?php\n\n{$useStatement}\n",
                $routeContent,
                1
            );
        }

        $groupPattern = "/(Route::middleware\(['\"]auth['\"]\)->group\(function\s*\(\)\s*\{)/";

        if (preg_replace_callback($groupPattern, function () {}, $routeContent) !== null && preg_match($groupPattern, $routeContent)) {
            $routeContent = preg_replace(
                $groupPattern,
                "$1\n    {$routeDefinition}",
                $routeContent,
                1
            );

            File::put($webRoutePath, $routeContent);
            $this->components->info("Updated: Route::resource('{$routePath}', {$controllerName}::class) berhasil ditambahkan ke routes/web.php");
        } else {
            $this->components->warn("Gagal menyisipkan route: Grup middleware('auth') tidak ditemukan di routes/web.php");
        }
    }
}
