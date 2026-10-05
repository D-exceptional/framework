<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use App\Database\QueryBuilder;

abstract class Model extends Database
{
    protected string $table;

    /**
     * Create a QueryBuilder for a model table.
     *
     * When no table is provided, the model's `$table` property
     * is used automatically.
     *
     * Usage:
     *
     * $this->table()
     *     ->select(['id', 'name'])
     *     ->where('status', '=', 'active')
     *     ->get();
     *
     * A different table can also be specified when needed:
     *
     * $this->table('users AS u')
     *     ->select(['u.user_id', 'u.email'])
     *     ->where('u.status', '=', 'active')
     *     ->get();
     *
     * This method is primarily used when querying a model's
     * table or another concrete database table.
     */
    protected function table(
        ?string $table = null
    ): QueryBuilder {

        return (new QueryBuilder($this->db))
            ->table($table ?? $this->table);
    }

    /**
     * Create a fresh QueryBuilder without a predefined table.
     *
     * Unlike `table()`, this method does not call `table()`
     * on the QueryBuilder. It is useful when the query will
     * define its own FROM source, especially with `fromSub()`.
     *
     * Usage:
     *
     * $customers = $this->table('order_items AS oi')
     *     ->select(['user_id'])
     *     ->groupBy('user_id');
     *
     * $total = $this->newQuery()
     *     ->fromSub($customers, 'customers')
     *     ->count();
     *
     * The returned QueryBuilder is completely fresh, so it can
     * also be used whenever a query does not naturally begin
     * from the model's own table.
     */
    public function newQuery(): QueryBuilder
    {
        return new QueryBuilder($this->db);
    }
}