<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;


class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

     protected $container = [];

     
    protected $signature = 'module:make {name}';
    protected $description = 'Create starter CRUD module';
    
    public function __construct()
     {
         parent::__construct();
     }
 
    /**
     * 
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->container['name'] = ucwords($this->ask('Please enter the name of the Module'));

        if (strlen($this->container['name']) == 0) {
            $this->error("\nModule name cannot be empty.");
        } else {
            $this->container['model'] = ucwords(Str::singular($this->container['name']));

            if ($this->confirm("Is '{$this->container['model']}' the correct name for the Model?", true)) {
                $this->comment('You have provided the following information:');
                $this->comment('Name:  ' . $this->container['name']);
                $this->comment('Model: ' . $this->container['model']);

                if ($this->confirm('Do you wish to continue?', true)) {
                    $this->comment('Success!');
                    $this->generate();
                } else {
                    return false;
                }

                return true;
            } else {
                $this->handle();
            }
        }

        $this->info('Starter '.$this->container['name'].' module installed successfully.');
    
    }

    protected function generate()
    {
       
        $module     = $this->container['name'];
        $model      = $this->container['model'];
        $Module     = $module;
        $module     = strtolower($module);
        $Model      = $model;
        $targetPath = base_path('Modules/'.$Module);
        // dd($targetPath);

        // Copy base module template
        $this->copy(base_path('stubs/base-module'), $targetPath);
      
        // Replace placeholders in files
        $this->replaceInFile($targetPath.'/config/config.php');
        $this->replaceInFile($targetPath.'/database/factories/ModelFactory.php');
        $this->replaceInFile($targetPath.'/database/migrations/create_module_table.php');
        $this->replaceInFile($targetPath.'/database/seeders/ModelDatabaseSeeder.php');
        $this->replaceInFile($targetPath.'/app/Http/Controllers/ModuleController.php');
        $this->replaceInFile($targetPath.'/app/Models/Model.php');
        $this->replaceInFile($targetPath.'/app/Providers/ModuleServiceProvider.php');
        $this->replaceInFile($targetPath.'/app/Providers/RouteServiceProvider.php');
        $this->replaceInFile($targetPath.'/resources/views/create.blade.php');
        $this->replaceInFile($targetPath.'/resources/views/edit.blade.php');
        $this->replaceInFile($targetPath.'/resources/views/index.blade.php');
        $this->replaceInFile($targetPath.'/routes/api.php');
        $this->replaceInFile($targetPath.'/routes/web.php');
        $this->replaceInFile($targetPath.'/tests/Feature/ModuleTest.php');
        $this->replaceInFile($targetPath.'/composer.json');
        $this->replaceInFile($targetPath.'/module.json');
        $this->replaceInFile($targetPath.'/vite.config.js');

        // Rename files with placeholders
        $this->rename($targetPath.'/database/factories/ModelFactory.php', $targetPath.'/database/factories/'.$Model.'Factory.php');
        $this->rename($targetPath.'/database/migrations/create_module_table.php', $targetPath.'/database/migrations/create_'.$module.'_table.php', 'migration');
        $this->rename($targetPath.'/database/seeders/ModelDatabaseSeeder.php', $targetPath.'/database/seeders/'.$Module.'DatabaseSeeder.php');
        $this->rename($targetPath.'/app/Http/Controllers/ModuleController.php', $targetPath.'/app/Http/Controllers/'.$Module.'Controller.php');
        $this->rename($targetPath.'/app/Models/Model.php', $targetPath.'/Models/'.$Model.'.php');
        $this->rename($targetPath.'/app/Providers/ModuleServiceProvider.php', $targetPath.'/app/Providers/'.$Module.'ServiceProvider.php');
        $this->rename($targetPath.'/tests/Feature/ModuleTest.php', $targetPath.'/tests/Feature/'.$Module.'Test.php');
    }

    protected function rename($path, $target, $type = null)
    {
        $filesystem = new SymfonyFilesystem;
        if ($filesystem->exists($path)) {
            if ($type == 'migration') {
                $timestamp = date('Y_m_d_his_');
                $target = str_replace("create", $timestamp."create", $target);
                $filesystem->rename($path, $target, true);
                $this->replaceInFile($target);
            } else {
                $filesystem->rename($path, $target, true);
            }
        }
    }

    protected function copy($path, $target)
    {
        $filesystem = new SymfonyFilesystem;
        if ($filesystem->exists($path)) {
            $filesystem->mirror($path, $target);
        }
    }

    protected function replaceInFile($path)
    {
    
        $name = $this->container['name'];
        $model = $this->container['model'];
        $types = [
            '{module_}' => null,
            '{module-}' => null,
            '{Module}' => $name,
            '{module}' => strtolower($name),
            '{Model}' => $model,
            '{model}' => strtolower($model)
        ];

        foreach($types as $key => $value) {
            if (file_exists($path)) {
                if ($key == "module_") {
                    $parts = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);
                    $parts = array_map('strtolower', $parts);
                    $value = implode('_', $parts);
                }

                if ($key == 'module-') {
                    $parts = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);
                $parts = array_map('strtolower', $parts);
                $value = implode('-', $parts);
            }

            file_put_contents($path, str_replace($key, $value, file_get_contents($path)));
        }
    }
}
}
