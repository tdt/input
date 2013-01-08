<?php

AutoInclude::bulkRegister(array(
    "CallbackIterator" => "includes/Phive/CallbackIterator.php",
    "AbstractQueue" => "includes/Phive/Queue/AbstractQueue.php",
    "InMemoryQueue" => "includes/Phive/Queue/InMemoryQueue.php",
    "QueueInterface" => "includes/Phive/Queue/QueueInterface.php",
    "AbstractPdoQueue" => "includes/Phive/Queue/Db/Pdo/AbstractPdoQueue.php",
    "MysqlQueue" => "includes/Phive/Queue/Db/Pdo/MysqlQueue.php",
    "PgsqlQueue" => "includes/Phive/Queue/Db/Pdo/PgsqlQueue.php",
    "SqliteQueue" => "includes/Phive/Queue/Db/Pdo/SqliteQueue.php",
    "MongoDbQueue" => "includes/Phive/Queue/MongoDb/MongoDbQueue.php",
    "RedisQueue" => "includes/Phive/Queue/Redis/RedisQueue.php")
);
