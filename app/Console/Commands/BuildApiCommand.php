<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\BuildApi;
use App\Actions\LoadResources;
use App\Actions\LoadSeeds;
use File;

class BuildApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'e28-api:setup {--refreshOnly=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        # If refreshOnly = true, we don't want to rebuild the entire API, just run fresh migrations/seeds
        $refreshOnly = $this->option('refreshOnly') == "true" ? true : false;
        
        $resourcePath = base_path('../resources.json');
        if (File::exists($resourcePath)) {
            $resourcesJson = File::get($resourcePath);
        } else {
            $this->error("Resource file not found at " . $resourcePath);
            return;
        }

        $loadResources = new LoadResources($resourcesJson);

        if (count($loadResources->errors) > 0) {
            $this->error('API Build failed when loading resources:');
            foreach ($loadResources->errors as $error) {
                $this->error('* '.$error);
            }
            return;
        }
       
        # Gather and validate seed JSON files
        $loadSeeds = new LoadSeeds($loadResources->resources);
        if (count($loadSeeds->errors) > 0) {
            $this->error('API Build failed when loading seeds:');
            foreach ($loadSeeds->errors as $error) {
                $this->error('* '.$error);
            }
            return;
        }

        # Build the API
        $action = new BuildApi($loadResources->resources, $loadSeeds->seeds, $refreshOnly);
        
        # If we're just doing a refresh, no resources were created so skip this step
        if (!$refreshOnly) {
            # Report on resources that were created
            $this->info('Resources created: ');
            foreach ($action->results['resources'] as $resource) {
                $this->info('* ' . $resource);
            }
            $this->info('');
        }
        
        
        # Report on seeds that were run
        $this->info('Seeds run: ');
        foreach ($action->results['seeds'] as $seed => $data) {
            $this->info('* ' . $seed . ' (' . count($data['added']) . ' rows added)');

            if (isset($data['failed'])) {
                $this->error('Failed rows: ');
                dump($data['failed']);
            }
        }
        $this->info('');

        # Report on any errors
        if (isset($action->results['errors'])) {
            $this->error('Errors: ');
            foreach ($action->results['errors'] as $key => $data) {
                $this->error($data);
            }
        }

        return 0;
    }
}