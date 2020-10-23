<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\BuildApi;
use App\Actions\LoadResources;
use App\Actions\LoadSeeds;

class BuildApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'e28-api:build';

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
        # Gather and validate resource JSON file
        $loadResources = new LoadResources();
       
        # Gather and validate seed JSON files
        $loadSeeds = new LoadSeeds($loadResources->resources);
        
        # Merge and display any errors
        $errors = array_merge($loadResources->errors, $loadSeeds->errors);
        if (count($errors) > 0) {
            $this->error('API Build failed:');
            foreach ($errors as $error) {
                $this->error('* '.$error);
            }
            return;
        }

        # Build the API
        $action = new BuildApi($loadResources->resources, $loadSeeds->seeds);

        # Report on resources that were created
        $this->info('Resources created: ');
        foreach ($action->results['resources'] as $resource) {
            $this->info('* ' . $resource);
        }
        $this->info('');
        
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