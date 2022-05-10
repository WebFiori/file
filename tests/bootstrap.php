<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
$testsDirName = 'tests';
$rootDir = substr(__DIR__, 0, strlen(__DIR__) - strlen($testsDirName));
$DS = DIRECTORY_SEPARATOR;
$rootDirTrimmed = trim($rootDir,'/\\');
echo 'Include Path: \''.get_include_path().'\''."\n";

if (explode($DS, $rootDirTrimmed)[0] == 'home') {
    //linux.
    $rootDir = $DS.$rootDirTrimmed.$DS;
} else {
    $rootDir = $rootDirTrimmed.$DS;
}
define('ROOT', $rootDir);
echo 'Root Directory: \''.$rootDir.'\'.'."\n";
require_once $rootDir.'webfiori'.$DS.'framework'.$DS.'File.php';
require_once $rootDir.'webfiori'.$DS.'framework'.$DS.'MIME.php';
require_once $rootDir.'webfiori'.$DS.'framework'.$DS.'exceptions'.$DS.'FileException.php';
require_once $rootDir.'vendor'.$DS.'webfiori'.$DS.'json'.$DS.'Json.php';
