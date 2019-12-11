<?php

require_once('definitionsTrait.php');

try {
    spl_autoload_register(function ($className){
        $namespaceParts = explode('\\', $className);
        $phpFile = isset($namespaceParts[count($namespaceParts)-1]) ? $namespaceParts[count($namespaceParts)-1] : null;

        if (is_null($phpFile)) {
            throw new Exception("$className not found.");
        }

        $possibleDirectories = [
            'Controllers',
            '*'
        ];

        foreach ($possibleDirectories as $directory) {
            $path = plugin_dir_path(__DIR__) . "gm-frontend-gallery/$directory/" . $phpFile . '.php';
            if (file_exists($path)) {
                require_once($path);
                break;
            }
        }
    });
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}