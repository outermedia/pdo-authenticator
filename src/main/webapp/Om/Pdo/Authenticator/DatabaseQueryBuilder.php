<?php
/**
 * User: ballmann
 * Date: 4/15/16
 */

namespace Om\Pdo\Authenticator;

/**
 * Class DatabaseQueryBuilder
 * @package Om\Pdo\Authenticator
 *
 * Helps to create sql statements.
 */
class DatabaseQueryBuilder
{
    /**
     * @var string - the table name to query
     */
    protected $tablename;
    
    /**
     * @var array - all fields to return
     */
    protected $fieldsToReturn;
    
    /**
     * @var array - all query conditions
     */
    protected $conditions;
    
    /**
     * @var string - created sql statement
     */
    protected $sql;

    /**
     * DatabaseQueryBuilder constructor.
     */
    public function __construct()
    {
        $this->tablename = 'Unknown';
        $this->fieldsToReturn = [];
        $this->conditions = [];
    }

    /**
     * Get the created sql string.
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Set the sql string.
     * @param string $sql
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
    }

    /**
     * Create a new instance.
     * @return DatabaseQueryBuilder
     */
    public static function newBuilder()
    {
        return new DatabaseQueryBuilder();
    }

    /**
     * Set the table's name to query.
     * @param string $name - is the table's name
     * @return $this
     */
    public function table($name)
    {
        $this->tablename = $name;
        return $this;
    }

    /**
     * Declare which field to return.
     * @param string $name - is the field's name
     * @return $this
     */
    public function returnField($name)
    {
        $this->fieldsToReturn[] = $name;
        return $this;
    }

    /**
     * Add one condition of type <field> = <expr>. Multiple conditions are 'and'ed.
     * @param string $fieldName - is the field's name to compare
     * @param string $expr - is the expected value
     * @return $this
     */
    public function ifEqual($fieldName, $expr)
    {
        $this->conditions[] = ['condition' => $fieldName . '=:var' . count($this->conditions), 'value' => $expr];
        return $this;
    }

    /**
     * Create a PDO statement.
     * @param \PDO $pdo - a PDO
     * @return $this
     */
    public function buildSql()
    {
        $sql = 'select ';
        if (count($this->fieldsToReturn) == 0) {
            $sql .= '*';
        } else {
            $sql .= implode(",", $this->fieldsToReturn);
        }
        $sql .= ' from ';
        $sql .= $this->tablename;
        $conditionNr = count($this->conditions);
        if ($conditionNr > 0) {
            $sql .= ' where ';
            for ($pos = 0; $pos < $conditionNr; $pos++) {
                $condition = $this->conditions[$pos];
                if ($pos > 0) $sql .= ' and ';
                $sql .= $condition['condition'];
            }
        }
        $this->sql = $sql;
        return $this;
    }

    /**
     * Create a PDO statement and set all condition's expressions for the previously build sql. A call of buildSql
     * is required before.
     * @param \PDO $pdo 
     * @return \PDOStatement
     */
    public function buildPdoStatement($pdo)
    {
        $stmt = $pdo->prepare($this->sql);
        $conditionNr = count($this->conditions);
        if ($conditionNr > 0) {
            for ($pos = 0; $pos < $conditionNr; $pos++) {
                $condition = $this->conditions[$pos];
                $stmt->bindParam(':var' . $pos, $condition['value']);
            }
        }
        return $stmt;
    }
}