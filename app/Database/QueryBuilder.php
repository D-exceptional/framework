<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use Exception;

class QueryBuilder
{
    protected string $table;

    protected array $selects = ['*'];

    protected array $wheres = [];

    protected array $joins = [];

    protected array $orders = [];

    protected array $groups = [];

    protected array $bindings = [];

    protected ?int $limit = null;

    protected ?int $offset = null;

    public function __construct(
        protected PDO $db
    ) {}

    // =========================================
    // TABLE
    // =========================================

    public function table(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    // =========================================
    // SELECT
    // =========================================

    public function select(array|string $columns): self
    {
        $this->selects = is_array($columns)
            ? $columns
            : [$columns];

        return $this;
    }

    // =========================================
    // WHERE
    // =========================================

    public function where(
        string $column,
        string $operator,
        mixed $value
    ): self {

        $this->wheres[] = [
            'boolean'  => 'AND',
            'condition' => "{$column} {$operator} ?"
        ];

        $this->bindings[] = $value;

        return $this;
    }

    public function orWhere(
        string $column,
        string $operator,
        mixed $value
    ): self {

        $this->wheres[] = [
            'boolean'  => 'OR',
            'condition' => "{$column} {$operator} ?"
        ];

        $this->bindings[] = $value;

        return $this;
    }

    public function whereIn(
        string $column,
        array $values
    ): self {

        $placeholders = implode(
            ', ',
            array_fill(0, count($values), '?')
        );

        $this->wheres[] = [
            'boolean' => 'AND',
            'condition' => "{$column} IN ({$placeholders})"
        ];

        $this->bindings = array_merge(
            $this->bindings,
            $values
        );

        return $this;
    }

    public function whereNotIn(
        string $column,
        array $values
    ): self {

        $placeholders = implode(
            ', ',
            array_fill(0, count($values), '?')
        );

        $this->wheres[] = [
            'boolean' => 'AND',
            'condition' => "{$column} NOT IN ({$placeholders})"
        ];

        $this->bindings = array_merge(
            $this->bindings,
            $values
        );

        return $this;
    }

    public function whereNull(
        string $column
    ): self {

        $this->wheres[] = [
            'boolean' => 'AND',
            'condition' => "{$column} IS NULL"
        ];

        return $this;
    }

    public function whereNotNull(
        string $column
    ): self {

        $this->wheres[] = [
            'boolean' => 'AND',
            'condition' => "{$column} IS NOT NULL"
        ];

        return $this;
    }

    public function whereLike(
        string $column,
        string $value
    ): self {

        return $this->where(
            $column,
            'LIKE',
            $value
        );
    }

    public function whereBetween(
        string $column,
        array $values
    ): self {

        if (count($values) !== 2) {
            throw new Exception(
                'whereBetween requires exactly 2 values.'
            );
        }

        $this->wheres[] = [
            'boolean' => 'AND',
            'condition' => "{$column} BETWEEN ? AND ?"
        ];

        $this->bindings[] = $values[0];
        $this->bindings[] = $values[1];

        return $this;
    }

    // =========================================
    // CONDITIONAL CALLBACKS
    // =========================================

    public function when(
        mixed $condition,
        callable $callback
    ): self {

        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    // =========================================
    // JOINS
    // =========================================

    public function join(
        string $table,
        string $first,
        string $operator,
        string $second,
        string $type = 'INNER'
    ): self {

        $this->joins[] =
            "{$type} JOIN {$table}
             ON {$first} {$operator} {$second}";

        return $this;
    }

    public function leftJoin(
        string $table,
        string $first,
        string $operator,
        string $second
    ): self {

        return $this->join(
            $table,
            $first,
            $operator,
            $second,
            'LEFT'
        );
    }

    public function rightJoin(
        string $table,
        string $first,
        string $operator,
        string $second
    ): self {

        return $this->join(
            $table,
            $first,
            $operator,
            $second,
            'RIGHT'
        );
    }

    // =========================================
    // ORDERING
    // =========================================

    public function orderBy(
        string $column,
        string $direction = 'ASC'
    ): self {

        $direction =
            strtoupper($direction) === 'DESC'
            ? 'DESC'
            : 'ASC';

        $this->orders[] =
            "{$column} {$direction}";

        return $this;
    }

    public function latest(
        string $column = 'created_at'
    ): self {

        return $this->orderBy(
            $column,
            'DESC'
        );
    }

    public function oldest(
        string $column = 'created_at'
    ): self {

        return $this->orderBy(
            $column,
            'ASC'
        );
    }

    // =========================================
    // GROUPING
    // =========================================

    public function groupBy(
        string|array $columns
    ): self {

        $columns = is_array($columns)
            ? $columns
            : [$columns];

        $this->groups = array_merge(
            $this->groups,
            $columns
        );

        return $this;
    }

    // =========================================
    // LIMIT & PAGINATION
    // =========================================

    public function limit(
        int $limit
    ): self {

        $this->limit = $limit;

        return $this;
    }

    public function offset(
        int $offset
    ): self {

        $this->offset = $offset;

        return $this;
    }

    public function paginate(
        int $page = 1,
        int $limit = 20
    ): self {

        $this->limit($limit);

        $this->offset(
            ($page - 1) * $limit
        );

        return $this;
    }

    // =========================================
    // INSERT
    // =========================================

    public function insert(
        array $data
    ): bool {

        $columns = array_keys($data);

        $placeholders = implode(
            ', ',
            array_fill(0, count($columns), '?')
        );

        $sql = "INSERT INTO {$this->table} (" .
            implode(', ', $columns) .
            ") VALUES ({$placeholders})";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(
            array_values($data)
        );
    }

    public function insertGetId(
        array $data
    ): int {

        $this->insert($data);

        return (int) $this->db->lastInsertId();
    }

    // =========================================
    // UPDATE
    // =========================================

    public function update(
        array $data
    ): bool {

        $sets = [];

        $updateBindings = [];

        foreach ($data as $column => $value) {

            $sets[] = "{$column} = ?";

            $updateBindings[] = $value;
        }

        $sql = "UPDATE {$this->table}
                SET " . implode(', ', $sets);

        $sql .= $this->compileWhere();

        $stmt = $this->db->prepare($sql);

        $success = $stmt->execute(
            array_merge(
                $updateBindings,
                $this->bindings
            )
        );

        $this->reset();

        return $success;
    }

    // =========================================
    // DELETE
    // =========================================

    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";

        $sql .= $this->compileWhere();

        $stmt = $this->execute($sql);

        $this->reset();

        return $stmt->rowCount() > 0;
    }

    // =========================================
    // INCREMENT / DECREMENT
    // =========================================

    public function increment(
        string $column,
        int $amount = 1
    ): bool {

        $sql = "UPDATE {$this->table}
                SET {$column} =
                {$column} + {$amount}";

        $sql .= $this->compileWhere();

        $stmt = $this->execute($sql);

        $this->reset();

        return $stmt->rowCount() > 0;
    }

    public function decrement(
        string $column,
        int $amount = 1
    ): bool {

        $sql = "UPDATE {$this->table}
                SET {$column} =
                {$column} - {$amount}";

        $sql .= $this->compileWhere();

        $stmt = $this->execute($sql);

        $this->reset();

        return $stmt->rowCount() > 0;
    }

    // =========================================
    // RETRIEVAL
    // =========================================

    public function get(): array
    {
        $sql = $this->buildQuery();

        $stmt = $this->execute($sql);

        $results = $stmt->fetchAll();

        $this->reset();

        return $results;
    }

    public function first(): ?array
    {
        $this->limit(1);

        $sql = $this->buildQuery();

        $stmt = $this->execute($sql);

        $result = $stmt->fetch();

        $this->reset();

        return $result ?: null;
    }

    public function firstOrFail(): array
    {
        $result = $this->first();

        if (!$result) {
            throw new Exception(
                'Record not found.'
            );
        }

        return $result;
    }

    public function find(
        int|string $id,
        string $column = 'id'
    ): ?array {

        return $this
            ->where($column, '=', $id)
            ->first();
    }

    public function findOrFail(
        int|string $id,
        string $column = 'id'
    ): array {

        return $this
            ->where($column, '=', $id)
            ->firstOrFail();
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    // =========================================
    // AGGREGATES
    // =========================================

    public function count(): int
    {
        return (int) $this->aggregate(
            'COUNT(*)'
        );
    }

    public function sum(
        string $column
    ): int|float {

        return $this->aggregate(
            "SUM({$column})"
        );
    }

    public function avg(
        string $column
    ): int|float {

        return $this->aggregate(
            "AVG({$column})"
        );
    }

    public function min(
        string $column
    ): int|float {

        return $this->aggregate(
            "MIN({$column})"
        );
    }

    public function max(
        string $column
    ): int|float {

        return $this->aggregate(
            "MAX({$column})"
        );
    }

    protected function aggregate(
        string $expression
    ): mixed {

        $sql = "SELECT {$expression}
                as aggregate
                FROM {$this->table}";

        $sql .= $this->compileWhere();

        $stmt = $this->execute($sql);

        $result = $stmt->fetch();

        $this->reset();

        return $result['aggregate'];
    }

    // =========================================
    // TRANSACTIONS
    // =========================================

    public function transaction(
        callable $callback
    ): mixed {

        try {

            $this->db->beginTransaction();

            $result = $callback($this);

            $this->db->commit();

            return $result;

        } catch (\Throwable $e) {

            $this->db->rollBack();

            throw $e;
        }
    }

    // =========================================
    // DEBUGGING
    // =========================================

    public function toSql(): string
    {
        return $this->buildQuery();
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    // =========================================
    // QUERY COMPILERS
    // =========================================

    protected function buildQuery(): string
    {
        return "SELECT " .
            implode(', ', $this->selects) .
            " FROM {$this->table}" .
            $this->compileJoin() .
            $this->compileWhere() .
            $this->compileOrder() .
            $this->compileLimit() .
            $this->compileGroup();
    }

    protected function compileWhere(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $sql = ' WHERE ';

        foreach ($this->wheres as $index => $where) {

            if ($index === 0) {
                $sql .= $where['condition'];
                continue;
            }

            $sql .=
                " {$where['boolean']}
                  {$where['condition']}";
        }

        return $sql;
    }

    protected function compileJoin(): string
    {
        return empty($this->joins)
            ? ''
            : ' ' . implode(' ', $this->joins);
    }

    protected function compileOrder(): string
    {
        return empty($this->orders)
            ? ''
            : ' ORDER BY ' .
                implode(', ', $this->orders);
    }

    protected function compileLimit(): string
    {
        $sql = '';

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    protected function compileGroup(): string
    {
        return empty($this->groups)
            ? ''
            : ' GROUP BY ' .
                implode(', ', $this->groups);
    }

    // =========================================
    // EXECUTION
    // =========================================

    protected function execute(
        string $sql
    )
    {
        $stmt = $this->db->prepare($sql);

        $stmt->execute($this->bindings);

        return $stmt;
    }

    // =========================================
    // RESET STATE
    // =========================================

    protected function reset(): void
    {
        $this->selects = ['*'];

        $this->wheres = [];

        $this->joins = [];

        $this->orders = [];

        $this->bindings = [];

        $this->groups = [];

        $this->limit = null;

        $this->offset = null;
    }
}