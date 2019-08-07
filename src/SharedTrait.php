<?php

namespace HnhDigital\LaravelDatabaseSeeder;

use File;

trait SharedTrait
{
    /**
     * Get files.
     *
     * @return array
     */
    private function getFiles($source_path)
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
        } elseif ($type !== 'dir') {
            $files = [$source_path];
        }

        $no_order = count($files);
        $files_order = [];

        // Reorder files correctly.
        foreach ($files as $path) {
            $table_name = File::name($path);
            $table_name_array = explode('_', $table_name, 2);

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
}
