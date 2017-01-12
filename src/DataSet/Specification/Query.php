<?php
/*
 * This file is part of DBUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\DbUnit\DataSet\Specification;

use PHPUnit\DbUnit\Database\DefaultConnection;
use PHPUnit\DbUnit\IDatabaseListConsumer;
use PHPUnit\DbUnit\DataSet\DefaultDataSet;
use PHPUnit_Extensions_Database_DataSet_ISpec;
use ReflectionClass;

/**
 * Creates DefaultDataSets based off of a spec string.
 *
 * This spec class requires a list of databases to be set to the object before
 * it can return a list of databases.
 *
 * The format of the spec string is as follows:
 *
 * <db label>:<schema>:<table name>:<sql>
 *
 * The db label should be equal to one of the keys in the array of databases
 * passed to setDatabases().
 *
 * The schema should be the primary schema you will be running the sql query
 * against.
 *
 * The table name should be set to what you would like the table name in the
 * dataset to be.
 *
 * The sql is the query you want to use to generate the table columns and data.
 * The column names in the table will be identical to the column aliases in the
 * query.
 */
class Query implements PHPUnit_Extensions_Database_DataSet_ISpec, IDatabaseListConsumer
{
    /**
     * @var array
     */
    protected $databases = [];

    /**
     * Sets the database for the spec
     *
     * @param array $databases
     */
    public function setDatabases(array $databases)
    {
        $this->databases = $databases;
    }

    /**
     * Creates a Default Data Set with a query table from a data set spec.
     *
     * @param  string $dataSetSpec
     * @return DefaultDataSet
     */
    public function getDataSet($dataSetSpec)
    {
        list($dbLabel, $schema, $table, $sql) = explode(':', $dataSetSpec, 4);
        $databaseInfo = $this->databases[$dbLabel];

        $pdoRflc = new ReflectionClass('PDO');
        $pdo = $pdoRflc->newInstanceArgs(explode('|', $databaseInfo));
        $dbConnection = new DefaultConnection($pdo, $schema);
        $table = $dbConnection->createQueryTable($table, $sql);

        return new DefaultDataSet([$table]);
    }
}
