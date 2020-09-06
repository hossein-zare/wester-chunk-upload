# Wester Chunk Upload Library For PHP

## Description
Wester chunk upload is a php library to deal with chunks while uploading. This makes it super easy to validate the chunks and make sure you are secure.

## Basic Usage
Here's an example of the simple usage.
```php
use Wester\ChunkUpload\Chunk;
use Wester\ChunkUpload\Header;
use Wester\ChunkUpload\Exceptions\ChunkException;
use Wester\ChunkUpload\Exceptions\FileException;
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

    // Finished?
    if ($chunk->isLast()) {

        $chunk->getFilePath();

    } else {

        // Progress
        return $chunk->getProgress();
        
    }

} catch (ValidationException $e) {

    // Read the Exceptions section (Extendable)

    Header::abort(402); // required

} catch (ChunkException $e) {

    /** NEVER CHANGE THIS CODE **/

    Header::abort(500);

} catch (FileException $e) {

    /** NEVER CHANGE THIS CODE **/

    Header::abort(500);

}
```

## Exceptions
This package provides a bunch of Validation Exceptions, You can see the available Exceptions right below.

* ### Chunk
    ```php
    use Wester\ChunkUpload\Exceptions\ChunkException;
    ```

* ### File
    ```php
    use Wester\ChunkUpload\Exceptions\FileException;
    ```

* ### Validation
    ```php
    use Wester\ChunkUpload\Validation\Rules\Exceptions\ValidationException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\RequiredRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\SizeRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\MinRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\MaxRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\StringRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\NumericRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\ExtensionRuleException;
    ```
