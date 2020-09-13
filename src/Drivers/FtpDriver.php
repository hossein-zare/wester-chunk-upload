<?php

namespace Wester\ChunkUpload\Drivers;

use Wester\ChunkUpload\Chunk;
use Wester\ChunkUpload\Header;
use Wester\ChunkUpload\Drivers\Contracts\DriverInterface;
use Wester\ChunkUpload\Drivers\Exceptions\FtpDriverException;

class FtpDriver implements DriverInterface
{
    /**
     * The configs.
     * 
     * @var array
     */
    public $configs;

    /**
     * The headers.
     * 
     * @var \Wester\ChunkUpload\Header
     */
    public $header;

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
     * @param  \Wester\ChunkUpload\Header  $header
     * @return void
     */
    public function __construct(array $configs, Header $header)
    {
        $this->configs = $configs;
        $this->header = $header;

        $this->createConnection()->login();
    }

    /**
     * Create a ftp connection.
     * 
     * @return \Wester\ChunkUpload\Drivers\FtpDriver
     */
    private function createConnection()
    {
        if (! $this->connection = @ftp_connect($this->configs['ftp_driver']['server']))
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
        if (! @ftp_login($this->connection, $this->configs['ftp_driver']['username'], $this->configs['ftp_driver']['password']))
            throw new FtpDriverException("FTP couldn't login to the server.");
    }

    /**
     * Delete a temp chunk.
     * 
     * @return void
     */
    public function delete(): void
    {
        $path = $this->getTempFilePath($this->header->chunkNumber);
        if (ftp_size($this->connection, $path) > -1) {
            ftp_delete($this->connection, $path);
        }

        if ($this->header->chunkNumber > 1) {
            $path = $this->getTempFilePath($this->header->chunkNumber - 1);
            if (ftp_size($this->connection, $path) > -1) {
                ftp_delete($this->connection, $path);
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
        $size = ftp_size($this->connection, $this->getTempFilePath());
        if (! ftp_append($this->connection, $this->getTempFilePath(), $tmpName)) {
            $this->close();
            throw new FtpDriverException("FTP Couldn't append to the file.");
        }
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
     * Move the file into the path.
     * 
     * @return void
     */
    public function move(): void
    {
        ftp_rename($this->connection, $this->getTempFilePath(), $this->getFilePath());
    }

    /**
     * Increase the chunk number of the file.
     * 
     * @return void
     */
    public function increase(): void
    {
        if ($this->header->chunkNumber > 1) {
            ftp_rename(
                $this->connection, $this->getTempFilePath($this->header->chunkNumber - 1), $this->getTempFilePath()
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
        return $this->configs['ftp_driver']['tmp_path'] . $this->createTempFileName($part);
    }

    /**
     * Get temp file path.
     * 
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->configs['ftp_driver']['path'] . $this->createFileName();
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

        return ftp_size(
            $this->connection, $this->getTempFilePath($this->header->chunkNumber - 1)
        ) > -1;
    }

    /**
     * Determine whether the chunk exists.
     * 
     * @return bool
     */
    public function exists(): bool
    {
        return ftp_size($this->connection, $this->getTempFilePath()) > -1;
    }
}
