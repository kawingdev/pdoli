<?php

ini_set('display_errors', 'on');
error_reporting(E_ALL);

require_once 'DB.php';

// Basic select from ID
$pdo = new DB();
$rs = $pdo->where(['id' => 1])//Build Where clause
->limit('0,10')//Build Limit statement
->orderBy(['id' => 'DESC'])//Build ORDER BY statement
->find('testing_table_1'); //Finally execute SQL with bindValue

dump($pdo->lastSQL());
dump($rs);

//Insert
$rs = $pdo->insert('testing_table_1', [
    'name' => 'ray.kong',
    'type' => 1
]);

dump($pdo->lastSQL());
dump($rs);

//Update
$rs = $pdo->where(['name' => 'ray.kong'])//Build Where clause
->update('testing_table_1',['type'=>2]); //Finally execute SQL accepting Table as first param and set data as second param

dump($pdo->lastSQL());
dump($rs);

function dump($var)
{
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
}

