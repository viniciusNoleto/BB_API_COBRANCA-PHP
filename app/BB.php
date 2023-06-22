<?php

    namespace ViniciusNoleto\BB_API_COBRANCA;

    use ViniciusNoleto\BB_API_COBRANCA\Connection\Api;

    class BB {

        private const BASIC_P_ROUTE = 'https://api.bb.com.br/pix-bb/v1/';
        private const BASIC_T_ROUTE = 'https://api.hm.bb.com.br/pix-bb/v1/';
        private const ARRECADACAO_ROUTE = 'arrecadacao-qrcodes';

        private $APP_KEY;
        private $BASIC;
        private $PIX_KEY;
        private $CONVENIO;
        private $CNPJ;
        private $AMBIENT;
        private $SEGMENT;


        public function __construct(Setup $setup){
            $this->APP_KEY = $setup->getAppKey();
            $this->BASIC = $setup->getBasic();
            $this->PIX_KEY = $setup->getPixKey();
            $this->CONVENIO = $setup->getConvenio();
            $this->CNPJ = $setup->getCNPJ();
            $this->SEGMENT = $setup->getSegment();
            $this->AMBIENT = $setup->getAmbient();
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
            $fator = 2;
            $values_by_factor = '';
            
            foreach(array_reverse(str_split($bar)) as $number){
                $values_by_factor .= $number * $fator;
    
                if($fator == 2) $fator = 1;
                else $fator = 2;
            }
        
            return (10 - (array_sum(str_split($values_by_factor)) % 10)) % 10;
        }

        private function getBarCode(Float $valor, Int $id, Int $cnpj): String {

            $valor = str_pad(number_format($valor, 2, '', ''), 11, '0', STR_PAD_LEFT);

            $cnpj = substr(str_pad($cnpj, 8, '0', STR_PAD_LEFT), 0, 8);

            $id = str_pad($id, 21, '0', STR_PAD_LEFT);

            $header = '8'.$this->SEGMENT.'6';
            $body = $valor.$cnpj.$id;

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
                        'codigoGuiaRecebimento' => self::getBarCode($reque_debt['Valor'], $reque_debt['Debt_ID'], $this->CNPJ),
    
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

                }catch(\Exception $e){ 
                    echo $e->getMessage(), '<br>';
                }
            }

            // You can put a Log fucntion here

            return false;

        }

        public function createPIX(String $title, Array $reque_debt) {

            return self::requestPix(function()use($title, $reque_debt){ return self::SEND('', 'POST', $title, $reque_debt);});

        }

        public function modifyPIX(String $title, Array $reque_debt, Int $exp) {

            return self::requestPix(function()use($title, $reque_debt, $exp){ return self::SEND('/'.self::getBarCode($reque_debt['Valor'], $reque_debt['Debt_ID'], $this->CNPJ), 'PUT', $title, $reque_debt, $exp);});

        }

        public function getPIX(Array $debt) {

            return self::requestPix(function()use($debt){ return self::RECIVE('', 'GET', $debt);});

        }

    }