<?php
namespace Lucinda\Query;

use Lucinda\Query\Clause\Alias;
use Lucinda\Query\Clause\Fields;
use Lucinda\Query\Clause\Join;
use Lucinda\Query\Operator\Join as JoinOperator;
use Lucinda\Query\Operator\Logical;
use Lucinda\Query\Clause\Condition;
use Lucinda\Query\Clause\OrderBy;
use Lucinda\Query\Clause\Limit;
use Lucinda\Query\Clause\Columns;

/**
 * Encapsulates SQL statement:
 *
 * SELECT {FIELDS}
 * FROM {TABLE}
 * {TYPE} JOIN ON {CONDITION}
 * ...
 * WHERE {CONDITION}
 * GROUP BY {COLUMNS}
 * HAVING {CONDITION}
 * ORDER BY {ORDER_BY}
 * LIMIT {LIMIT}
 */
class Select implements Stringable
{
    protected $isDistinct=false;
    protected $columns;
    protected $joins=[];
    protected $where;
    protected $groupBy;
    protected $having;
    protected $orderBy;
    protected $limit;
    protected $table;

    /**
     * Constructs a SELECT statement based on table name and optional alias
     * 
     * @param string $table Name of table to select from (including schema)
     * @param string $alias Optional alias to identify table with
     */
    public function __construct(string $table, string $alias="")
    {
        $this->table = ($alias?new Alias($table, $alias):$table);
    }

    /**
     * Sets statement as DISTINCT, filtering out repeating rows
     */
    public function distinct(): void
    {
        $this->isDistinct=true;
    }

    /**
     * Sets fields or columns to select
     *
     * @param string[] $columns Sets list of column names directly
     * @return Fields Object to set further fields on.
     */
    public function fields(array $columns = []): Fields
    {
        $columns = new Fields($columns);
        $this->columns = $columns;
        return $columns;
    }

    /**
     * Adds a LEFT JOIN statement
     *
     * @param string $tableName Name of table to join with
     * @param string $tableAlias Optional alias of table to join with
     * @return Join Object to set join conditions on.
     */
    public function joinLeft(string $tableName, string $tableAlias = ""): Join
    {
        $join = new Join($tableName, $tableAlias, JoinOperator::LEFT);
        $this->joins[]=$join;
        return $join;
    }

    /**
     * Adds a RIGHT JOIN statement
     *
     * @param string $tableName Name of table to join with
     * @param string $tableAlias Optional alias of table to join with
     * @return Join Object to set join conditions on.
     */
    public function joinRight(string $tableName, string $tableAlias = ""): Join
    {
        $join = new Join($tableName, $tableAlias, JoinOperator::RIGHT);
        $this->joins[]=$join;
        return $join;
    }

    /**
     * Adds a INNER JOIN statement
     *
     * @param string $tableName Name of table to join with
     * @param string $tableAlias Optional alias of table to join with
     * @return Join Object to set join conditions on.
     */
    public function joinInner(string $tableName, string $tableAlias = ""): Join
    {
        $join = new Join($tableName, $tableAlias, JoinOperator::INNER);
        $this->joins[]=$join;
        return $join;
    }

    /**
     * Adds a CROSS JOIN statement
     *
     * @param string $tableName Name of table to join with
     * @param string $tableAlias Optional alias of table to join with
     * @return Join Object to set join conditions on.
     */
    public function joinCross(string $tableName, string $tableAlias = ""): Join
    {
        $join = new Join($tableName, $tableAlias, JoinOperator::CROSS);
        $this->joins[]=$join;
        return $join;
    }

    /**
     * Sets up WHERE clause.
     *
     * @param string[string] $condition Sets condition group directly when conditions are all of equals type
     * @param Logical $logicalOperator Enum holding operator that will link conditions in group (default: AND)
     * @return Condition Object to set further conditions on.
     */
    public function where(array $condition=[], string $logicalOperator=Logical::_AND_): Condition
    {
        $where = new Condition($condition, $logicalOperator);
        $this->where=$where;
        return $where;
    }

    /**
     * Sets up GROUP BY statement
     *
     * @param string[] $columns Sets list of column names directly
     * @return Columns Object to set further fields on.
     */
    public function groupBy(array $columns = []): Columns
    {
        $columns = new Columns($columns);
        $this->groupBy = $columns;
        return $columns;
    }

    /**
     * Sets up HAVING clause.
     *
     * @param string[string] $condition Sets condition group directly when conditions are all of equals type
     * @param Logical $logicalOperator Enum holding operator that will link conditions in group (default: AND)
     * @return Condition Object to set further conditions on.
     */
    public function having(array $condition=[], string $logicalOperator=Logical::_AND_): Condition
    {
        $where = new Condition($condition, $logicalOperator);
        $this->having=$where;
        return $where;
    }

    /**
     * Sets up ORDER BY clause
     *
     * @param string[] $fields Sets list of columns to order by directly in ASC mode
     * @return OrderBy Object to set further clauses on.
     */
    public function orderBy(array $fields = []): OrderBy
    {
        $orderBy = new OrderBy($fields);
        $this->orderBy = $orderBy;
        return $orderBy;
    }

    /**
     * Sets a LIMIT clause
     *
     * @param integer $limit Sets how many rows SELECT will return.
     * @param integer $offset Optionally sets offset to start limiting with.
     */
    public function limit(int $limit, int $offset=0): void
    {
        $this->limit = new Limit($limit, $offset);
    }

    /**
     * Converts object to SQL statement.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Compiles SQL statement based on data collected in class fields.
     *
     * @return string SQL that results from conversion
     */
    public function toString(): string
    {
        $output =
                "SELECT".($this->isDistinct?" DISTINCT":"").
                "\r\n".($this->columns?$this->columns->toString():"*").
                "\r\n"."FROM ".$this->table;
        if (sizeof($this->joins)>0) {
            foreach ($this->joins as $join) {
                $output .= "\r\n".$join->toString();
            }
        }
        $output .=
                ($this->where && !$this->where->isEmpty()?"\r\nWHERE ".$this->where->toString():"").
                ($this->groupBy && !$this->groupBy->isEmpty()?"\r\nGROUP BY ".$this->groupBy->toString():"").
                ($this->having && !$this->having->isEmpty()?"\r\nHAVING ".$this->having->toString():"").
                ($this->orderBy && !$this->orderBy->isEmpty()?"\r\nORDER BY ".$this->orderBy->toString():"").
                ($this->limit?"\r\nLIMIT ".$this->limit->toString():"");
        return $output;
    }
}
