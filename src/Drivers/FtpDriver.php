<?php

namespace Wester\ChunkUpload\Drivers;

use Wester\ChunkUpload\Chunk;
use Wester\ChunkUpload\Header;
use Wester\ChunkUpload\Drivers\Contracts\DriverInterface;
use Wester\ChunkUpload\Drivers\Exceptions\FtpDriverException;

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
        ftp_close($this->connection);
    }

    /**
     * Create a ftp connection.
     * 
     * @return \Wester\ChunkUpload\Drivers\FtpDriver
     */
    private function createConnection()
    {
        if (! $this->connection = @ftp_connect($this->chunk->configs['ftp_driver']['server']))
            throw new FtpDriverException("FTP couldn't connect to the server.");

        return $this;
    }

    /**
     * Login to the ftp account.
     * 
     * @return void
     */
    private function login()
    {
        if (! @ftp_login($this->connection, $this->chunk->configs['ftp_driver']['username'], $this->chunk->configs['ftp_driver']['password']))
            throw new FtpDriverException("FTP couldn't login to the server.");
    }

    /**
     * Store the file.
     * 
     * @param  string  $fileName
     * @return void
     */
    public function store($fileName)
    {
        if (! ftp_append($this->connection, $this->chunk->getTempFilePath(), $fileName)) {
            $this->close();

            throw new FtpDriverException("FTP Couldn't append to the file.");
        }
    }

    /**
     * Delete a temp chunk.
     * 
     * @return void
     */
    public function delete()
    {
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
    }

    /**
     * Move the file into the path.
     * 
     * @return void
     */
    public function move()
    {
        ftp_rename($this->connection, $this->chunk->getTempFilePath(), $this->chunk->getFilePath());
    }

    /**
     * Increase the chunk number of the file.
     * 
     * @return void
     */
    public function increase()
    {
        if ($this->chunk->header->chunkNumber > 1) {
            ftp_rename(
                $this->connection, $this->chunk->getTempFilePath($this->chunk->header->chunkNumber - 1), $this->chunk->getTempFilePath()
            );
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

        return ftp_size(
            $this->connection, $this->chunk->getTempFilePath($this->chunk->header->chunkNumber - 1)
        ) > -1;
    }

    /**
     * Determine whether the chunk exists.
     * 
     * @return bool
     */
    public function exists()
    {
        return ftp_size($this->connection, $this->chunk->getTempFilePath()) > -1;
    }
}
