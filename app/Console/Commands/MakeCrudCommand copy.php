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
        $name = $this->argument('name'); // Contoh: Departments

        $modelName           = Str::studly($name);                          // Departments
        $controllerName      = "{$modelName}Controller";                   // DepartmentsController
        $requestName         = "{$modelName}Request";                      // DepartmentsRequest
        $tableName           = Str::snake($name);                          // departments
        $viewFolder          = strtolower(Str::kebab($name));              // departments
        $routePath           = strtolower(Str::kebab($name));              // departments
        $modelVariable       = Str::camel(Str::singular($name));            // department
        $alpineComponent     = Str::camel($name) . 'Page';                  // departmentsPage
        $modalAlpineComponent = Str::camel(Str::singular($name)) . 'Modal'; // departmentModal
        $modalId             = Str::camel(Str::singular($name)) . 'Modal';  // departmentModal
        $title               = Str::title(str_replace('-', ' ', $viewFolder)); // Departments

        // Mapping seluruh file yang akan di-generate
        $files = [
            // 1. Model
            app_path("Models/{$modelName}.php") => [
                'stub' => base_path('stubs/crud/model.stub'),
                'replace' => [
                    '{{ class }}'     => $modelName,
                    '{{ tableName }}' => $tableName,
                ],
            ],

            // 2. Request
            app_path("Http/Requests/{$requestName}.php") => [
                'stub' => base_path('stubs/crud/request.stub'),
                'replace' => [
                    '{{ class }}'     => $requestName,
                    '{{ tableName }}' => $tableName,
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
