# Wester Chunk Upload Library For PHP

## Description
Wester chunk upload is a php library to deal with chunked uploads. This makes it super easy to validate the chunks and ensure you are secure.

## Installation
```bash
composer require wester/wester-chunk-upload
```

## Usage
Here's an example of the package.
```php
use Wester\ChunkUpload\Chunk;
use Wester\ChunkUpload\Header;
use Wester\ChunkUpload\Validation\Rules\Exceptions\SizeRuleException;
use Wester\ChunkUpload\Validation\Rules\Exceptions\MinRuleException;
use Wester\ChunkUpload\Validation\Rules\Exceptions\MaxRuleException;
use Wester\ChunkUpload\Validation\Rules\Exceptions\ExtensionRuleException;

try {
    $chunk = new Chunk([
        'name' => 'video', // same as    $_FILES['video']
        'chunk_size' => 40000, // must be equal to the value specified on the client side
        'path' => __DIR__ . '/uploads/', // where to upload the final file
        'tmp_path' => __DIR__ . '/uploads/temp/', // where to store the temp chunks

        // File details
        'file_name' => Chunk::RANDOM_FILE_NAME,
        'file_extension' => Chunk::ORIGINAL_FILE_EXTENSION,

        // File validation
        'validation' => [
            'file_name' => ['extension:mp4,avi'],
            'file_size' => ['size:237492']
        ]
    ]);

    $chunk->validate()->store();

    // Finished?
    if ($chunk->isLast()) {

        $chunk->getFilePath();

    } else {

        return $chunk->getProgress();

    }

} catch (SizeRuleException|MinRuleException|MaxRuleException|ExtensionRuleException $e) {
    Header::status(422);

    return $e->getMessage();
} catch (\Exception $e) {

    /** NEVER CHANGE THIS CODE **/
    Header::abort(400);

}
```

## Methods
* `store()` stores the chunk and merges it.
* `validate()` validates the chunk.
* `getFilePath()` gets the final file path.
* `getProgress()` gets the progress percentage (float).
* `isLast()` checks if its the last chunk.
* `getFileExtension()` gets the file extension.
* `getFileName()` gets the file name without extension.
* `getFullFileName()` gets the full file name with extension.
* `getTempFilePath()` gets the temp file path.
* `getSize()` gets the current chunk size.
* `getTotalNumber()` gets the total number of chunks.

## Properties
* `configs` returns an array of the parsed configs.

    ```php
    $chunk->configs['name'];
    ...
    ```
* `header` returns an instance of `\Wester\ChunkUpload\Header`

    ```php
    $chunk->header->chunkNumber;
    $chunk->header->chunkTotalNumber;
    $chunk->header->chunkSize; // equal to: x-chunk-size 
    $chunk->header->fileName;
    $chunk->header->fileSize;
    $chunk->header->fileIdentity;
    ```

## Flags
* `Chunk::RANDOM_FILE_NAME` creates a random file name.
* `Chunk::ORIGINAL_FILE_NAME` preserves the original file name.
* `Chunk::ORIGINAL_FILE_EXTENSION` preserves the original file extension.
> You can also specify a custom file name and extension.

## Validation
* ### File Name
    **`extension`**
    ```php
    'file_name' => ['extension:mp4,avi']
    ```
* ### File Size
    **`size`**
    ```php
    'file_size' => ['size:237492']
    ```

    **`min`**
    ```php
    'file_size' => ['min:10000']
    ```

    **`min`**
    ```php
    'file_size' => ['max:90000']
    ```

## Exceptions
This package provides a bunch of Validation Exceptions, You can see the available Exceptions right below.

* ### Validation
    ```php
    use Wester\ChunkUpload\Validation\Rules\Exceptions\ValidationException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\SizeRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\MinRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\MaxRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\ExtensionRuleException;
    ```
    Only `SizeRuleException`, `MinRuleException`, `MaxRuleException` and `ExtensionRuleException` Exceptions are usable for you.

* ### Chunk
    ```php
    use Wester\ChunkUpload\Exceptions\ChunkException;
    ```

* ### File
    ```php
    use Wester\ChunkUpload\Exceptions\FileException;
    ```

## HTTP Response Status Codes
This package uses the HTTP response status codes to decide what to do next if the request fails or succeeds when uploading.

* ### Errors
    The following status codes will interrupt the process.

    * `422`
    * `400`
    * `500`

> If another status code is returned the chunk must be re-uploaded.

## Client Side
### Headers
There are some headers that should be sent to the server.
* `x-chunk-number` The current chunk number which is being uploaded.
* `x-chunk-total-number` The total number of chunks.
* `x-chunk-size` Maximum size of each chunk. (each chunk must be 4000 bytes and only the last chunk can be less than that)
* `x-file-name` The uploaded file name.
* `x-file-size` The uploaded file size.
* `x-file-identity` Random string for the file.

An example of the headers.
```json
{
    "x-chunk-number" : 1,
    "x-chunk-total-number" : 5,
    "x-chunk-size" : 4000,
    "x-file-name" : "my-file-name.mp4",
    "x-file-size" : 20000,
    "x-file-identity" : "rmghdygvdstcsjglltmbvkynxpeajgcg"
}
```