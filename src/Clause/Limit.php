<?php
namespace Lucinda\Query\Clause;

use Lucinda\Query\Stringable;

/**
 * Encapsulates SQL LIMIT clause
 */
class Limit implements Stringable
{
    protected $limit;
    protected $offset;
    
    /**
     * Sets up clause directly from constructor.
     *
     * @param integer $limit Sets how many rows SELECT will return.
     * @param integer $offset Optionally sets offset to start limiting with.
     */
    public function __construct(int $limit, int $offset=0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * Compiles SQL clause based on data collected in class fields.
     *
     * @return string SQL that results from conversion
     */
    public function toString(): string
    {
        return $this->limit." OFFSET ".$this->offset;
    }
}
