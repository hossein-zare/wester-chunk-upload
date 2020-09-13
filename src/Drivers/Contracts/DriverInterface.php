<?php

namespace Wester\ChunkUpload\Drivers\Contracts;

interface DriverInterface
{
    public function open();
    public function close();
    public function store($fileName);
    public function delete();
    public function move();
    public function increase();
    public function prevExists();
    public function exists();
}