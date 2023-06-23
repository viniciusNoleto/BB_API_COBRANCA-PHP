<?php

    namespace ViniciusNoleto\BB_API_COBRANCA;

    use ViniciusNoleto\BB_API_COBRANCA\Connection\Api;

    class BB {

        private const BASIC_P_ROUTE = 'https://api.bb.com.br/pix-bb/v1/';
        private const BASIC_T_ROUTE = 'https://api.hm.bb.com.br/pix-bb/v1/';
        private const ARRECADACAO_ROUTE = 'arrecadacao-qrcodes';

        private $APP_KEY = '';
        private $BASIC = '';
        private $PIX_KEY = '';
        private $CONVENIO = '';
        private $COMPANY_ID = '';
        private $AMBIENT = '';
        private $SEGMENT = '';



        public function __construct(String $APP_KEY, String $BASIC, String $PIX_KEY, String $CONVENIO, String $COMPANY_ID, String $SEGMENT, String $AMBIENT = 'T'){
            
            $this->setAppKey($APP_KEY);
            $this->setBasic($BASIC);
            $this->setPixKey($PIX_KEY);
            $this->setConvenio($CONVENIO);
            $this->setCompanyID($COMPANY_ID);
            $this->setSegment($SEGMENT);
            $this->setAmbient($AMBIENT);

        }



        private function setAppKey(String $APP_KEY) {
            if(strlen($APP_KEY) != 32) throw new \Exception('Invalid App Key');

            $this->APP_KEY = $APP_KEY;
        }


        private function setBasic(String $BASIC) {
            $this->BASIC = $BASIC;
        }


        private function setPixKey(String $PIX_KEY) {
            $this->PIX_KEY = $PIX_KEY;
        }


        private function setConvenio(String $CONVENIO) {
            if(strlen($CONVENIO) != 5) throw new \Exception('Invalid App Key');

            $this->CONVENIO = $CONVENIO;
        }

        private function validateCNPJ(&$CNPJ): Void {
            
            if($CNPJ == '') throw new \Exception('Empty pure CNPJ String', 523);


            $CNPJ = preg_replace('/[^\d^\.^\-^\/]/', '', $CNPJ);
            
            if($CNPJ == '') throw new \Exception('Empty CNPJ String pos basic formatting', 523);
            

            $CNPJ = preg_replace('/[^0-9]/', '', $CNPJ);
            
            if($CNPJ == '') throw new \Exception('Empty CNPJ String pos complet formatting', 523);


            $CNPJ = str_pad($CNPJ, 14, '0', STR_PAD_LEFT);
        
            if(strlen($CNPJ) != 14) throw new \Exception('Invalid CNPJ size', 523);
        
            if(preg_match('/(\d)\1{13}/', $CNPJ)) throw new \Exception('Repeated numbers', 523);
        
        
            if(strlen($CNPJ) == 14){
                
                for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
                    $soma += $CNPJ[$i] * $j;
                    $j = ($j == 2) ? 9 : $j - 1;
                }
        
                $resto = $soma % 11;
        
                if ($CNPJ[12] != ($resto < 2 ? 0 : 11 - $resto))  throw new \Exception('Invalid first verification number of CNPJ', 523);
        
                for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
                    $soma += $CNPJ[$i] * $j;
                    $j = ($j == 2) ? 9 : $j - 1;
                }
        
                $resto = $soma % 11;
        
                if($CNPJ[13] != ($resto < 2 ? 0 : 11 - $resto)) throw new \Exception('Invalid second verification number of CNPJ', 523);
        
            };

        }

        private function validateFebranID(&$FEBRAN): Void {
            
            if(strlen($FEBRAN) != 4) throw new \Exception('Wrong Febran ID size', 523);
            if(!is_numeric($FEBRAN)) throw new \Exception('Febran ID must be numeric', 523);

        }
        
        private function setCompanyID(String $COMPANY_ID) {

            if(strlen($COMPANY_ID) > 4) $this->validateCNPJ($COMPANY_ID);
            else $this->validateFebranID($COMPANY_ID);

            $this->COMPANY_ID = $COMPANY_ID;
        }


        private function setSegment(String $AMBIENT) {
            if($AMBIENT != 'T' && $AMBIENT != 'P') throw new \Exception('Wrong Ambient', 523);

            $this->AMBIENT = $AMBIENT;
        }


        private function setAmbient(String $SEGMENT) {
            if($SEGMENT > 9 || $SEGMENT < 1 || $SEGMENT == 8) throw new \Exception('Wrong Ambient', 523);

            $this->SEGMENT = $SEGMENT;
        }



        private function getAmbientRoute(): String {
            return $this->AMBIENT == 'T' ? self::BASIC_T_ROUTE:self::BASIC_P_ROUTE;
        }

        private function getOAuth(String $scope = 'pix.arrecadacao-requisicao pix.arrecadacao-info'): String {
            return Api::getOAuth(
                'https://oauth.hm.bb.com.br/oauth/token',
                'Basic '.$this->BASIC,
                'client_credentials',
                $scope
            )['access_token'] ?? throw new \Exception('GetOAuth internal problem', 523);
        }

        private function getBarCodeMod10ValidateNumber(String $bar): Int {
            $factor = 2;
            $values_by_factor = '';
            
            foreach(array_reverse(str_split($bar)) as $number){
                $values_by_factor .= $number * $factor;
    
                if($factor == 2) $factor = 1;
                else $factor = 2;
            }
        
            return (10 - (array_sum(str_split($values_by_factor)) % 10)) % 10;
        }

        private function getBarCode(Float $valor, Int $id): String {

            $valor = str_pad(number_format($valor, 2, '', ''), 11, '0', STR_PAD_LEFT);

            $COMPANY_ID = strlen($this->COMPANY_ID) != 4  ? substr($this->COMPANY_ID, 0, 8):$this->COMPANY_ID;

            $id = str_pad($id, 21, '0', STR_PAD_LEFT);

            $header = '8'.$this->SEGMENT.'6';
            $body = $valor.$COMPANY_ID.$id;

            return $header.self::getBarCodeMod10ValidateNumber($header.$body).$body;
        }


        private function SEND(String $link, String $method, String $title, Array $reque_debt, Int $exp = 0) {
            return Api::RUN(

                $this->getAmbientRoute().
                self::ARRECADACAO_ROUTE.
                $link.
                '?gw-app-key='.
                $this->APP_KEY,

                $method,

                [
                    'Authorization: Bearer '.self::getOAuth('pix.arrecadacao-requisicao'),
                    'Content-Type: application/json'
                ],

                array_merge(
                    [
                        'numeroConvenio' => $this->CONVENIO, 
    
                        'indicadorCodigoBarras' => 'N',
                        'codigoGuiaRecebimento' => self::getBarCode($reque_debt['Valor'], $reque_debt['Debt_ID'], $this->COMPANY_ID),
    
                        'codigoPaisTelefoneDevedor' => 55,
    
                        'dddTelefoneDevedor' => substr($reque_debt['Contato'], 0, 2),
                        'numeroTelefoneDevedor' => substr($reque_debt['Contato'], 2),
                        'nomeDevedor' => $reque_debt['Nome'],
    
                        'codigoSolicitacaoBancoCentralBrasil' => $this->PIX_KEY,

                        'descricaoSolicitacaoPagamento' => $title,
                        
                        'valorOriginalSolicitacao' => floatval($reque_debt['Valor']),
    
                        'quantidadeSegundoExpiracao' => $exp
                    ],
                    
                    strlen($reque_debt['Reque_ID']) == 11 ? 
                        ['cpfDevedor' => $reque_debt['Reque_ID']]:
                        ['cnpjDevedor' => $reque_debt['Reque_ID']]
                )
            );
        }

        private function RECIVE(String $link, String $method, Array $reque_debt) {
            return Api::RUN(
                self::getAmbientRoute().self::ARRECADACAO_ROUTE.$link.
                    '?gw-dev-app-key='.$this->APP_KEY.
                    '&numeroConvenio='.$this->CONVENIO.
                    '&codigoGuiaRecebimento='.$reque_debt['Debt_ID'],
                $method,
                [
                    'Authorization: Bearer '.self::getOAuth('pix.arrecadacao-info'),
                    'Content-Type: application/json'
                ]
            );
        }


        private function requestPix(Callable $funciton): Mixed {

            for ($try = 5; $try > 0; $try--) { 
                try{
                    
                    $response = $funciton();

                    if(!is_array($response)) continue;

                    if(isset($response['erros'])){
                        if($response['erros'][0]['mensagem'] == 'Erro Interno do Servidor') continue;

                        throw new \Exception('Problema no Pipeline do Pix: '.($response['erros'][0]['mensagem'] ?? 'undefined'), 500);
                    } 

                    return $response;

                }catch(\Exception $e){ /* You can put a Log fucntion here */ }
            }

            // You can put a Log fucntion here

            return false;

        }

        public function createPIX(String $title, Array $reque_debt) {

            return self::requestPix(
                function()use($title, $reque_debt){ 
                    return self::SEND('', 'POST', $title, $reque_debt);
                }
            );

        }

        public function modifyPIX(String $title, Array $reque_debt, Int $exp) {

            return self::requestPix(
                function()use($title, $reque_debt, $exp){ 
                    return self::SEND('/'.self::getBarCode($reque_debt['Valor'], $reque_debt['Debt_ID'], $this->COMPANY_ID), 'PUT', $title, $reque_debt, $exp);
                }
            );

        }

        public function getPIX(Array $debt) {

            return self::requestPix(
                function()use($debt){ 
                    return self::RECIVE('', 'GET', $debt);
                }
            );

        }

    }