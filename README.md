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
            'file_name' => ['extension:mp4,avi'],
            'file_size' => ['size:237492']
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

    // Read Extensions (Validation)

} catch (ChunkException $e) {

    /** NEVER CHANGE THIS CODE **/

    Header::status(500);

} catch (FileException $e) {

    /** NEVER CHANGE THIS CODE **/

    Header::status(500);

}
```

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
    \Exception
    ```