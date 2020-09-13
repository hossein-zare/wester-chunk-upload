<?php

namespace Wester\ChunkUpload\Drivers\Contracts;

interface DriverInterface
{
    public function delete(): void;
    public function store(string $tmpName): void;
    public function move(): void;
    public function increase(): void;
    public function createTempFileName(int $part = null): string;
    public function createRandomString(): string;
    public function createFileName(): string;
    public function getTempFilePath(int $part = null): string;
    public function getFilePath(): string;
    public function getFileName(): string;
    public function getFullFileName(): string;
    public function getFileExtension();
    public function prevExists();
    public function exists(): bool;
}