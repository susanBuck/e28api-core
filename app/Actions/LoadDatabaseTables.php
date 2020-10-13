<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;

class LoadDatabaseTables
{
    public $results;

    public function __construct()
    {
        $tables = DB::table('sqlite_master')->select('name')->where('type', 'table')->orderBy('name')->get();

        $exclude = ['personal_access_tokens', 'migrations', 'failed_jobs', 'password_resets', 'sqlite_sequence', 'sessions'];

        foreach ($tables as $table) {
            if (!in_array($table->name, $exclude)) {
                $data = DB::table($table->name)->get();

                $this->results[$table->name] = $data->toArray();
            }
        }
    }
}