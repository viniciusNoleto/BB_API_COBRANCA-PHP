<?php

    namespace ViniciusNoleto\BB_API_COBRANCA\Utils;
    
    class Cache {

        // Obtém o nome completo do arquivo de cache com base na rota e no nome fornecidos.
        // Get the full file name for cache based on the provided route and name.
        private static function getFileName(String $route, String $name): String {
            return __DIR__.'/../../'.$route.'temp/'.preg_replace('/[^0-9a-z]/i', '-', $name).'.bin';
        }
    
        // Verifica se o arquivo de cache ainda é válido com base no limite de tempo fornecido.
        // Check if the cache file is still valid based on the provided time limit.
        private static function fileValidTime(String $file, Int $limit): Bool {
            if(file_exists($file)) return (time() - filemtime($file)) < $limit;
            return false;
        }
    
        // Armazena o conteúdo fornecido em um arquivo de cache.
        // Store the provided content in a cache file.
        private static function storeCache(String $file, Mixed $content): Mixed {
            file_put_contents($file, serialize(preg_replace('/\s+/', ' ', $content)));
            return $content;
        }
    
        // Obtém o conteúdo armazenado em um arquivo de cache.
        // Get the stored content from a cache file.
        private static function getFile(String $file): Mixed {
            if(!file_exists($file)) return null;
            return unserialize(file_get_contents($file));
        }
    
        // Obtém o conteúdo armazenado em cache, ou cria e armazena o conteúdo fornecido.
        // Get the cached content, or create and store the provided content.
        public static function getCache(String $route, String $name, Int $limit, Mixed $content): Mixed {
            $file = self::getFileName($route, $name);
    
            if(self::fileValidTime($file, $limit)) return self::getFile($file);
    
            return self::storeCache($file, $content);
        }
    
        // Obtém o conteúdo armazenado em cache com base em uma condição, ou executa a função fornecida e armazena o resultado.
        // Get the cached content based on a condition, or execute the provided function and store the result.
        public static function getConditionalCache(String $route, String $name, Int $limit, Callable $function, Callable $error_case): Mixed {
            $file = self::getFileName($route, $name);
    
            if(self::fileValidTime($file, $limit)) return self::getFile($file);
    
            $response = $function();
            return $error_case($response) ? self::storeCache($file, $response) : null;
        }
    }