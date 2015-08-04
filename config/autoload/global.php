<?php
return array(
    'db' => array(
        'driver' => 'Pdo',
        'dsn' => 'mysql:dbname=test;host=localhost',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
        # multi db while conserving the defaut adapter (not mandatory, see there for the different syntaxes
        # http://stackoverflow.com/questions/14003187/configure-multiple-databases-in-zf2
        # https://samsonasik.wordpress.com/2013/07/27/zend-framework-2-multiple-named-db-adapter-instances-using-adapters-subkey/
        'adapters'=>array(
            'readDB' => array(
                'driver'         => 'Pdo',
                'dsn'            => 'mysql:dbname=test;host=localhost',
                'driver_options' => array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                )
            ),
            'writeDB' => array(
                'driver'         => 'Pdo',
                'dsn'            => 'mysql:dbname=test;host=localhost',
                'driver_options' => array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                )
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
        ),
        'abstract_factories' => array(
            'Zend\Db\Adapter\AdapterAbstractServiceFactory',
        ),
    )
);