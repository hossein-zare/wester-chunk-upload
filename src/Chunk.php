<?php

namespace Wester\ChunkUpload;

use Wester\ChunkUpload\Language\Language;
use Wester\ChunkUpload\Exceptions\ChunkException;

class Chunk
{
    /**
     * The file name flags.
     */
    const RANDOM_FILE_NAME = 1;
    const ORIGINAL_FILE_NAME = 2;

    /**
     * The file extension flags
     */
    const ORIGINAL_FILE_EXTENSION = 1;

    /**
     * The file.
     * 
     * @var object
     */
    public $file;

    /**
     * The headers.
     * 
     * @var \Wester\ChunkUpload\Header
     */
    public $header;

    /**
     * The driver.
     * 
     * @var object
     */

    /**
     * The required headers.
     * 
     * @var array
     */
    protected $requiredHeaders = [];

    /**
     * The configs.
     * 
     * @var array
     */
    public $configs = [];

    /**
     * The language.
     * 
     * @var array
     */
    private $language = [
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
    ];

    /**
     * Create a new instance.
     * 
     * @param  array  $configs
     * @return void
     */
    public function __construct(array $configs)
    {
        $this->setConfigs($configs)
            ->setLanguage($this->language)
            ->setRequiredHeaders()
            ->setFile()
            ->setHeader()
            ->setDriver()
            ->header->validate($this->requiredHeaders);
    }

    /**
     * Set configs.
     * 
     * @param  array  $configs
     * @return \Wester\ChunkUpload\Chunk
     */
    private function setConfigs(array $configs)
    {
        $this->configs = $configs;

        return $this;
    }

    /**
     * Set required headers.
     * 
     * @return \Wester\ChunkUpload\Chunk
     */
    private function setRequiredHeaders()
    {
        $this->requiredHeaders = [
            'x-chunk-number' => ['!', 'required', 'numeric'],
            'x-chunk-total-number' => ['!', 'required', 'numeric'],
            'x-chunk-size' => ['!', 'required', 'numeric'],
            'x-file-name' => ['required', 'string', ...$this->getValidationRule(['extension'])],
            'x-file-size' => ['required', 'file', ...$this->getValidationRule(['min', 'max', 'size'])],
            'x-file-identity' => ['!', 'required', 'string', 'size:32']
        ];

        return $this;
    }

    /**
     * Create an object of header.
     * 
     * @return \Wester\ChunkUpload\Header
     */
    private function getHeader()
    {
        return new Header(
            array_keys($this->requiredHeaders)
        );
    }

    /**
     * Set the instance of header.
     * 
     * @return \Wester\ChunkUpload\Header
     */
    private function setHeader()
    {
        $this->header = $this->getHeader();

        return $this;
    }

    /**
     * Set language array.
     * 
     * @param  array  $language
     * @return \Wester\ChunkUpload\Chunk
     */
    public function setLanguage(array $language)
    {
        Language::set($language);

        return $this;
    }

    /**
     * Create an object of file.
     * 
     * @param  string  $name
     * @return \Wester\ChunkUpload\File
     */
    private function getFile(string $name)
    {
        return new File($name);
    }

    /**
     * Set the instance of file.
     * 
     * @return \Wester\ChunkUpload\Chunk
     */
    private function setFile()
    {
        $this->file = $this->getFile($this->configs['name']);

        return $this;
    }

    /**
     * Initialize the driver.
     * 
     * @return object
     */
    private function setDriver()
    {
        if (in_array($this->configs['driver'], ['local', 'ftp'])) {
            $name = ucfirst($this->configs['driver']);
            $driver = "\\Wester\\ChunkUpload\\Drivers\\{$name}Driver";
        }

        $this->driver = new $driver($this->configs, $this->header);

        return $this;
    }

