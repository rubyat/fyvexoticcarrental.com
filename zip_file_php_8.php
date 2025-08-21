<?php

// Ensure the zip file is created first

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
set_time_limit(-1);
ini_set('memory_limit', '-1');

$the_folder = realpath('../public_html/');
$zip_file_name = 'backup.zip';

if (file_exists($zip_file_name)) {
    unlink($zip_file_name); // Ensure the file does not exist before creating a new one
}

$za = new FlxZipArchive;
$res = $za->open($zip_file_name, ZipArchive::CREATE | ZipArchive::OVERWRITE);

if ($res === TRUE) {
    $za->addDir($the_folder, basename($the_folder));
    $za->close();
    echo 'done';
} else {
    echo 'Could not create a zip archive';
}

class FlxZipArchive extends ZipArchive
{
    public function addDir(string $location, string $name): void
    {
        $this->addEmptyDir($name);
        $this->addDirDo($location, $name);
    }

    private function addDirDo(string $location, string $name): void
    {
        $name .= '/';
        $location .= '/';
        $dir = opendir($location);
        while ($file = readdir($dir)) {
            if ($file == '.' || $file == '..' || $file == 'ai1wm-backups') continue;
            $do = (filetype($location . $file) == 'dir') ? 'addDir' : 'addFile';
            $this->$do($location . $file, $name . $file);
        }
        closedir($dir); // Close the directory handle
    }
}
