<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Str;
use File;
use Artisan;
use stdClass;
use Hash;

class BuildApi
{
    public $results;
    private $errors;
    
    private $routes;
    private $resourceName;
    private $fields;
    
    /**
     *
     */
    public function __construct($resources, $seeds)
    {
        $this->deleteExistingGeneratedFiles();

        # First pass through resources, we generate files
        foreach ($resources as $resourceName => $fields) {

            # Set the current resource we're working on
            # so the following actions apply to this resource
            $this->setResource($resourceName, $fields);

            $this->createMigration();
            $this->createModel();
            $this->createController();
            $this->createRequest();
            $this->appendRoutes();

            # Track resources for outputting purposes
            $this->results['resources'][] = $this->resourceName;
        }

        # Migrations are generated, so now we run them all
        Artisan::call('migrate:fresh --force');

        # Routes are all built, so we write them
        $this->writeRoutes();

        # Second pass through resources now that tables exist to seed data
        # Add a `user` resource so we can run user seeds
        $resources->user = new stdClass();
        foreach ($resources as $resourceName => $fields) {
            $this->setResource($resourceName, $fields);
            $this->seedData($seeds);
        }
    }

    /**
     *
     */
    private function setResource($resourceName, $fields)
    {
        $this->resourceName = $resourceName;
        $this->resourceNameStudly = Str::studly($resourceName);
        $this->resourceNameLowerPlural = Str::lower(Str::plural($resourceName));
        $this->fields = $fields;
    }

    /**
     *
     */
    private function deleteExistingGeneratedFiles()
    {
        # Remove any existing migrations we have created
        $existingFiles = File::files(base_path('database/migrations'));
        foreach ($existingFiles as $file) {
            if (Str::contains($file->getFilename(), '9999_99_99_999999_')) {
                File::delete($file->getPathname());
            }
        }

        $paths = [
            '/app/Http/Controllers/GeneratedControllers',
            '/app/Models/GeneratedModels',
            '/app/Http/Requests/GeneratedRequests'
        ];

        foreach ($paths as $path) {
            $files = File::allFiles(base_path($path));
            File::delete($files);
        }
    }

    /**
     *
     */
    private function seedData($seeds)
    {
        $results = ['error' => [], 'added' => []];

        # No seeds found for this resource
        if (!property_exists($seeds, $this->resourceName)) {
            return;
        }
        
        if ($this->resourceName != "user") {
            $class = "App\Models\GeneratedModels\\" . $this->resourceNameStudly;
        } else {
            $class = "App\Models\\" . $this->resourceNameStudly;
        }

        foreach ($seeds->{$this->resourceName}->seeds as $data) {
            $resource = new $class;

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                } else {
                    $resource->{$key} = ($key == 'password') ? Hash::make($value) : $value;
                }
            }

            $error = null;

            try {
                $resource->save();
            } catch (\Illuminate\Database\QueryException $e) {
                $error = 'Caught exception: '.  $e->getMessage(). "\n";
                $results['failed'][] = $error;
            }

            if (!$error) {
                $results['added'][] = $resource->toArray();
            }
        }
        
        $this->results['seeds'][$this->resourceName] = $results;
    }

    /**
     *
     */
    private function createModel()
    {
        $template = File::get(base_path('templates/Resource.php'));
        $template = str_replace('Resource', $this->resourceNameStudly, $template);

        if (isset($this->fields->user_id)) {
            $template = str_replace('# properties #', 'public $userRestricted = true;', $template);
        } else {
            $template = str_replace('# properties #', 'public $userRestricted = false;', $template);
        }
        
        $template = str_replace('# table name #', $this->resourceNameLowerPlural, $template);


        File::put(app_path('Models/GeneratedModels/' . $this->resourceNameStudly . '.php'), $template);
    }
    
    /**
     *
     */
    private function appendRoutes()
    {
        $routes = File::get(base_path('templates/routes.txt'));
        $routes = str_replace('Resource', $this->resourceNameStudly, $routes);
        $routes = str_replace('# route #', $this->resourceName, $routes);

        if (isset($this->fields->user_id)) {
            $routes = str_replace('# middleware #', 'middleware("auth:sanctum")->', $routes);
        } else {
            $routes = str_replace('# middleware #', '', $routes);
        }

        $this->routes .= $routes;
    }

    /**
     *
     */
    private function writeRoutes()
    {
        // Update routes after iterating through all the resources
        File::put(base_path('routes/generated-routes.php'), "<?php \n" . $this->routes);
    }

    /**
     *
     */
    private function createController()
    {
        $template = File::get(base_path('templates/ResourceController.php'));
        $template = str_replace('Resource', $this->resourceNameStudly, $template);
        $template = str_replace('resource', $this->resourceName, $template);

        $fieldsDeclaration = 'private $fields = [';
        foreach ($this->fields as $field => $details) {
            $fieldsDeclaration .= '"'.$field.'" => [';
            foreach ($details->validators as $validator) {
                $fieldsDeclaration .= '"'.$validator.'",';
            }

            $fieldsDeclaration .= '], ';
        }
        $fieldsDeclaration .= '];';

        $template = str_replace('private $fields = [];', $fieldsDeclaration, $template);

        File::put(app_path('Http/Controllers/GeneratedControllers/'.$this->resourceNameStudly.'Controller.php'), $template);
    }

    /**
     *
     */
    private function createRequest()
    {
        $template = File::get(base_path('templates/ResourceRequest.php'));
       
        $rules = "return [\n";
        foreach ($this->fields as $fieldName => $details) {
            if ($fieldName == 'user_id') {
                continue;
            }
            
            $rules .= "'$fieldName' => [";

            foreach ($details->validators as $validator) {
                if ($validator == 'unique') {
                    # Process a validator such as `unique`
                    $rules .= '"unique:' . $this->resourceNameLowerPlural . ',' . $fieldName . ',".$this->route("id"),';
                } else {
                    $rules .= "'$validator', ";
                }
            }
            $rules .= "],\n";
        }
        $rules .= "];";
        
        $template = str_replace('# rules #', $rules, $template);
        $template = str_replace('Resource', $this->resourceNameStudly, $template);
        $template = str_replace('resource', $this->resourceName, $template);

        File::put(app_path('Http/Requests/GeneratedRequests/' . $this->resourceNameStudly . 'Request.php'), $template);
    }

    /**
     *
     */
    private function deleteGeneratedMigrationFiles()
    {
        # Remove any existing migrations we have created
        $existingFiles = File::files(base_path('database/migrations'));
        foreach ($existingFiles as $file) {
            if (Str::contains($file->getFilename(), '9999_99_99_999999_')) {
                File::delete($file->getPathname());
            }
        }
    }

    /**
     *
     */
    private function createMigration()
    {
        # Create migration file for this resource
        $template = File::get(base_path('templates/migration.php'));
        $template = str_replace('Resource', $this->resourceNameStudly, $template);
        $template = str_replace('# table name #', $this->resourceNameLowerPlural, $template);


        $schema = '';

        foreach ($this->fields as $field => $fieldDetails) {
            $schema .= "\$table->" . $fieldDetails->type . "('" . $field ."')->nullable(); \n";
        }

        $template = str_replace('# schema #', $schema, $template);
        File::put(base_path('database/migrations/9999_99_99_999999_create_'.$this->resourceName.'_table.php'), $template);
    }

    /**
     *
     */
    private function runMigration($migrationFile)
    {
        $cmd = 'migrate:refresh --force --path=database/migrations/'.$migrationFile;
        Artisan::call($cmd);
    }
}