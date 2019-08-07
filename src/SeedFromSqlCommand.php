<?php

namespace HnhDigital\LaravelDatabaseSeeder;

use Config;
use DB;
use File;
use Illuminate\Console\Command;

class SeedFromRawCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-from-sql {path}
                            {--force : Force import without confirmaion}
                            {--connection=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed database from a SQL found in a given file or folder path.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $source_path = $this->argument('path');
        $connection = !empty($this->option('connection'))
            ? $this->option('connection')
            : Config::get('database.default');

        try {
            $type = File::type($source_path);
        } catch (\Exception $exception) {
            $this->error("{$source_path} does not exist.");

            return 1;
        }

        if ($type === 'dir') {
            if (substr($source_path, strlen($source_path) - 1) === '/') {
                $source_path = substr($source_path, 0, -1);
            }

            $files = File::files($source_path);
        } else {
            $files = [$source_path];
        }

        $no_order = count($files);
        $progress_bar = $this->output->createProgressBar($no_order);
        $files_order = [];

        foreach ($files as $path) {
            $table_name = File::name($path);
            $table_name_array = explode('_', $table_name, 2);

            if (is_numeric($table_name_array[0])) {
                $table_order = $table_name_array[0];
                $files_order[$table_order] = $path;
            } else {
                $files_order[$no_order] = $path;
                $no_order++;
            }
        }

        ksort($files_order);

        $this->info(' This process will replace any existing data.');

        if (!$this->option('force')) {
            $force_import = $this->confirm('Are you sure? [y|N]');
        }

        foreach ($files_order as $path) {
            $table_name = File::name($path);
            $table_name_array = explode('_', $table_name, 2);

            if (is_numeric($table_name_array[0])) {
                $table_name = $table_name_array[1];
            }

            if ($force_import) {
                try {
                    DB::connection($connection)->unprepared(File::get($path));

                    $this->line('');
                    $this->line('');
                    $this->info("Processing {$table_name}");
                    $this->line('');
                } catch (\Exception $exception) {
                    $this->line('');
                    $this->error("SQL error occurred on importing {$table_name}");
                    $this->line($exception->getMessage());
                    $this->line('');
                }

                $progress_bar->advance();
            }
        }

        $this->line('');
        $this->line('');
        $this->info('Done.');
    }
}
