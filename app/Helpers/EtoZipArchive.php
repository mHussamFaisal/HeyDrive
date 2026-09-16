<?php

namespace App\Helpers;

use Illuminate\Filesystem\Filesystem;
use \ZipArchive;

class EtoZipArchive extends ZipArchive
{
    public $timeOut;
    public $startTime;
    public $waitingTime = 5;

    /**
     * @param string $zipFile
     * @return mixed
     */
    public function getZip($zipFile = '') {
        $this->timeOut = get_ini_time(20);
        $this->startTime = microtime(true);
        return $this->open($zipFile);
    }

    /**
     * @param $destination
     * @param array $subdirs
     * @param array $errors
     * @return array
     */
    public function extractSubdirArrayTo($destination, $subdirs = [], $errors=[]) {
        $destination = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $destination);

        if (is_dir($destination)) {
            if (substr($destination, mb_strlen(DIRECTORY_SEPARATOR, "UTF-8") * -1) != DIRECTORY_SEPARATOR) {
                $destination .= DIRECTORY_SEPARATOR;
            }
        }

        $beforeForeachStartTime = microtime(true) - $this->startTime;
        foreach($subdirs as $i=>$subdir) {
            $loopStartTime = microtime(true);
            $subdir = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $subdir);
            if (is_dir($destination.DIRECTORY_SEPARATOR.$subdir)) {
                $subdir .= substr($subdir, -1) != DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : '';
            }
            $dir = $destination;
            $dir .= substr($subdir, -1) == "/" ? $subdir : dirname($subdir);

            if (!is_dir($dir)) {
                if (!@mkdir($dir, 0755, true)) {
                    $errors[$i] = $subdir;
                }
            }

            $this->extractTo($destination, [str_replace(array("/", "\\"), "/", $subdir)]);

            unset($subdirs[$i]);

            $executionTime = microtime(true);
            $loopTime = $executionTime - $loopStartTime;

            if ((($executionTime - $this->startTime) + $beforeForeachStartTime + $loopTime + $this->waitingTime) > $this->timeOut) {
                break;
            }
        }

        return $subdirs;
    }

    /**
     * @param null $regexFilter
     * @param bool $onlyFilter
     * @return array
     */
    public function listFiles($regexFilter = null, $onlyFilter = true, $regexFilterSkip = null)
    {
        $list = [];
        for ( $i = 0; $i < $this->numFiles; $i++ ) {
            $name = $this->getNameIndex($i);

            if (is_array($regexFilter)) {
                $regexp = '#';
                foreach($regexFilter as $filter) {
                    $regexp .= '('.$filter.')';
                }
                $regexp .= '#';
            }
            elseif (null !== $regexFilter) {
                $regexp = '#('.$regexFilter.')#';
            }

            if ($regexFilterSkip && preg_match($regexFilterSkip, $name)) {
                continue;
            }

            if ($regexFilter) {
                if ($onlyFilter) {
                    set_error_handler(function () { });
                    preg_match($regexp, $name, $matches);
                    restore_error_handler();
                    if ($matches) {
                        $list[] = $matches[0];
                    }
                    else {
                        $list[] = $name;
                    }
                }
            }
            else {
                $list[] = $name;
            }
        }

        $list = array_unique($list);

        return $list;
    }
}
