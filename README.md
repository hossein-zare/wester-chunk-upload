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

} catch (SizeRuleException $e) {
    Header::status(422);

    return 'Your file size didn\'t match the specified size.';
} catch (MinRuleException $e) {
    Header::status(422);

    return 'Your file is too small.';
} catch (MaxRuleException $e) {
    Header::status(422);

    return 'Your file is too large.';
} catch (ExtensionRuleException $e) {
    Header::status(422);

    return 'Your file type is invalid.';
} catch (ValidationException $e) {

    /** NEVER CHANGE THIS CODE **/
    Header::abort(500);
} catch (\Exception $e) {

    /** NEVER CHANGE THIS CODE **/
    Header::status(500);

}
```

## Methods
* `store()` stores the chunk and merges it.
* `validate()` validates the chunk.
* `getFilePath()` gets the final file path.
* `getProgress()` gets the progress in percentage (float).
* `isLast()` checks if its the last chunk.
* `getFileExtension()` gets the file extension.
* `getFileName()` gets the file name without extension.
* `getFullFileName()` gets the full file name with extension.
* `getTempFilePath()` gets the temp file path.
* `getSize()` gets the current chunk size.
* `getTotalNumber()` gets the total number of chunks.

## Properties
* `options` returns an array of the parsed options.

    ```php
    $chunk->options['name'];
    ...
    ```
* `header` returns an instance of `\Wester\ChunkUpload\Header`

    ```php
    $chunk->header->chunkNumber;
    $chunk->header->chunkTotalNumber;
    $chunk->header->chunkSize; // equal to   $chunk->options['chunk_size'];
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
    use Wester\ChunkUpload\Validation\Rules\Exceptions\RequiredRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\SizeRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\MinRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\MaxRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\StringRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\NumericRuleException;
    use Wester\ChunkUpload\Validation\Rules\Exceptions\ExtensionRuleException;
    ```
    Only `Size` `Min` `Max` `Extension` Exceptions are usable for you.
    
    ```php
    try {

        $chunk = ...

    } catch (SizeRuleException $e) {
        Header::status(422);

        return 'Your file size didn\'t match the specified size.';
    } catch (MinRuleException $e) {
        Header::status(422);

        return 'Your file size is too less.';
    } catch (MaxRuleException $e) {
        Header::status(422);

        return 'Your file size is too much.';
    } catch (ExtensionRuleException $e) {
        Header::status(422);

        return 'Your file type is invalid.';
    } catch (ValidationException $e) {

        /** NEVER CHANGE THIS CODE **/
        Header::abort(500);
    }
    ```

* ### Chunk
    ```php
    use Wester\ChunkUpload\Exceptions\ChunkException;
    ```
    You can also use `\Exception` if you don't care whether a chunk or file exception has been thrown.

    ```php
    } catch (ChunkException $e) {

        /** NEVER CHANGE THIS CODE **/
        Header::abort(500);
    }

    // or 

    } catch (\Exception $e) {

        /** NEVER CHANGE THIS CODE **/
        Header::abort(500);
    }
    ```

* ### File
    ```php
    use Wester\ChunkUpload\Exceptions\FileException;
    ```
    You can also use `\Exception` if you don't care whether a chunk or file exception has been thrown.

     ```php
    } catch (FileException $e) {

        /** NEVER CHANGE THIS CODE **/
        Header::abort(500);
    }

    // or 

    } catch (\Exception $e) {

        /** NEVER CHANGE THIS CODE **/
        Header::abort(500);
    }
    ```

* #### Order
    The order of exceptions is highly recommended because instead of catching a specified exception it may catch `\Exception`

    ```php
    SizeRuleException
    MinRuleException
    MaxRuleException
    ExtensionRuleException
    ValidationException
    ChunkException
    FileException
    \Exception // Required
    ```