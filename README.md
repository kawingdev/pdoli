# lightweightPDO

lightweight class for MySQL access using PDO

Easy to use and setup  

It could be easy to implement into lightweight ORM in PDO

## Basic configuration

Inlcude DB.php in your projects
```php
require_once 'DB.php';
```

Edit DB.php to fit your database access configs

```php
/** Database Name */
define('DB_NAME', 'github_testing');
/** Database Host */
define('DB_HOST', 'localhost');
/** Database Port */
define('DB_PORT', 3306);
/** Database User */
define('DB_USER', 'github.tester');
/** Database Password */
define('DB_PASSWORD', '!Qwertyuiop234567890');
```

# Usage


### Select 
```php
//Create new DB object
$pdo = new Pdoli();
//Or calling static function 
\Pdoli::conn()

//Basic select clause
$result = $pdo->where(['id' => 1]) //Build Where clause
    ->limit('0,10') //Build Limit statement
    ->orderBy(['id'=>'DESC']) //Build ORDER BY statement
    ->find('testing_table_1'); //Finally execute SQL with bindValue

//Return result in array or empty array if no data found
var_dump($result)

array(1) {
  [0]=>
  array(3) {
    ["id"]=>
    string(1) "1"
    ["name"]=>
    string(8) "ray.kong"
    ["type"]=>
    string(1) "1"
  }
}

//executed SQL with bindValue in PDO
var_dump($pdo->lastSQL());

"SELECT * 
    FROM testing_table_1 
    WHERE id = :id_0 
    ORDER BY id DESC 
    LIMIT 0,10"
```

### Insert 
```php
//Create new DB object
$pdo = new Pdoli();

//Basic insert
$result = $pdo->insert('testing_table_1', [
    'name' => 'ray.kong',
    'type' => 1
]);

//executed SQL with bindValue in PDO
"INSERT INTO `testing_table_1` 
    (name, type) 
    VALUES (:name0, :type1);"
    
//Returning affected row
```

### Update 
```php
//Create new DB object
$pdo = new Pdoli();

//Basic update
$result = $pdo->where(['name' => 'ray.kong'])//Build Where clause
    ->update('testing_table_1',['type'=>2]); //Finally execute SQL accepting Table as first param and set data as second param

//executed SQL with bindValue in PDO
"UPDATE `testing_table_1` 
    SET type = :type_1 
    WHERE name = :name_0"
    
//Returning affected row
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://github.com/kawing1989/lightweightPDO/blob/master/LICENSE)