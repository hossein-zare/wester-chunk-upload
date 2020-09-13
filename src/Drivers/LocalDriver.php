<?php

namespace Wester\ChunkUpload\Drivers;

use \Wester\ChunkUpload\Chunk;
use \Wester\ChunkUpload\Header;
use Wester\ChunkUpload\Drivers\Contracts\DriverInterface;

class LocalDriver implements DriverInterface
{
    /**
     * Create a new instance.
     * 
     * @param  array  $configs
     * @param  \Wester\ChunkUpload\Header  $header
     * @return void
     */
    public function __construct(array $configs, Header $header)
    {
        $this->configs = $configs;
        $this->header = $header;
    }

    /**
     * Delete a temp chunk.
     * 
     * @return void
     */
    public function delete(): void
    {
        $path = $this->getTempFilePath($this->header->chunkNumber);
        if (file_exists($path)) {
            unlink($path);
        }

        if ($this->header->chunkNumber > 1) {
            $path = $this->getTempFilePath($this->header->chunkNumber - 1);
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    /**
     * Store the file.
     * 
     * @param  string  $tmpName
     * @return void
     */
    public function store(string $tmpName): void
    {
        $file = fopen($this->getTempFilePath(), 'a');

        fwrite($file, file_get_contents(
            $tmpName
        ));

        fclose($file);
    }

    /**
     * Move the file into the path.
     * 
     * @return void
     */
    public function move(): void
    {
        rename($this->getTempFilePath(), $this->getFilePath());
    }

    /**
     * Increase the chunk number of the file.
     * 
     * @return void
     */
    public function increase(): void
    {
        if ($this->header->chunkNumber > 1) {
            rename(
                $this->getTempFilePath($this->header->chunkNumber - 1), $this->getTempFilePath()
            );
        }
    }

    /**
     * Create a unique temp file name.
     * 
     * @param  null|int  $part
     * @return string
     */
    public function createTempFileName(int $part = null): string
    {
        $mixture = [
            $this->header->fileSize,
            $this->header->fileName,
            $this->header->fileIdentity
        ];

        $identity = [
            $this->getFileExtension(),
            ($part ?: $this->header->chunkNumber),
            'tmp'
        ];
    
        return implode('.', [
            hash('ripemd160', implode($mixture)), implode('.', array_filter($identity))
        ]);
    }

    /**
     * Create a random string.
     * 
     * @return string
     */
    public function createRandomString(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Create a file name.
     * 
     * @return string
     */
    public function createFileName(): string
    {
        if (is_int($this->configs['file_name'])) {
            switch ($this->configs['file_name']) {
                case Chunk::RANDOM_FILE_NAME:
                    $this->configs['file_name'] = $this->createRandomString();
                break;

                case Chunk::ORIGINAL_FILE_NAME:
                    $this->configs['file_name'] = pathinfo($this->header->fileName, PATHINFO_FILENAME);
                break;
            }
        }

        return $this->getFullFileName();
    }

    /**
     * Get temp file path.
     * 
     * @param  null|int  $part
     * @return string
     */
    public function getTempFilePath(int $part = null): string
    {
        return $this->configs['tmp_path'] . $this->createTempFileName($part);
    }

    /**
     * Get temp file path.
     * 
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->configs['path'] . $this->createFileName();
    }

    /**
     * Get file name.
     * 
     * @return string
     */
    public function getFileName(): string
    {
        return $this->configs['file_name'];
    }

    /**
     * Get file name with extension.
     * 
     * @return string
     */
    public function getFullFileName(): string
    {
        return implode('.', array_filter([$this->getFileName(), $this->getFileExtension()]));
    }

    /**
     * Get the file extension.
     * 
     * @return null|string
     */
    public function getFileExtension()
    {
        if ($this->configs['file_extension'] === Chunk::ORIGINAL_FILE_EXTENSION) {
            $extension = trim(pathinfo($this->header->fileName, PATHINFO_EXTENSION));
            $extension = empty($extension) ? null : $extension;

            $this->configs['file_extension'] = $extension;
        }

        return $this->configs['file_extension'];
    }

    /**
     * Determine whether the previous chunk exists.
     * 
     * @return null|bool
     */
    public function prevExists()
    {
        if ($this->header->chunkNumber === 1)
            return null;

        return file_exists(
            $this->getTempFilePath($this->header->chunkNumber - 1)
        );
    }

    /**
     * Determine whether the chunk exists.
     * 
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->getTempFilePath());
    }
}
