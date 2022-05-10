# File
Basic class library to read, write and view files using PHP.


<p align="center">
  <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php81.yml">
    <img src="https://github.com/WebFiori/file/workflows/Build%20PHP%208.1/badge.svg?branch=main">
  </a>
  <a href="https://codecov.io/gh/WebFiori/file">
    <img src="https://codecov.io/gh/WebFiori/file/branch/main/graph/badge.svg" />
  </a>
  <a href="https://sonarcloud.io/dashboard?id=WebFiori_file">
      <img src="https://sonarcloud.io/api/project_badges/measure?project=WebFiori_file&metric=alert_status" />
  </a>
  <a href="https://packagist.org/packages/webfiori/file">
    <img src="https://img.shields.io/packagist/dt/webfiori/file?color=light-green">
  </a>
</p>

## Supported PHP Versions
| Build Status |
|:-----------:|
|<a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php70.yml"><img src="https://github.com/WebFiori/file/workflows/Build%20PHP%207.0/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php71.yml"><img src="https://github.com/WebFiori/file/workflows/Build%20PHP%207.1/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php72.yml"><img src="https://github.com/WebFiori/file/workflows/Build%20PHP%207.2/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php73.yml"><img src="https://github.com/WebFiori/file/workflows/Build%20PHP%207.3/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php74.yml"><img src="https://github.com/WebFiori/file/workflows/Build%20PHP%207.4/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php80.yml"><img src="https://github.com/WebFiori/file/workflows/Build%20PHP%208.0/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php81.yml"><img src="https://github.com/WebFiori/file/workflows/Build%20PHP%208.1/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php82.yml"><img src="https://github.com/WebFiori/file/workflows/Build%20PHP%208.2/badge.svg?branch=main"></a><br>(dev)|

## Main Aim of The Library
The main aim of the library is to have an OOP abstraction that simplifies most common operations with files in PHP.

## Usage

### Reading a File

``` php
$file = new File('path/to/my/file.txt');
$file->read();

$fileData = $file->getRawData();
// Do anything you like with data string.
```

### Creating New File

``` php 
$file = new File('path/to/my/new/file.txt');
$file->setRawData('Hello World');

$file->write(false, true);
//The first parameter is used to append data to file.
//If the file exist and set to true, data will be appended.
//Second parameter is used to tell the class to create
//The file if does not extst. If set to false and the
//file does not exist, the class will throw an exception.

```

### Appending to Existing File

``` php 
$file = new File('path/to/my/old/file.txt');
$file->setRawData('Another Hello World');
$file->write(true);
```

### Encoding or Decoding of Files

Base64 encoding and decoding is usually used to make sure that binary data is stored and transmitted reliably from one place to another. For more information, [read here](https://en.wikipedia.org/wiki/Base64)

``` php
$file = new File('file.txt');
$file->setRawData('Hello World');

$encoded = $file->getRawData('e');
//The 'e' here means 'base 64 encode'
```

``` php
$file = new File('file.txt');
$file->setRawData('Hello World');

$decoded = $file->getRawData('d');
//The 'd' here means 'base 64 decode'
```

### Display File

``` php 
$file = new File('some-pdf.pdf');
$file->read();
$file->view();
```

To trigger a download dialog in web browser, supply `true` as argument to the method `File::view()`.
``` php 
$file = new File('some-pdf.pdf');
$file->read();
$file->view(true);
```

### Chunking File

Usually, when a big file is stored in a database table, it is encoded then divided into smaller chunks, and each chunk is stored in a record. The class provides a single method for doing such procedure.

``` php
$file = new File('some-big-movie-file.mp4');
$file->read();

//The size of each chunk will be 1500 bytes and they will be base 64 encoded.
$dataChunks = $file->getChunks(1500);

foreach ($dataChunks as $chunk) {
    //......
}
```
