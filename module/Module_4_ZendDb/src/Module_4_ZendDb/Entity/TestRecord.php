<?php
/**
 * Created by PhpStorm.
 * User: T
 * Date: 03/08/2015
 * Time: 10:36
 */

namespace Module_4_ZendDb\Entity;


use Zend\Db\Adapter\Adapter;
use Zend\Db\RowGateway\RowGatewayInterface;
use Zend\Db\Sql\Sql;
use Zend\Debug\Debug;
use Zend\Stdlib\ArrayObject;

/**
 * Class TestRecord
 * ActiveRecord approach
 * @see http://framework.zend.com/manual/current/en/modules/zend.db.row-gateway.html#activerecord-style-objects
 * @package Module_4_ZendDb\src\Module_4_ZendDb\Entity
 */
class TestRecord extends ArrayObject // Should be compliant with ArrayObject to be used as a RowGatewayFeature
                implements RowGatewayInterface

{
   const TABLE = "test";

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * TestRecord constructor.
     * @param $adapter
     * @param $array
     */
    public function __construct(Adapter $adapter)
    {
        // Allow accessing properties as either array keys or object properties:
        parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);  // http://stackoverflow.com/questions/14610307/spl-arrayobject-arrayobjectstd-prop-list
        $this->adapter = $adapter;
    }

    public function save()
    {
        $sql = new Sql($this->adapter, TestRecord::TABLE);

        $sqlObject = null;

        if ( $this->id) {
            // update existing
            $sqlObject = $sql->update()
                ->set([
                    "title" => $this->title,
                    "created_on" => $this->created_on,
                    "type" => $this->type
                ])
                ->where([
                    "id" => $this->id
                ]);
        } else {
            // insert new
            $sqlObject = $sql->insert()
                ->columns(["title", "created_on", "type"])
                ->values([
                    "title" => $this->title,
                    "created_on" => $this->created_on,
                    "type" => $this->type
                ]);
        }

        $statement = $sql->prepareStatementForSqlObject($sqlObject);
        $results = $statement->execute();
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }


}