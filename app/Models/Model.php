<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use App\Database\QueryBuilder;

abstract class Model extends Database
{
    protected string $table;

    protected function query(): QueryBuilder
    {
        return (new QueryBuilder($this->db))->table($this->table);
    }
}