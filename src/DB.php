<?php
/**
 * Created by PhpStorm.
 * User: idist
 * Date: 19/06/2018
 * Time: 15:47
 */

namespace IDisT\SQLiteMultiThread;


class DB extends \SQLite3
{
    public function __construct($filename)
    {

//        echo $filename."\n";
        $this->open($filename);
    }

}