# File
Basic class library to read, write and view files using PHP.


<p align="center">
  <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php82.yml">
    <img src="https://github.com/WebFiori/file/actions/workflows/php82.yml/badge.svg?branch=main">
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

## Content

* [Supported PHP Versions](#supported-php-versions)
* [Main Aim of The Library](#main-aim-of-the-library)
* [Usage](#usage)
    * [Reading a File](#reading-a-file)
    * [Creating New File](#creating-new-file)
    * [Appending to Existing File](#appending-to-existing-file)
    * [Overriding a File](#overriding-a-file)
    * [Encoding or Decoding of Files](#encoding-or-decoding-of-files)
      * [Decoding](#decoding)
    * [Reading and Storing Encoded Files](#reading-and-storing-encoded-files)
    * [Display a File](#display-a-file)
    * [Chunking File](#chunking-files)

## Supported PHP Versions
|                                                                                       Build Status                                                                                        |
|:-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php70.yml"><img src="https://github.com/WebFiori/file/actions/workflows/php70.yml?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php71.yml"><img src="https://github.com/WebFiori/file/actions/workflows/php71.yml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php72.yml"><img src="https://github.com/WebFiori/file/actions/workflows/php72.yml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php73.yml"><img src="https://github.com/WebFiori/file/actions/workflows/php73.yml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php74.yml"><img src="https://github.com/WebFiori/file/actions/workflows/php74.yml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php80.yml"><img src="https://github.com/WebFiori/file/actions/workflows/php80.yml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php81.yml"><img src="https://github.com/WebFiori/file/actions/workflows/php81.yml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php82.yml"><img src="https://github.com/WebFiori/file/actions/workflows/php82.yml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php83.yml"><img src="https://github.com/WebFiori/file/actions/workflows/php83.yml/badge.svg?branch=main"></a> |

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

Also, it is possible to read a specific range of bytes by supplying the range to the method `File::read()`

``` php
$file = new File('path/to/my/file.txt');
//Read bytes 10-100 inclusive
$file->read(10, 100);

$fileData = $file->getRawData();
```

### Creating New File

``` php 
$file = new File('path/to/my/new/file.txt');

//The method File::create() is used to create new files
$file->create();
$file->setRawData('Hello World');
$file->write();
```

### Appending to Existing File

``` php 
$file = new File('path/to/my/old/file.txt');
$file->setRawData('Hello');
$file->write();
$file->setRawData(' World');
$file->write();

//Setting raw data before each call to the method File::write() will append to file.
```

### Overriding a File

``` php 
$file = new File('path/to/my/old/file.txt');
$file->setRawData('Hello');
$file->write();
$file->setRawData(' World');
$file->write(true);

//By supplying true as parameter to the method File::write(), the old content of the file will be overridden. 
```
### Encoding or Decoding of Files

Base64 encoding and decoding is usually used to make sure that binary data is stored and transmitted reliably from one place to another. For more information, [read here](https://en.wikipedia.org/wiki/Base64)

#### Decoding
``` php
$file = new File('file.txt');

//'Hello World!' in base64
$encodedData = 'SGVsbG8gV29ybGQh';

$file->setRawData($encodedData, true);
$decoded = $file->getRawData();

//$decoded is now the string 'Hello World!'
//By supplying true as second parameter to the method File::setRawData(), the method will decode given data
```

``` php
$file = new File('file.txt');
$file->setRawData('Hello World');

$encoded = $file->getRawData(true);
//$encoded is now the string 'SGVsbG8gV29ybGQh'
//By supplying true as second parameter to the method File::getRawData(), the method will encode given data
```

### Reading and Storing Encoded Files
The method `File::writeEncoded()` is used to write base 64 enceded binary files as follows.

``` php
$file = new File('my-img.png');
$file->writeEncoded();

//This will create new file with the name 'my-img.png.bin'
```

The method `File::readDecoded()` is used to read base 64 enceded binary files as follows.

``` php
$file = new File('some-binary-with-encoded.bin');
$file->readDecoded();
$decoded = $file->getRawData();

```

### Display a File

The method `File::view()` is used to dispatch the content of the file to front-end. It also supports the header `content-range` which can be used to get partial file content.

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

### Chunking Files

Usually, when a big file is stored in a database table, it is encoded then divided into smaller chunks, and each chunk is stored in a record. The class provides a single method for doing such procedure.

``` php
$file = new File('some-big-movie-file.mp4');
$file->read();

//The size of each chunk will be 1500 bytes and they will be base 64 encoded by default.
$dataChunks = $file->getChunks(1500);

foreach ($dataChunks as $chunk) {
    //......
}
```

Supplying `false` as second parameter to the method will disable base 64 encoding.
