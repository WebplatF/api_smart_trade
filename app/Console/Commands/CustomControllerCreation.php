<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;


if (!function_exists('app_path')) {
    function app_path($path = '')
    {
        return app()->basePath('app' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}

class CustomControllerCreation extends Command
{
    protected $signature = 'make:custom-controller-service {name}';
    protected $description = 'Create a controller and its corresponding service file automatically';

    public function handle()
    {
        $name = $this->argument('name');
        $controllerPath = app_path("Http/Controllers/{$name}Controller.php");
        $servicePath = app_path("Services/{$name}Service.php");

        if (!File::exists(app_path('Services'))) {
            File::makeDirectory(app_path('Services'));
        }

        $serviceContent = <<<PHP
        <?php
        namespace App\Services;
        class {$name}Service
        {
        }
        PHP;

        File::put($servicePath, $serviceContent);
        $this->info("Service created: {$servicePath}");

        $controllerContent = <<<PHP
        <?php
        namespace App\Http\Controllers;
        use App\Services\\{$name}Service;
        use Illuminate\Http\Request;
        use Illuminate\Support\Facades\Validator;
        use App\Helper\ResponseHelper;
        use Throwable;
        class {$name}Controller extends Controller
        {
            protected {$name}Service \${$this->camelCase($name)}Service;

        public function __construct({$name}Service \${$this->camelCase($name)}Service)
        {
            \$this->\${$this->camelCase($name)}Service = \${$this->camelCase($name)}Service;
        }
        }
        PHP;
        File::put($controllerPath, $controllerContent);
        $this->info("Controller created: {$controllerPath}");
    }

    protected function camelCase($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string))));
    }
}
