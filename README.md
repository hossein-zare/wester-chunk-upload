# Wester Chunk Upload Library For PHP
Wester chunk upload is a php library to handle chunked uploads which supports local and ftp file upload out of the box.  
You'll feel safe with the built-in file validator.

## Table of contents
* [Installation](#installation)
* [Basic Usage](#basic-usage)
* [Drivers](#drivers)
    * [Implement The Driver](#implement-the-driver)
* [Methods](#methods)
* [Properties](#properties)
* [Validation Rules](#validation-rules)
* [Language](#language)
* [Flags](#flags)
* [HTTP Response Status Codes](#http-response-status-codes)
* [Client Side](#client-side)
    * [Headers](#headers)
    * [Examples](#examples)
        * [Javascript](#javascript)
        * [Contribution](#contribution)
* [Support Us](#support-us)

## Installation
```bash
composer require wester/chunk-upload
```

## Basic Usage
Here's an example of the package.
```php
// You don't need this line in laravel or some other frameworks.
require("./vendor/autoload.php");

use Wester\ChunkUpload\Chunk;
use Wester\ChunkUpload\Validation\Exceptions\ValidationException;

try {
    $chunk = new Chunk([
        'name' => 'video', // same as    $_FILES['video']
        'chunk_size' => 4000, // must be equal to the value specified on the client side

        // Driver
        'driver' => 'local', // [local, ftp]

        // Local driver details
        'local_driver' => [
            'path' => __DIR__ . '/uploads/', // where to upload the final file
            'tmp_path' => __DIR__ . '/uploads/temp/', // where to store the temp chunks
        ],

        // FTP driver details
        'ftp_driver' => [
            'server' => '',
            'username' => '',
            'password' => '',

            'path' =>  '/uploads/', // where to upload the final file
            'tmp_path' => '/uploads/temp/', // where to store the temp chunks
        ],

        // File details
        'file_name' => Chunk::RANDOM_FILE_NAME,
        'file_extension' => Chunk::ORIGINAL_FILE_EXTENSION,

        // File validation
        'validation' => ['extension:mp4,avi'],
    ]);

    $chunk->validate()->store();

    if ($chunk->isLast()) {

        // done
        $chunk->getFilePath();
        
    } else {
        $chunk->response()->json([
            'progress' => $chunk->getProgress()
        ]);
    }

} catch (ValidationException $e) {
    $e->response(422)->json([
        'message' => $e->getMessage(),
        'data' => $e->getErrors(),
    ]);
} catch (\Exception $e) {
    $e->response(400)->abort();
}
```

## Drivers
This package supports `ftp` file upload out of the box.  
`local` and `ftp` or custom drivers can be used.

```php
'driver' => 'ftp',
```

* ### Implement The Driver
    Your custom driver should implement the `\Wester\ChunkUpload\Drivers\Contracts\DriverInterface`.  

    ```php
    'driver' => \My\Custom\Drivers\DriverName::class,
    'custom_driver' => [
        'path' =>  '/uploads/',
        'tmp_path' => '/uploads/temp/',
    ],
    ```

    ```php
    <?php

    namespace My\Custom\Drivers;

    class DriverName implements \Wester\ChunkUpload\Drivers\Contracts\DriverInterface
    {
        public function open() {};
        public function close() {};
        public function store($fileName) {};
        public function delete() {};
        public function move() {};
        public function increase() {};
        public function prevExists() {};
        public function exists() {};
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
* `setLanguage([...])` sets the language to the provided array
* `response($status = null)` returns an instance of `\Wester\ChunkUpload\Response`

    ```php
    $chunk->response(200)->json([...]);
    $chunk->response()->json([...]);

    // If an exception is caught...
    $e->response(400)->...
    $e->response(400)->abort();
    $e->response()->abort(400);
    ...
    ```

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

## Validation Rules
* `extension`
    ```php
    'validation' => ['extension:mp4,avi']
    ```
* `size`
    ```php
    'validation' => ['size:237492']
    ```

 * `min`
    ```php
    'validation' => ['min:10000']
    ```

* `max`
    ```php
    'validation' => ['max:90000']
    ```

## Language
You can easily change the validation messages the same as Laravel.

```php
$chunk->setLanguage([
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
    ],
    'max' => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
    ],
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',

    'attributes' => [
        'x-file-name' => 'file',
        'x-file-size' => 'file',
    ],
]);
```

## Flags
* `Chunk::RANDOM_FILE_NAME` creates a random file name.
* `Chunk::ORIGINAL_FILE_NAME` preserves the original file name.
* `Chunk::ORIGINAL_FILE_EXTENSION` preserves the original file extension.
> You can also specify a custom file name and extension.

## HTTP Response Status Codes
This package uses the HTTP response status codes to decide what to do next if the request fails or succeeds when uploading.

* ### Success
    * `200` All of the chunks have been uploaded completely.
    * `201` The server is waiting for the next chunk to be sent.

* ### Errors
    The following status codes will interrupt the process.

    * `400`
    * `404`
    * `415`
    * `422`
    * `500`
    * `501`

> Feel free to add more status codes to your client side.

> If another status code is returned the chunk must be re-uploaded such as `timeout` and `network error`.

## Client Side
### Headers
There are some headers that should be sent to the server.
* `x-chunk-number` The current chunk number which is being uploaded.
* `x-chunk-total-number` The total number of chunks.
* `x-chunk-size` Maximum size of each chunk. (each chunk must be 4000 bytes and only the last chunk can be less than that)
* `x-file-name` The uploaded file name.
* `x-file-size` The uploaded file size.
* `x-file-identity` Random string for the file which must be 32 characters in length.

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

### Examples & Packages
You can find examples in [wester-chunk-upload-examples](https://github.com/hossein-zare/wester-chunk-upload-examples/) repository.

#### **Javascript**
The client side implementation in Javascript.
* [https://github.com/hossein-zare/wester-chunk-upload-examples/tree/master/javascript](https://github.com/hossein-zare/wester-chunk-upload-examples/tree/master/javascript)

#### **React Native**
The client side implementation in React Native.
* [https://github.com/hossein-zare/react-native-chunk-upload](https://github.com/hossein-zare/react-native-chunk-upload)

#### Contribution
* If you want to add more implementations in other languages please submit your PR to the [wester-chunk-upload-examples](https://github.com/hossein-zare/wester-chunk-upload-examples/) repository.

## Support Us
Just star the repository, that's it! 😉