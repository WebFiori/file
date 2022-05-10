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
define('ROOT_DIR', $rootDir);
define('DS', DIRECTORY_SEPARATOR);
echo 'Root Directory: \''.$rootDir.'\'.'."\n";
$jsonLibPath = $rootDir.'vendor'.DS.'webfiori'.DS.'jsonx'.DS.'webfiori'.DS.'json';
require_once $jsonLibPath.DS.'JsonI.php';
require_once $jsonLibPath.DS.'Json.php';
require_once $jsonLibPath.DS.'JsonConverter.php';
require_once $jsonLibPath.DS.'CaseConverter.php';
require_once $jsonLibPath.DS.'JsonTypes.php';
require_once $jsonLibPath.DS.'Property.php';

require_once $rootDir.'vendor'.DS.'webfiori'.DS.'http'.DS.'webfiori'.DS.'http'.DS.'Response.php';

require_once $rootDir.'src'.DS.'webfiori'.DS.'framework'.DS.'File.php';
require_once $rootDir.'src'.DS.'webfiori'.DS.'framework'.DS.'MIME.php';
require_once $rootDir.'src'.DS.'webfiori'.DS.'framework'.DS.'exceptions'.DS.'FileException.php';

