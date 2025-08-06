<?php

use WebFiori\File\File;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

define('DS', DIRECTORY_SEPARATOR);
define('TESTING', true);
require_once __DIR__.DS.'..'.DS.'vendor'.DS.'autoload.php';

$testsDirName = 'tests';
$rootDir = substr(__DIR__, 0, strlen(__DIR__) - strlen($testsDirName));
$DS = DIRECTORY_SEPARATOR;
$rootDirTrimmed = trim($rootDir,'/\\');
if (explode($DS, $rootDirTrimmed)[0] == 'home') {
    //linux.
    $rootDir = $DS.$rootDirTrimmed.$DS;
} else {
    $rootDir = $rootDirTrimmed.$DS;
}
define('ROOT_PATH', $rootDir);

register_shutdown_function(function () {
    $file = new File(ROOT_PATH.DS.'tests'.DS.'files'.DS.'not-exist'.DS.'new.txt');
    $file->remove();
    rmdir(ROOT_PATH.DS.'tests'.DS.'files'.DS.'not-exist');
});

