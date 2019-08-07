<?php

namespace HnhDigital\LaravelDatabaseSeeder;

use DB;
use File;
use Illuminate\Console\Command;
use League\Csv\Reader;

class SeedFromCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-from-csv {path}
                            {--force : Force import without confirmaion}
                            {--connection-from-dirname}
                            {--connection=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed a table from a given CSV data in a given file or folder path.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get arguments and options.
        $source_path = $this->argument('path');
        $connection = !empty($this->option('connection'))
            ? $this->option('connection')
            : config('database.default');


        // Get the files.
        $files = $this->getFiles();

        if (count($files) === 0) {
            $this->error('No files found.');

            return;
        }

        // Setup progress bar.
        $this->progress_bar = $this->output->createProgressBar(count($files));

        $this->info('This process will replace any existing data.');

        if (!$this->option('force')) {
            $force_import = $this->confirm('Are you sure? [y|N]');
        }

        // Process each file.
        foreach ($files as $path) {
            $this->process($path);
        }

        $this->line('');
        $this->info('Done.');
    }

    /**
     * Get files.
     *
     * @return array
     */
    private function getFiles()
    {
        try {
            $type = File::type($source_path);
        } catch (\Exception $exception) {
            $this->error("{$source_path} does not exist.");

            return [];
        }

        if ($type === 'dir') {
            if (substr($source_path, strlen($source_path) - 1) === '/') {
                $source_path = substr($source_path, 0, -1);
            }

            $files = File::files($source_path);
        } elseif ($type !== 'dir') { {
            $files = [$source_path];
        }

        $no_order = count($files);
        $files_order = [];

        // Reorder files correctly.
        foreach ($files as $path) {
            $table_name = File::name($path);
            $table_name_array = explode('_', $tableName, 2);

            if (is_numeric($table_name_array[0])) {
                $table_order = $table_name_array[0];
                $files_order[$table_order] = $path;

                continue;
            }

            $files_order[$no_order] = $path;
            $no_order++;
        }

        ksort($files_order);

        return $files_order;
    }

    /**
     * Process a given file.
     *
     * @return void
     */
    private function process($path)
    {
        $table_name = File::name($path);
        $table_name_array = explode('_', $table_name, 2);

        if (is_numeric($table_name_array[0])) {
            $table_name = $table_name_array[1];
        }

        if (!$force_import) {
            return;
        }

        try {

            $this->line('');
            $this->line('');
            $this->info('Processing '.$table_name);
            $this->line('');

            $csv = Reader::createFromPath($path);
            $csv->setHeaderOffset(0);

            DB::connection($connection)
                ->statement('SET FOREIGN_KEY_CHECKS=0;');

            DB::connection($connection)
                ->table($table_name)
                ->truncate();

            DB::connection($connection)
                ->statement('SET FOREIGN_KEY_CHECKS=1;');

            foreach ($csv as $record)  {
                foreach ($record as $key => &$value) {
                    if ($value === 'NULL') {
                        $value = null;
                    }
                }

                DB::connection($connection)
                    ->table($table_name)
                    ->insert($record);
            }

        } catch (\Exception $exception) {
            $this->line('');
            $this->error('SQL error occurred on importing '.$table_name);
            $this->line($exception->getMessage());
            $this->line('');
        }

        $this->progress_bar->advance();
    }
}
