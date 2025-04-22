<?php


    $src= '访问时间-：'.date('Y-m-d H:i:s',time()).PHP_EOL;

        file_put_contents('text.txt',$src,FILE_APPEND);






?>