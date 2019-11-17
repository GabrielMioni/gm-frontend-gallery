<?php

try {
    spl_autoload_register(function ($className){
        $namespaceParts = explode('\\', $className);
        $path = 'Controllers/' . $namespaceParts[count($namespaceParts)-1] . '.php';

        if (file_exists($path)) {
            require_once($path);
        }
    });
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}