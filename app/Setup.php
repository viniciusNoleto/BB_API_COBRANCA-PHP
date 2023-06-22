<?php

    namespace ViniciusNoleto\BB_API_COBRANCA;

    class Setup {

        private $APP_KEY;
        private $BASIC;
        private $PIX_KEY;
        private $CONVENIO;
        private $CNPJ;
        private $SEGMENT;
        private $AMBIENT;



        public function __construct(String $APP_KEY, String $BASIC, String $PIX_KEY, String $CONVENIO, String $CNPJ, String $SEGMENT, String $AMBIENT){
            $this->setAppKey($APP_KEY);
            $this->setBasic($BASIC);
            $this->setPixKey($PIX_KEY);
            $this->setConvenio($CONVENIO);
            $this->setCNPJ($CNPJ);
            $this->setSegment($SEGMENT);
            $this->setAmbient($AMBIENT);
        }



        public function getAppKey(): String {
            return $this->APP_KEY;
        }

        private function setAppKey(String $APP_KEY): Setup {
            if(strlen($APP_KEY) != 32) throw new \Exception('Invalid App Key');

            $this->APP_KEY = $APP_KEY;

            return $this;
        }


        public function getBasic(): String {
            return $this->BASIC;
        }
        
        private function setBasic(String $BASIC): Setup {
            $this->BASIC = $BASIC;
 
            return $this;
        }


        public function getPixKey(): String {
            return $this->PIX_KEY;
        }
        
        private function setPixKey(String $PIX_KEY): Setup {
            $this->PIX_KEY = $PIX_KEY;
             
            return $this;
        }


        public function getConvenio(): String {
            return $this->CONVENIO;
        }
        
        private function setConvenio(String $CONVENIO): Setup {
            if(strlen($CONVENIO) != 5) throw new \Exception('Invalid App Key');

            $this->CONVENIO = $CONVENIO;
             
            return $this;
        }


        public function getCNPJ(): String {
            return $this->CNPJ;
        }

        private function validateCNPJ(&$CNPJ): Void {
            
            if($CNPJ == '') throw new \Exception('Empty pure CNPJ String', 523);


            $CNPJ = preg_replace('/[^\d^\.^\-^\/]/', '', $CNPJ);
            
            if($CNPJ == '') throw new \Exception('Empty CNPJ String pos basic formatting', 523);
            

            $CNPJ = preg_replace('/[^0-9]/', '', $CNPJ);
            
            if($CNPJ == '') throw new \Exception('Empty CNPJ String pos complet formatting', 523);

        
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
        
        private function setCNPJ(String $CNPJ): Setup {

            $this->validateCNPJ($CNPJ);

            $this->CNPJ = $CNPJ;
             
            return $this;
        }


        public function getSegment(): String {
            return $this->AMBIENT;
        }
        
        private function setSegment(String $AMBIENT): Setup {
            if($AMBIENT != 'T' && $AMBIENT != 'P') throw new \Exception('Wrong Ambient', 523);

            $this->AMBIENT = $AMBIENT;
             
            return $this;
        }


        public function getAmbient(): String {
            return $this->SEGMENT;
        }
        
        private function setAmbient(String $SEGMENT): Setup {
            if($SEGMENT > 9 || $SEGMENT < 1 || $SEGMENT == 8) throw new \Exception('Wrong Ambient', 523);

            $this->SEGMENT = $SEGMENT;
             
            return $this;
        }


    }