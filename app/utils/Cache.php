<?php

    namespace ViniciusNoleto\BB_API_COBRANCA\Utils;

    class Cache {

        private static function getFileName(String $route, String $name): String {

            return __DIR__.'/../../'.$route.'temp/'.preg_replace('/[^0-9a-z]/i', '-', $name).'.bin';

        }

        private static function fileValidTime(String $file, Int $limit): Bool {

            if(file_exists($file)) return (time() - filemtime($file)) < $limit;
            return false;

        }

        private static function storeCache(String $file, Mixed $content): Mixed {
            file_put_contents($file, serialize(preg_replace('/\s+/', ' ', $content)));

            return $content;
        }

        private static function getFile(String $file): Mixed {
            if(!file_exists($file)) return null;

            return unserialize(file_get_contents($file));
        }

        public static function getCache(String $route, String $name, Int $limit, Mixed $content): Mixed {
            $file = self::getFileName($route, $name);

            if(self::fileValidTime($file, $limit)) return self::getFile($file);

            return self::storeCache($file, $content);
        }

        public static function getConditionalCache(String $route, String $name, Int $limit, Callable $function, Callable $error_case): Mixed {
            $file = self::getFileName($route, $name);

            if(self::fileValidTime($file, $limit)) return self::getFile($file);
            
            $response = $function();
            return $error_case($response) ? self::storeCache($file, $response):null;
        }

    }