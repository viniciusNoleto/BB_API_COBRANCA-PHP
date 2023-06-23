<?php

    namespace ViniciusNoleto\BB_API_COBRANCA;

    use ViniciusNoleto\BB_API_COBRANCA\Connection\Api;

    class BB {

        // Constantes de rota
        // Route constants
        private const BASIC_P_ROUTE = 'https://api.bb.com.br/pix-bb/v1/';
        private const BASIC_T_ROUTE = 'https://api.hm.bb.com.br/pix-bb/v1/';
        private const ARRECADACAO_ROUTE = 'arrecadacao-qrcodes';


        // Variáveis de validação da API
        // API validation variables
        private $APP_KEY = '';
        private $BASIC = '';
        private $PIX_KEY = '';
        private $CONVENIO = '';
        private $COMPANY_ID = '';
        private $SEGMENT = '';
        private $AMBIENT = '';


        // Construtor com validação imbutida
        // Constructor with embedded validation
        public function __construct(String $APP_KEY, String $BASIC, String $PIX_KEY, String $CONVENIO, String $COMPANY_ID, String $SEGMENT, String $AMBIENT = 'T'){
            
            $this->setAppKey($APP_KEY);
            $this->setBasic($BASIC);
            $this->setPixKey($PIX_KEY);
            $this->setConvenio($CONVENIO);
            $this->setCompanyID($COMPANY_ID);
            $this->setSegment($SEGMENT);
            $this->setAmbient($AMBIENT);

        }



        // Define e valida a chave do aplicativo
        // Set and validate the app key
        private function setAppKey(String $APP_KEY) {
            if(strlen($APP_KEY) != 32) throw new \Exception('Invalid App Key');

            $this->APP_KEY = $APP_KEY;
        }

        // Define a chave basic
        // Set the basic key
        private function setBasic(String $BASIC) {
            $this->BASIC = $BASIC;
        }

        // Define a chave Pix
        // Set the Pix key
        private function setPixKey(String $PIX_KEY) {
            $this->PIX_KEY = $PIX_KEY;
        }

        // Define e valida o número do convênio
        // Set and validate the 'convenio' number
        private function setConvenio(String $CONVENIO) {
            if(strlen($CONVENIO) != 5) throw new \Exception('Invalid App Key');

            $this->CONVENIO = $CONVENIO;
        }

        // Valida um número de CNPJ (Cadastro Nacional da Pessoa Jurídica)
        // Validate a CNPJ (National Register of Legal Entities) number
        private function validateCNPJ(&$CNPJ): Void {
            
            if($CNPJ == '') throw new \Exception('Empty pure CNPJ String', 523);

            // Remove caracteres não numéricos, exceto "." e "-"
            // Remove non-numeric characters, except for "." and "-"
            $CNPJ = preg_replace('/[^\d^\.^\-^\/]/', '', $CNPJ);
            
            if($CNPJ == '') throw new \Exception('Empty CNPJ String pos basic formatting', 523);

            // Remove todos os caracteres não numéricos
            // Remove all non-numeric characters
            $CNPJ = preg_replace('/[^0-9]/', '', $CNPJ);
            
            if($CNPJ == '') throw new \Exception('Empty CNPJ String pos complet formatting', 523);

            // Completa o número do CNPJ com zeros à esquerda
            // Pad the CNPJ number with leading zeros
            $CNPJ = str_pad($CNPJ, 14, '0', STR_PAD_LEFT);
        
            if(strlen($CNPJ) != 14) throw new \Exception('Invalid CNPJ size', 523);
        
            // Verifica se há números repetidos
            // Check for repeated numbers
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

        // Valida um ID de Febran (Federação Brasileira de Bancos)
        // Validate a Febran ID (Brazilian Federation of Banks)
        private function validateFebranID(&$FEBRAN): Void {
            
            if(strlen($FEBRAN) != 4) throw new \Exception('Wrong Febran ID size', 523);
            if(!is_numeric($FEBRAN)) throw new \Exception('Febran ID must be numeric', 523);

        }
        
        // Define e valida o ID da empresa
        // Set and validate the company ID
        private function setCompanyID(String $COMPANY_ID) {

            // CNPJ
            if(strlen($COMPANY_ID) > 4) $this->validateCNPJ($COMPANY_ID);
            else $this->validateFebranID($COMPANY_ID);
            // Febran

            $this->COMPANY_ID = $COMPANY_ID;
        }

        // Define e valida o segmento
        // Set and validate the segment
        private function setSegment(String $SEGMENT) {
            if($SEGMENT > 9 || $SEGMENT < 1 || $SEGMENT == 8) throw new \Exception('Wrong Ambient', 523);

            $this->SEGMENT = $SEGMENT;
        }
        
        // Define e valida o ambiente
        // Set and validate the ambient
        private function setAmbient(String $AMBIENT) {
            if($AMBIENT != 'T' && $AMBIENT != 'P') throw new \Exception('Wrong Ambient', 523);

            $this->AMBIENT = $AMBIENT;
        }



        // Retorna a rota de ambiente
        // Returns the environment route
        private function getAmbientRoute(): String {
            return $this->AMBIENT == 'T' ? self::BASIC_T_ROUTE : self::BASIC_P_ROUTE;
        }

        // Obtém o token de autenticação OAuth
        // Gets the OAuth authentication token
        private function getOAuth(String $scope = 'pix.arrecadacao-requisicao pix.arrecadacao-info'): String {
            return Api::getOAuth(
                'https://oauth.hm.bb.com.br/oauth/token',
                'Basic '.$this->BASIC,
                'client_credentials',
                $scope
            )['access_token'] ?? throw new \Exception('GetOAuth internal problem', 523);
        }

        // Retorna o número de validação módulo 10 do código de barras
        // Returns the modulo 10 validation number for the barcode
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

        // Retorna o código de barras
        // Returns the barcode
        private function getBarCode(Float $valor, Int $id): String {

            $valor = str_pad(number_format($valor, 2, '', ''), 11, '0', STR_PAD_LEFT);

            // Verifica se é CNPJ ou código Febran 
            // Verify if it is CNPJ or Febran ID
            $COMPANY_ID = strlen($this->COMPANY_ID) != 4 ? substr($this->COMPANY_ID, 0, 8) : $this->COMPANY_ID;

            $id = str_pad($id, 21, '0', STR_PAD_LEFT);

            $header = '8'.$this->SEGMENT.'6';
            $body = $valor.$COMPANY_ID.$id;

            return $header.self::getBarCodeMod10ValidateNumber($header.$body).$body;
        }

        // Valida as informações da dívida
        // Validates the debt information
        private function validateDebtInfo(Array &$debt_info, Array $needed): Void {
            foreach($needed as $need) 
                if(!isset($debt_info[$need])) 
                    throw new \Exception('Incompleted debt info, missing '.$need, 523);
        }

        // Função focada em enviar dados de pix
        // Function focused to send pix data 
        private function SEND(String $link, String $method, String $title, Array $debt_info, Int $exp = 0) {

            // Valida a formatação do array de informações da cobrança
            // Validates the format of the debt information array
            $this->validateDebtInfo($debt_info, [
                'Via_Cobranca_Value',
                'Via_Cobranca_ID',
                'Cobrado_Name',
                'Cobrado_ID'
            ]);

            // Envia uma requisição
            // Run a request
            return Api::RUN(

                // Link da API de Cobrança do BB
                // BB Cobrança API link
                $this->getAmbientRoute().
                    self::ARRECADACAO_ROUTE.
                    $link.
                    '?gw-app-key='.
                    $this->APP_KEY,

                $method,

                // Autorização utilizando o OAuth com escopo de criação e modificação
                // Authorization using OAuth with creation and modify scope
                [
                    'Authorization: Bearer '.self::getOAuth('pix.arrecadacao-requisicao'),
                    'Content-Type: application/json'
                ],

                // Funde arrays com todas as informações do pix
                // Merge arrays with all pix informations
                array_merge(


                    [
                        'numeroConvenio' => $this->CONVENIO, 
    
                        'indicadorCodigoBarras' => 'N', // TODO: Mudar para 'S' no futuro
                        'codigoGuiaRecebimento' => self::getBarCode($debt_info['Via_Cobranca_Value'], $debt_info['Via_Cobranca_ID'], $this->COMPANY_ID),

                        'nomeDevedor' => $debt_info['Cobrado_Name'],
    
                        'codigoSolicitacaoBancoCentralBrasil' => $this->PIX_KEY,

                        'descricaoSolicitacaoPagamento' => $title,
                        
                        'valorOriginalSolicitacao' => floatval($debt_info['Via_Cobranca_Value']),
    
                        'quantidadeSegundoExpiracao' => $exp
                    ],
                    
                    // Seleciona o tipo de documento do cobrano
                    // Select the 'cobrança' target document type
                    strlen($debt_info['Cobrado_ID']) == 11 ? 
                        ['cpfDevedor' => $debt_info['Cobrado_ID']]:
                        ['cnpjDevedor' => $debt_info['Cobrado_ID']],

                    // Insere o contato apenas se esta informação existir no array
                    // Insert contac info only if this information exist in debt_info array
                    isset($debt_info['Cobrado_Contact']) ? [
                        'codigoPaisTelefoneDevedor' => 55,
    
                        'dddTelefoneDevedor' => substr($debt_info['Cobrado_Contact'], 0, 2),
                        'numeroTelefoneDevedor' => substr($debt_info['Cobrado_Contact'], 2)
                    ]:[]
                )
            );
        }

        // Função para receber dados de transação
        // Function to receive transaction data
        private function RECIVE(String $link, String $method, Array $debt_info) {

            // Valida a formatação do array de informações da cobrança
            // Validates the format of the debt information array
            $this->validateDebtInfo($debt_info, [
                'Cobrado_ID',
                'Via_Cobranca_Value'
            ]);

            // Envia uma requisição
            // Run a request
            return Api::RUN(

                // Link da API de Cobrança do BB
                // BB Cobrança API link
                $this->getAmbientRoute().self::ARRECADACAO_ROUTE.$link.
                    '?gw-dev-app-key='.$this->APP_KEY.
                    '&numeroConvenio='.$this->CONVENIO.
                    '&codigoGuiaRecebimento='.self::getBarCode($debt_info['Via_Cobranca_Value'], $debt_info['Via_Cobranca_ID'], $this->COMPANY_ID),

                $method,

                // Autorização utilizando o OAuth com escopo de informação
                // Authorization using OAuth with information scope
                [
                    'Authorization: Bearer '.self::getOAuth('pix.arrecadacao-info'),
                    'Content-Type: application/json'
                ]
            );
        }

        // Função para realizar uma requisição PIX
        // Function to make a PIX request
        private function requestPix(Callable $funciton): Mixed {

            // Loop that tries to make a BB API request a maximum of five times
            // Loop que tenta fazer no máximo cinco vezes uma requisição da API do BB
            for ($try = 5; $try > 0; $try--) { 
                try{
                    
                    // Faz a requisição
                    // Do the request
                    $response = $funciton();
                    
                    // Passa para o próximo ciclo se a resposta não for um array 
                    // Jump loop if the response is not an array
                    if(!is_array($response)) continue;

                    if(isset($response['erros'])){

                        // Passa para o próximo ciclo se o erro for interno do servidor
                        // Jump loop if it was an server internal error
                        if($response['erros'][0]['mensagem'] == 'Erro Interno do Servidor') continue;

                        
                        // Lança uma exceção se for qualquer outro erro
                        // Throws an exception if it's any other error
                        throw new \Exception('Problema no Pipeline do Pix: '.($response['erros'][0]['mensagem'] ?? 'undefined'), 523);
                    } 

                    return $response;

                }catch(\Exception $e){ /* You can put a Log fucntion here */ }
            }

            // You can put a Log fucntion here

            return false;

        }

        // Função para criar um PIX
        // Function to create a PIX
        public function createPIX(String $title, Array $debt_info) {

            return self::requestPix(
                function()use($title, $debt_info){ 
                    return self::SEND(
                        '', 
                        'POST', 
                        $title, 
                        $debt_info
                    );
                }
            );

        }

        // Função para modificar um PIX
        // Function to modify a PIX
        public function modifyPIX(String $title, Array $debt_info, Int $exp) {

            return self::requestPix(
                function()use($title, $debt_info, $exp){ 
                    return self::SEND(
                        '/'.self::getBarCode(
                            $debt_info['Via_Cobranca_Value'] ?? 0, 
                            $debt_info['Via_Cobranca_ID'] ?? 0, 
                            $this->COMPANY_ID
                        ), 
                        'PUT', 
                        $title, 
                        $debt_info, 
                        $exp
                    );
                }
            );

        }

        // Função para obter informações de um PIX
        // Function to get information about a PIX
        public function getPIX(Array $debt_info) {

            return self::requestPix(
                function()use($debt_info){ 
                    return self::RECIVE(
                        '', 
                        'GET', 
                        $debt_info
                    );
                }
            );

        }

    }