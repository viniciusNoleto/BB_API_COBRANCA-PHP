<?php

    namespace ViniciusNoleto\BB_API_COBRANCA\Connection;

    use ViniciusNoleto\BB_API_COBRANCA\Utils\Cache;

    class Api {

        public static function postFieldsEncode(String $encode, Array $input): String|Array {
            switch ($encode) {
                case 'json': return json_encode($input);
                case 'x-www-form-urlencoded': return http_build_query($input);
                default: return $input;
            }
        }

        public static function RUN(String $link, String $method = 'GET', Array $headers = [], Array $post = [], String $send_type = 'json', Int $timeout = 180): Array {
            $cURL = curl_init();

            curl_setopt_array($cURL, [
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,

                CURLOPT_URL => $link,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_CUSTOMREQUEST => in_array($method, ['POST', 'GET', 'PUT', 'DELETE', 'PATCH']) ? $method:'GET',
                CURLOPT_POSTFIELDS => self::postFieldsEncode($send_type, $post)
            ]);
            
            $return = curl_exec($cURL);
            curl_close($cURL);

            $cURLError = curl_error($cURL);
            if($cURLError) return ['ErrorCURL' => $cURLError];
            if(is_null($return)) return ['ErrorCURL' => 'Empty return'];

            return json_decode($return, true);
        }

        public static function getOAuth(String $route, String $authorization, String $grant_type, String $scope, Int $limit = 475): ?Array {
            
            return Cache::getConditionalCache(
                'app/',
                'oauth-'.$grant_type.$scope,
                $limit,
                function()use($route, $authorization, $grant_type, $scope){
                    return self::RUN(
                        $route,
                        'POST',
                        [
                            'Authorization: '.$authorization,
                            'Content-Type: application/x-www-form-urlencoded'
                        ],
                        [
                            'grant_type' => $grant_type,
                            'scope' => $scope,
                        ],
                        'x-www-form-urlencoded'
                    );
                },
                function($response){

                    if(!is_array($response)) return false;
                    return isset($response['access_token']);
                    
                }
            );

        }

    }