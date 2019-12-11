<?php

require_once('definitionsTrait.php');

try {
    spl_autoload_register(function ($className){
        $namespaceParts = explode('\\', $className);
        $path = plugin_dir_path(__DIR__) . 'gm-frontend-gallery/Controllers/' . $namespaceParts[count($namespaceParts)-1] . '.php';

        if (file_exists($path)) {
            require_once($path);
        }
    });
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}