# Wester Chunk Upload Library For PHP

## Description
Wester chunk upload is a php library to deal with chunks while uploading. This makes it super easy to validate the chunks and make sure you are secure.

## Basic Usage
Here's an example of the simple usage.
```php
use Wester\ChunkUpload\Chunk;
use Wester\ChunkUpload\Header;
use Wester\ChunkUpload\Validation\Rules\Exceptions\ValidationException;

try {
    $chunk = new Chunk([
        'name' => 'video', // same as $_FILES['video']
        'chunk_size' => 40000, // in bytes
        'path' => __DIR__ . '/uploads/', // where to upload the final file
        'tmp_path' => __DIR__ . '/uploads/temp/', // where to store the temp chunks

        'file_name' => Chunk::RANDOM_FILE_NAME,
        'file_extension' => Chunk::ORIGINAL_FILE_EXTENSION,

        'validation' => [
            'file_name' => ['extension:jpg'],
            'file_size' => ['size:23792']
        ]
    ]);

    $chunk->validate()->store();

    // Progress
    $chunk->getProgress(); // float

    // Finished?
    if ($chunk->isLast()) {

        // Full File Name
        $chunk->getFullFileName(); // string

        // File Extension
        $chunk->getFileExtension(); // null|string

        // File Path
        $chunk->getFilePath(); // string

    }

} catch (ValidationException $e) {
    Header::abort(402);
} catch (ChunkException $e) {
    Header::abort(500);
} catch (FileException $e) {
    Header::abort(500);
}
```