<?php

namespace App\Contracts;

interface JobInterface
{
    public function handle(): void;
}