<?php

namespace HnhDigital\LaravelDatabaseSeeder;

use DB;
use File;
use Illuminate\Console\Command;
use League\Csv\Reader;

class SeedFromCsvCommand extends Command
{
    use SharedTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-from-csv {path}
                            {--force : Force import without confirmaion}
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

        // Force default database connection.
        $this->connection = !empty($this->option('connection'))
            ? $this->option('connection')
            : config('database.default');

        // Get the files.
        $files = $this->getFiles($source_path);

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

        if (!$force_import) {
            return;
        }

        // Process each file.
        foreach ($files as $path) {
            $this->process($path);
        }

        $this->line('');
        $this->info('Done.');
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

        $connection = $this->connection;

        $table_name_array = explode('.', $table_name, 2);

        // Connection is present in the file name.
        if (count($table_name_array) === 2) {
            $connection = $table_name_array[0];
            $table_name = $table_name_array[1];
        }

        try {
            $this->line('');
            $this->line("Processing <info>{$table_name}</info>");
            $this->line('');

            $this->prepareTable($connection, $table_name);

            $csv = Reader::createFromPath($path);
            $csv->setHeaderOffset(0);

            foreach ($csv as $record) {
                $this->processRow($connection, $table_name, $record);
            }
        } catch (\Exception $exception) {
            $this->line('');
            $this->error('SQL error occurred on importing '.$table_name);
            $this->line($exception->getMessage());
            $this->line('');
        }

        $this->progress_bar->advance();
    }

    /**
     * Prepare table.
     *
     * @param string $connection
     * @param string $table_name
     *
     * @return void
     */
    private function prepareTable($connection, $table_name)
    {
        DB::connection($connection)
            ->statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::connection($connection)
            ->table($table_name)
            ->truncate();

        DB::connection($connection)
            ->statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Process row in CSV file.
     *
     * @return void
     */
    private function processRow($connection, $table_name, $record)
    {
        foreach ($record as &$value) {
            if ($value === 'NULL') {
                $value = null;
            }
        }

        DB::connection($connection)
            ->table($table_name)
            ->insert($record);
    }
}
