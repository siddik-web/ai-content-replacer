<?php

/**
 * Utility class with helper functions for directory and file management.
 */
class Util
{
    /**
     * Retrieves all folder names within a specified directory, excluding specific folders.
     *
     * This method scans the provided directory and returns an array of folder names, excluding
     * any folders named 'overrides' and 'en-GB'.
     *
     * @param string $directory The path to the directory to scan for folders.
     *
     * @return array An array containing the names of folders found within the directory,
     *               excluding 'overrides' and 'en-GB'.
     */
    public static function getAllFolders(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $contents = scandir($directory);
        $folders = [];

        foreach ($contents as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($fullPath) && $item !== 'overrides' && $item !== 'en-GB') {
                $folders[] = $item;
            }
        }

        return $folders;
    }
}
