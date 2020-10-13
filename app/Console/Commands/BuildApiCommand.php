<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\BuildApi;
use App\Actions\LoadResources;

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
        $resources = new LoadResources();
        $action = new BuildApi($resources->resources);

        $this->info('Resources created: ');
        foreach ($action->results['resources'] as $resource) {
            $this->info('* ' . $resource);
        }
        $this->info('');

        
        
        $this->info('Seeds run: ');
        foreach ($action->results['seeds'] as $seed => $data) {
            $this->info('* ' . $seed . ' (' . count($data['added']) . ' rows added)');

            if (isset($data['failed'])) {
                $this->error('Failed rows: ');
                dump($data['failed']);
            }
        }
        $this->info('');


        if (isset($action->results['errors'])) {
            $this->info('Errors: ');
            foreach ($action->results['errors'] as $key => $data) {
                $this->error($data);
            }
        }

        
        return 0;
    }
}