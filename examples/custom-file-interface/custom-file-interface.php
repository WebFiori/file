<?php

/**
 * Example: Implementing FileInterface
 *
 * Demonstrates how to create a custom FileInterface implementation.
 * This is useful for:
 * - In-memory file storage (testing, caching)
 * - Database-backed file storage
 * - Remote storage adapters (S3, FTP)
 * - Mocking in unit tests
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\FileInterface;
use WebFiori\File\MIME;

// --- Custom implementation: InMemoryFile ---
// Stores file data entirely in memory with no disk I/O.

class InMemoryFile implements FileInterface {
    private string $data = '';
    private string $dir = '';
    private string $name = '';

    public function __construct(string $name = '', string $dir = '/virtual') {
        $this->name = $name;
        $this->dir = $dir;
    }

    public function append(string|array $data): void {
        if (is_array($data)) {
            $this->data .= implode('', $data);
        } else {
            $this->data .= $data;
        }
    }

    public function copy(string $destination): FileInterface {
        $copy = new self(basename($destination), dirname($destination));
        $copy->setRawData($this->data);

        return $copy;
    }

    public function create(bool $createDirIfNotExist = false): void {
        // No-op: always "exists" in memory
    }

    public function getAbsolutePath(): string {
        return $this->dir.'/'.$this->name;
    }

    public function getDir(): string {
        return $this->dir;
    }

    public function getExtension(): string {
        $parts = explode('.', $this->name);

        return count($parts) > 1 ? end($parts) : '';
    }

    public function getMIME(): string {
        return MIME::getType($this->getExtension());
    }

    public function getName(): string {
        return $this->name;
    }

    public function getRawData(bool $encode = false): string {
        return $encode ? base64_encode($this->data) : $this->data;
    }

    public function getSize(): ?int {
        return strlen($this->data);
    }

    public function isExist(): bool {
        return strlen($this->data) > 0;
    }

    public function moveTo(string $destination): void {
        $this->name = basename($destination);
        $this->dir = dirname($destination);
    }

    public function read(int $from = -1, int $to = -1): void {
        // No-op: data is already in memory
    }

    public function remove(): bool {
        $this->data = '';

        return true;
    }

    public function setDir(string $dir): bool {
        $this->dir = $dir;

        return true;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setRawData(string $raw, bool $decode = false, bool $strict = false): void {
        $this->data = $decode ? base64_decode($raw, $strict) : $raw;
    }

    public function write(bool $append = true, bool $createIfNotExist = false): void {
        // No-op: no disk to write to
    }
}

// --- Using the custom implementation ---

echo "=== InMemoryFile ===\n";
$file = new InMemoryFile('report.txt');
$file->setRawData('Quarterly earnings report.');

echo "Name: ".$file->getName()."\n";
echo "Path: ".$file->getAbsolutePath()."\n";
echo "MIME: ".$file->getMIME()."\n";
echo "Size: ".$file->getSize()." bytes\n";
echo "Data: ".$file->getRawData()."\n";

// --- Dependency injection: code that accepts FileInterface ---

echo "\n=== Dependency Injection ===\n";

class DocumentProcessor {
    public function toUpperCase(FileInterface $file): string {
        return strtoupper($file->getRawData());
    }
    public function wordCount(FileInterface $file): int {
        return str_word_count($file->getRawData());
    }
}

$processor = new DocumentProcessor();
echo "Word count: ".$processor->wordCount($file)."\n";
echo "Upper case: ".$processor->toUpperCase($file)."\n";

// --- Copy and move ---

echo "\n=== Copy and Move ===\n";
$copy = $file->copy('/archive/report-backup.txt');
echo "Copy path: ".$copy->getAbsolutePath()."\n";
echo "Copy data: ".$copy->getRawData()."\n";

$file->moveTo('/final/report-2026.txt');
echo "Moved to:  ".$file->getAbsolutePath()."\n";

// --- Mocking in tests (PHPUnit-style) ---

echo "\n=== Mocking Pattern ===\n";
// In a real test you'd use $this->createMock(FileInterface::class)
// Here we show the pattern with our InMemoryFile:

$mockFile = new InMemoryFile('mock.csv');
$mockFile->setRawData("id,name\n1,Alice\n2,Bob");

echo "Mock file: ".$mockFile->getName()."\n";
echo "Mock MIME: ".$mockFile->getMIME()."\n";
echo "Processor works with mock: ".$processor->wordCount($mockFile)." words\n";

// --- Using with uploaders (afterUpload callback) ---

echo "\n=== Using with Uploaders ===\n";

// Simulate a storage backend that uses your custom FileInterface implementation
class InMemoryStorage {
    /** @var FileInterface[] */
    private array $files = [];

    public function count(): int {
        return count($this->files);
    }

    public function get(string $name): ?FileInterface {
        return $this->files[$name] ?? null;
    }

    public function save(FileInterface $file): void {
        $this->files[$file->getName()] = $file;
    }
}

$storage = new InMemoryStorage();

// After upload, convert UploadedFile into your custom implementation
$tmpDir = __DIR__.'/../tmp';

if (!is_dir($tmpDir.'/uploads')) {
    mkdir($tmpDir.'/uploads', 0755, true);
}

$inputPath = $tmpDir.'/custom-input.dat';
file_put_contents($inputPath, 'Data for custom storage.');

$uploader = new WebFiori\File\StreamingUploader($tmpDir.'/uploads', [], $inputPath);
$uploader->setOnAfterUpload(function (WebFiori\File\UploadedFile $uploaded) use ($storage)
{
    // Convert to your custom FileInterface implementation
    $myFile = new InMemoryFile($uploaded->getName(), '/storage');
    $myFile->setRawData(file_get_contents($uploaded->getAbsolutePath()));
    $storage->save($myFile);

    // Remove the disk copy (now lives in your custom storage)
    $uploaded->remove();
});

$uploader->receive('custom-stored.dat');

echo "Files in storage: ".$storage->count()."\n";
$stored = $storage->get('custom-stored.dat');
echo "Stored name: ".$stored->getName()."\n";
echo "Stored path: ".$stored->getAbsolutePath()."\n";
echo "Stored size: ".$stored->getSize()." bytes\n";
echo "Stored data: ".$stored->getRawData()."\n";

// Cleanup
unlink($inputPath);
@rmdir($tmpDir.'/uploads');
