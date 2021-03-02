<?php

namespace Wester\ChunkUpload\Drivers;

use Wester\ChunkUpload\Chunk;
use Wester\ChunkUpload\Drivers\Contracts\DriverInterface;
use Wester\ChunkUpload\Exceptions\MainException;

class LocalDriver implements DriverInterface
{
    /**
     * The chunk.
     * 
     * @var \Wester\ChunkUpload\Chunk
     */
    public $chunk;
    
    /**
     * Create a new instance.
     * 
     * @param  \Wester\ChunkUpload\Chunk  $chunk
     * @return void
     */
    public function __construct(Chunk $chunk)
    {
        $this->chunk = $chunk;
    }

    /**
     * Open the connection.
     * 
     * @return void
     */
    public function open()
    {
        //
    }

    /**
     * Close the connection.
     * 
     * @return void
     */
    public function close()
    {
        //
    }

    /**
     * Store the file.
     * 
     * @param  string  $fileName
     * @return void
     */
    public function store($fileName)
    {
        try {
            $file = fopen($this->chunk->getTempFilePath(), 'a');

            fwrite($file, file_get_contents(
                $fileName
            ));

            fclose($file);
        } catch (\Exception $e) {
            throw new MainException($e);
        }
    }

    /**
     * Delete a temp chunk.
     * 
     * @return void
     */
    public function delete()
    {
        try {
            $path = $this->chunk->getTempFilePath($this->chunk->header->chunkNumber);
            if (file_exists($path)) {
                unlink($path);
            }

            if ($this->chunk->header->chunkNumber > 1) {
                $path = $this->chunk->getTempFilePath($this->chunk->header->chunkNumber - 1);
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        } catch (\Exception $e) {
            throw new MainException($e);
        }
    }

    /**
     * Move the file into the path.
     * 
     * @return void
     */
    public function move()
    {
        try {
            rename($this->chunk->getTempFilePath(), $this->chunk->getFilePath());
        } catch (\Exception $e) {
            throw new MainException($e);
        }
    }

    /**
     * Increase the chunk number of the file.
     * 
     * @return void
     */
    public function increase()
    {
        try {
            if ($this->chunk->header->chunkNumber > 1) {
                rename(
                    $this->chunk->getTempFilePath($this->chunk->header->chunkNumber - 1), $this->chunk->getTempFilePath()
                );
            }
        } catch (\Exception $e) {
            throw new MainException($e);
        }
    }

    /**
     * Determine whether the previous chunk exists.
     * 
     * @return null|bool
     */
    public function prevExists()
    {
        if ($this->chunk->header->chunkNumber === 1)
            return null;

        return file_exists(
            $this->chunk->getTempFilePath($this->chunk->header->chunkNumber - 1)
        );
    }

    /**
     * Determine whether the chunk exists.
     * 
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->chunk->getTempFilePath());
    }
}
