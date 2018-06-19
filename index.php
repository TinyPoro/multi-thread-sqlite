<?php
/**
 * Created by PhpStorm.
 * User: idist
 * Date: 19/06/2018
 * Time: 15:23
 */
?>
<?php
class MyDB extends SQLite3
{
    function __construct()
    {
        $this->open('demo.sqlite');
    }
}
$db = new MyDB();
if(!$db){
    echo $db->lastErrorMsg();
} else {
    echo "Opened database successfully\n";
}
?>