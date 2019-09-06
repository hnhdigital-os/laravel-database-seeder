<?php

namespace HnhDigital\LaravelDatabaseSeeder;

use DB;
use File;
use Config;
use Illuminate\Console\Command;

class SeedFromSqlCommand extends Command
{
    use SharedTrait;

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

        // Force default database connection.
        $this->connection = ! empty($this->option('connection'))
            ? $this->option('connection')
            : Config::get('database.default');

        // Get the files.
        $files = $this->getFiles($source_path);

        if (count($files) === 0) {
            $this->error('No files found.');

            return;
        }

        // Setup progress bar.
        $this->progress_bar = $this->output->createProgressBar(count($files));

        $this->info('This process will replace any existing data.');

        if (! $this->option('force')) {
            $force_import = $this->confirm('Are you sure? [y|N]');
        }

        if (! $force_import) {
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

        // The order of seeding is present in the file name.
        if (is_numeric($table_name_array[0])) {
            $table_name = $table_name_array[1];
        }

        $table_name_array = explode('.', $table_name, 2);

        $connection = $this->connection;

        // Connection is present in the file name.
        if (count($table_name_array) === 2) {
            $connection = $table_name_array[0];
            $table_name = $table_name_array[1];
        }

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

        $this->progress_bar->advance();
    }
}
