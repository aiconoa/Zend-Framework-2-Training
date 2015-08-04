<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Module_4_ZendDb\Controller;

use Module_4_ZendDb\Entity\TestRecord;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\RowGateway\RowGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\TableGateway\TableGateway;
use Zend\Debug\Debug;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{

    private $localAdapter;

    /**
     * IndexController constructor.
     */
    public function __construct()
    {
        $config = [
            'driver' => 'Pdo',
            'dsn' => 'mysql:dbname=aiconoazf2;host=localhost',
            'username' => 'root',
            'password' => '',
            'driver_options' => array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
            )
        ];
        $this->localAdapter = new \Zend\Db\Adapter\Adapter($config);

    }

    public function demoAdapterAction() {
        // QUERY PREPARATION, prefered way
        // query() is a convenience method
        // see http://framework.zend.com/manual/current/en/modules/zend.db.adapter.html#query-preparation-through-zend-db-adapter-adapter-query
        $result = $this->localAdapter->query("SELECT * FROM test WHERE id = ?", array(1));
        Debug::dump($result);
        // QUERY EXECUTION, mainly for DDL operations
        // see http://framework.zend.com/manual/current/en/modules/zend.db.adapter.html#query-execution-through-zend-db-adapter-adapter-query
        $result = $this->localAdapter->query('ALTER TABLE ADD test(title_index) ON (title)', Adapter::QUERY_MODE_EXECUTE);
        Debug::dump($result);
        // CREATING STATEMENTS
        // gives greater control over the query() convenience method
        // see http://framework.zend.com/manual/current/en/modules/zend.db.adapter.html#creating-statements
        $statement = $this->localAdapter->createStatement("SELECT * FROM test WHERE id = ?", array(1));
        $result = $statement->execute();
        Debug::dump($result);
        // CREATING A VENDOR AND PLATFORM PORTABLE API
        // see http://framework.zend.com/manual/current/en/modules/zend.db.adapter.html#examples
    }


    public function demoDefaultAdapterAction() {
        // this adapter is configured as "Zend\Db\Adapter\Adapter" service inside global.php or local.php.
        // it uses the "db" config key for the database configuration
        $defaultAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        Debug::dump($defaultAdapter);
    }

    public function multiDBAdapterAction() {
        // either create manually adapters
        // OR manually create services
        // OR use the Zend\Db\Adapter\AdapterAbstractServiceFactory as we are doing here
        // multi db while conserving the defaut adapter (not mandatory, see there for the different syntaxes
        // http://stackoverflow.com/questions/14003187/configure-multiple-databases-in-zf2
        //  https://samsonasik.wordpress.com/2013/07/27/zend-framework-2-multiple-named-db-adapter-instances-using-adapters-subkey/
        $readDB = $this->getServiceLocator()->get('readDB');
        Debug::dump($readDB);
        $writeDB = $this->getServiceLocator()->get('writeDB');
        Debug::dump($writeDB);
    }

    public function demoResultSetAction() {
        $result = $this->localAdapter->query("SELECT * FROM test")->execute();

        // when using query() $result is generally a ResultInterface Object
        // see http://framework.zend.com/manual/current/en/modules/zend.db.result-set.html#quickstart
        if($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            return new ViewModel(["result" => $resultSet]);
        }

        // for most other purposes, ResultSet will be used
        // see http://framework.zend.com/manual/current/en/modules/zend.db.result-set.html#zend-db-resultset-resultset-and-zend-db-resultset-abstractresultset
    }

    public function demoSqlAction() {
        // see http://framework.zend.com/manual/current/en/modules/zend.db.sql.html
        $sql = new Sql($this->localAdapter);
        $select = $sql->select(); // @return Zend\Db\Sql\Select
        $select->from('test'); // see the Select API http://framework.zend.com/manual/current/en/modules/zend.db.sql.html#zend-db-sql-select
        $select->where(array('id' => 2));

        //Either prepare
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        foreach($results as $row) {
            Debug::dump($row);
        }

        // Or execute
        $sqlString = $sql->buildSqlString($select);
        print_r($sqlString);
        $results = $this->localAdapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        foreach($results as $row) {
            Debug::dump($row);
        }

        // TODO more examples with UPDATE, INSERT, DELETE
        // TODO more examples with PREDICATES http://framework.zend.com/manual/current/en/modules/zend.db.sql.html#zend-db-sql-where-zend-db-sql-having
        // TODO more examples with Ddl http://framework.zend.com/manual/current/en/modules/zend.db.sql.ddl.html

        exit(0);
    }

    public function demoTableGatewayAction() {
        // see http://framework.zend.com/manual/current/en/modules/zend.db.table-gateway.html
        $testTable = new TableGateway('test', $this->localAdapter);

        // multiple rows
        // select() takes the same arguments as Zend\Db\Sql\Select::where() plus can consume a closure which will be passed a Select object
        $rowset = $testTable->select(array('type' => 'one'));
        echo 'Rows of type one: ';
        foreach ($rowset as $testRow) {
            Debug::dump($testRow);
        }

        // when one row is expected
        $rowset = $testTable->select(array('id' => 2));
        $rowId2 = $rowset->current();
        Debug::dump($rowId2);

        // TODO select() can also consume a closure and selectWith() can consume a Select object

        // TODO Features API for extending the base functionnalities without having to polymorphically extend the base class
        // see http://framework.zend.com/manual/current/en/modules/zend.db.table-gateway.html#basic-usage
        exit(0);
    }

    public function demoRowGatewayStandaloneAction() {
        // query the database
        $resultSet = $this->localAdapter->query('SELECT * FROM test WHERE id = ?', array(2));

        // get array of data
        $rowData = $resultSet->current()->getArrayCopy();

        // row gateway
        $rowGateway = new RowGateway('id', 'test', $this->localAdapter);
        $rowGateway->populate($rowData, true);

        $rowGateway->title = 'New title - RowGateway';
        $rowGateway->save();

        // or delete this row:
        // $rowGateway->delete();
    }

    public function demoRowGatewayWithTableGatewayAction() {
        $table = new TableGateway('test', $this->localAdapter, new RowGatewayFeature('id'));
        $results = $table->select(array('id' => 2));

        $testRow = $results->current();
        $testRow->title = 'New title - TableGateway';
        $testRow->save();
    }

    public function demoRowGatewayActiveRecordAction() {
        $table = new TableGateway('test', $this->localAdapter, new RowGatewayFeature(new TestRecord($this->localAdapter)));
        $results = $table->select(array('id' => 2));
        $testRow = $results->current();
        $testRow->title = 'New title - ActiveRecord';
        $testRow->save();


        $newRow = new TestRecord($this->localAdapter);
        $newRow->title = "Active Record new Row " . mt_rand();
        $newRow->created_on = date("Y-m-d H:i:s"); // "2015/01/01";
        $newRow->type = "three";
        $newRow->save();
    }


    public function demoMetadataAction() {
        // TODO see http://framework.zend.com/manual/current/en/modules/zend.db.metadata.html
    }

}
