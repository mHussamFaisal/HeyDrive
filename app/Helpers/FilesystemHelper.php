<?php

namespace App\Helpers;

class FilesystemHelper
{
    public static function resetPermissions($dir = 0755, $file = 0644)
    {
        $list = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path()), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($list as $v) {
            $name = basename($v);

            if ($name != '.' && $name != '..' && strpos($v, '.git') === false && strpos($v, '.idea') === false && strpos($v, 'node_modules') === false) {
                if (is_dir($v)) {
                    @chmod($v, $dir);
                }
                else if (is_file($v)) {
                    @chmod($v, $file);
                }
            }
        }
    }

    public static function clearTmp()
    {
        try {
            $fileSystem = new \Illuminate\Filesystem\Filesystem();
            $fileSystem->cleanDirectory(public_path('tmp'));
            $fileSystem->put(public_path('tmp/.gitignore'), "*\r\n!.gitignore");
        }
        catch (Exception $e) {
            \Log::error(['FilesystemHelper::clearTmp()', $e->getMessage(), $e->getCode()]);
        }
    }
}
