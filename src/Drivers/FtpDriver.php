<?php

namespace Wester\ChunkUpload\Drivers;

use Wester\ChunkUpload\Chunk;
use Wester\ChunkUpload\Header;
use Wester\ChunkUpload\Drivers\Contracts\DriverInterface;
use Wester\ChunkUpload\Drivers\Exceptions\FtpDriverException;
use Wester\ChunkUpload\Exceptions\MainException;

class FtpDriver implements DriverInterface
{
    /**
     * The chunk.
     * 
     * @var \Wester\ChunkUpload\Chunk
     */
    public $chunk;

    /**
     * The connection.
     * 
     * @var mixed
     */
    private $connection;

    /**
     * Create a new instance.
     * 
     * @param  array  $configs
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
        $this->createConnection()->login();
    }

    /**
     * Close the connection.
     * 
     * @return void
     */
    public function close()
    {
        try {
            ftp_close($this->connection);
        } catch (\Exception $e) {
            throw new MainException($e);
        }
    }

    /**
     * Create a ftp connection.
     * 
     * @return \Wester\ChunkUpload\Drivers\FtpDriver
     */
    private function createConnection()
    {
        try {
            if (! $this->connection = @ftp_connect($this->chunk->configs['ftp_driver']['server']))
                throw new FtpDriverException("FTP couldn't connect to the server.");

            return $this;
        } catch (\Exception $e) {
            throw new MainException($e);
        }
    }

    /**
     * Login to the ftp account.
     * 
     * @return void
     */
    private function login()
    {
        try {
            if (! @ftp_login($this->connection, $this->chunk->configs['ftp_driver']['username'], $this->chunk->configs['ftp_driver']['password']))
                throw new FtpDriverException("FTP couldn't login to the server.");
        } catch (\Exception $e) {
            throw new MainException($e);
        }
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
            if (! ftp_append($this->connection, $this->chunk->getTempFilePath(), $fileName)) {
                $this->close();

                throw new FtpDriverException("FTP Couldn't append to the file.");
            }
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
            if (ftp_size($this->connection, $path) > -1) {
                ftp_delete($this->connection, $path);
            }

            if ($this->chunk->header->chunkNumber > 1) {
                $path = $this->chunk->getTempFilePath($this->chunk->header->chunkNumber - 1);
                if (ftp_size($this->connection, $path) > -1) {
                    ftp_delete($this->connection, $path);
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
            ftp_rename($this->connection, $this->chunk->getTempFilePath(), $this->chunk->getFilePath());
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
                ftp_rename(
                    $this->connection, $this->chunk->getTempFilePath($this->chunk->header->chunkNumber - 1), $this->chunk->getTempFilePath()
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
        try {
            if ($this->chunk->header->chunkNumber === 1)
                return null;

            return ftp_size(
                $this->connection, $this->chunk->getTempFilePath($this->chunk->header->chunkNumber - 1)
            ) > -1;
        } catch (\Exception $e) {
            throw new MainException($e);
        }
    }

    /**
     * Determine whether the chunk exists.
     * 
     * @return bool
     */
    public function exists()
    {
        try {
            return ftp_size($this->connection, $this->chunk->getTempFilePath()) > -1;
        } catch (\Exception $e) {
            throw new MainException($e);
        }
    }
}