    /**
     * Validate chunks.
     * 
     * @return \Wester\ChunkUpload\Chunk
     */
    public function validate()
    {
        if ($this->header->chunkTotalNumber !== $this->getTotalNumber()) {
            $this->revoke("The total number of chunks is invalid.");
        }

        if ($this->header->chunkNumber < 1 || $this->header->chunkNumber > $this->header->chunkTotalNumber) {
            $this->revoke("The chunk number is invalid.");
        }

        if ($this->file->size !== $this->getSize($this->header->chunkNumber)) {
            $this->revoke("The chunk size is invalid.");
        }

        if (! $this->isChunk()) {
            $this->revoke("The uploaded file is not a chunk.");
        }

        if ($this->driver->prevExists() === false) {
            $this->revoke("Previous chunk doesn't exist.");
        }

        if ($this->driver->exists()) {
            $this->revoke("Chunk {$this->header->chunkNumber} already exists.");
        }

        return $this;
    }

    /**
     * Revoke the action.
     * 
     * @param  string  $text
     * @return void
     * 
     * @throws \Wester\ChunkUpload\ChunkException
     */
    private function revoke(string $text): void
    {
        $this->driver->delete();

        throw new ChunkException($text);
    }

    /**
     * Get validation rules.
     * 
     * @param  array  $rules
     * @return array
     */
    private function getValidationRule(array $rules)
    {
        $array = [];

        foreach ($this->configs['validation'] as $validation) {
            $values = explode(':', $validation);

            if (in_array($values[0], $rules))
                $array[] = $validation;
        }

        return $array;
    }

    /**
     * Store the chunk.
     * 
     * @return \Wester\ChunkUpload\Chunk
     */
    public function store()
    {
        $this->driver->increase();
        $this->driver->store($this->file->tmp_name);
    
        if ($this->isLast()) {
            $this->response()->status(200);
            $this->driver->move();
        } else {
            $this->response()->status(201);
        }

        $this->driver->close();

        return $this;
    }

    /**
     * Get progress.
     * 
     * @return float
     */
    public function getProgress()
    {
        return ($this->header->chunkNumber / $this->header->chunkTotalNumber) * 100;
    }

    /**
     * Get total number of chunks.
     * 
     * @return int
     */
    public function getTotalNumber(): int
    {
        $number = (int) ceil($this->header->fileSize / $this->configs['chunk_size']);

        return $number !== 0 ? $number : 1;
    }

    /**
     * Get the size of the specified chunk.
     * 
     * @param  int  $part
     * @return int
     */
    public function getSize(int $part): int
    {
        $total = $this->header->fileSize - ($part * $this->configs['chunk_size']);

        if ($total < 0) {
            return $this->configs['chunk_size'] + $total;
        }

        return $this->configs['chunk_size'];
    }

    /**
     * Get temp file path.
     * 
     * @param  null|int  $part
     * @return string
     */
    public function getTempFilePath(int $part = null): string
    {
        return $this->driver->getTempFilePath($part);
    }

    /**
     * Get temp file path.
     * 
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->driver->getFilePath();
    }

    /**
     * Get file name.
     * 
     * @return string
     */
    public function getFileName(): string
    {
        return $this->driver->getFileName();
    }

    /**
     * Get file name with extension.
     * 
     * @return string
     */
    public function getFullFileName(): string
    {
        return $this->driver->getFullFileName();
    }

    /**
     * Get the file extension.
     * 
     * @return null|string
     */
    public function getFileExtension()
    {
        return $this->driver->getFileExtension();
    }

    /**
     * Determine whether it's the last chunk.
     * 
     * @return bool
     */
    public function isLast(): bool
    {
        return $this->header->chunkNumber === $this->header->chunkTotalNumber;
    }

    /**
     * Check if the file is a chunk.
     * 
     * @return bool
     */
    private function isChunk(): bool
    {
        return $this->file->name === 'blob'
            && $this->file->type === 'application/octet-stream';
    }

    /**
     * Create a new instance of the response class.
     * 
     * @param  null|int  $status
     * @return \Wester\ChunkUpload\Response
     */
    public function response($status = null)
    {
        return (new Response())->status($status);
    }
}
