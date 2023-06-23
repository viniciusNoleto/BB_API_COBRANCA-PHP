# **API de Cobrança do Banco do Brasil**

## **Utilização**

```php

    $bb = new ViniciusNoleto\BB_API_COBRANCA\BB(
        'app_key',
        'basic',
        'pix_key',
        'convenio',
        'company_id', // CNPJ or Febran
        'segment_number',
        'P'
    );

    $created_pix_info = $bb->createPIX(
        'Pix Teste',
        [
            'Via_Cobranca_Value' => 'value',
            'Via_Cobranca_ID' => 'id',
            'Cobrado_Name' => 'name',
            'Cobrado_ID' => 'id',
            'Cobrado_Contact' => 'contact'
        ]
    );

    print_r($created_pix_info);

```

## **Documetntação das Classes**

### **Class Cache**

Esta classe é responsável por lidar com armazenamento em cache de arquivos.

> **Métodos**

- **`getFileName()`** (*`String`*): Obtém o nome completo do arquivo com base na rota e no nome fornecidos.
  - **`route`** (*String*): A rota do arquivo.
  - **`name`** (*String*): O nome do arquivo.

- **`fileValidTime()`** (*`Bool`*): Verifica se o arquivo em cache ainda é válido com base no limite de tempo.
  - **`file`** (*String*): O nome completo do arquivo.
  - **`limit`** (*Int*): O limite de tempo em segundos.

- **`storeCache()`** (*`Mixed`*): Armazena o conteúdo em cache no arquivo especificado.
  - **`file`** (*String*): O nome completo do arquivo.
  - **`content`** (*Mixed*): O conteúdo a ser armazenado em cache.

- **`getFile()`** (*`Mixed`*): Obtém o conteúdo armazenado em cache do arquivo especificado.
  - **`file`** (*String*): O nome completo do arquivo.

- **`getCache()`** (*`Mixed`*): Obtém o conteúdo em cache com base na rota e no nome do arquivo.
  - **`route`** (*String*): A rota do arquivo.
  - **`name`** (*String*): O nome do arquivo.
  - **`limit`** (*Int*): O limite de tempo em segundos.
  - **`content`** (*Mixed*): O conteúdo a ser armazenado em cache, se necessário.

- **`getConditionalCache()`** (*`Mixed`*): Obtém o conteúdo em cache condicionalmente com base na rota, no nome do arquivo e em uma função condicional.
  - **`route`** (*String*): A rota do arquivo.
  - **`name`** (*String*): O nome do arquivo.
  - **`limit`** (*Int*): O limite de tempo em segundos.
  - **`function`** (*Callable*): A função a ser executada para obter o conteúdo em caso de falha no cache.
  - **`error_case`** (*Callable*): A função que avalia se a resposta é um caso de erro.
  
<br/>

### **Class Api**

Esta classe fornece métodos para realizar chamadas de API e obter dados.

> **Métodos**

- **`postFieldsEncode()`**: (*`String|Array`*): Codifica os campos de postagem com base no tipo de codificação especificado.
  - **`encode`** (*String*): O tipo de codificação, que pode ser "json" ou "x-www-form-urlencoded".
  - **`input`** (*Array*): Os campos de postagem a serem codificados.

- **`RUN()`**: (*`Array`*): Executa uma chamada de API usando o cURL.
  - **`link`** (*String*): O link da API.
  - **`method`** (*String*, opcional): O método da solicitação, padrão é "GET".
  - **`headers`** (*Array*, opcional): Os cabeçalhos da solicitação, padrão é uma matriz vazia.
  - **`post`** (*Array*, opcional): Os campos de postagem da solicitação, padrão é uma matriz vazia.
  - **`send_type`** (*String*, opcional): O tipo de envio dos dados, pode ser "json" ou "x-www-form-urlencoded", padrão é "json".
  - **`timeout`** (*Int*, opcional): O tempo limite da solicitação em segundos, padrão é 180.

- **`getOAuth()`**: (*`?Array`*): Obtém um token OAuth condicionalmente usando a classe Cache.
  - **`route`** (*String*): A rota da API para obter o token OAuth.
  - **`authorization`** (*String*): A string de autorização para autenticação.
  - **`grant_type`** (*String*): O tipo de concessão do token OAuth.
  - **`scope`** (*String*): O escopo do token OAuth.
  - **`limit`** (*Int*, opcional): O limite de tempo em segundos para manter o token em cache, padrão é 475.

<br/>

### **Class BB**

Classe responsável por interagir com a API do Banco do Brasil.

> **Atributos**

- *`const`* **`BASIC_P_ROUTE`**: Rota básica para ambiente de produção.
- *`const`* **`BASIC_T_ROUTE`**: Rota básica para ambiente de testes.
- *`const`* **`ARRECADACAO_ROUTE`**: Rota para arrecadação de QR codes.
- **`APP_KEY`**: Chave da aplicação.
- **`BASIC`**: Chave basic.
- **`PIX_KEY`**: Chave PIX.
- **`CONVENIO`**: Convênio.
- **`COMPANY_ID`**: ID da empresa.
- **`AMBIENT`**: Ambiente.
- **`SEGMENT`**: Segmento.

> **Métodos**

- **`__construct(APP_KEY, BASIC, PIX_KEY, CONVENIO, COMPANY_ID, SEGMENT, AMBIENT = 'T')`**: Construtor da classe BancoDoBrasilAPI.
  - **`APP_KEY`** (*`String`*): Chave da aplicação.
  - **`BASIC`** (*`String`*): Chave basic.
  - **`PIX_KEY`** (*`String`*): Chave PIX.
  - **`CONVENIO`** (*`String`*): Convênio.
  - **`COMPANY_ID`** (*`String`*): ID da empresa.
  - **`SEGMENT`** (*`String`*): Segmento.
  - **`AMBIENT`** (*`String`*): Ambiente (padrão: 'T').

- **`setAppKey(APP_KEY)`** (*`Void`*): Define a chave da aplicação.
  - **`APP_KEY`** (*`String`*): Chave da aplicação.

- **`setBasic(BASIC)`** (*`Void`*): Define a chave basic.
  - **`BASIC`** (*`String`*): Chave basic.

- **`setPixKey(PIX_KEY)`** (*`Void`*): Define a chave PIX.
  - **`PIX_KEY`** (*`String`*): Chave PIX.

- **`setConvenio(CONVENIO)`** (*`Void`*): Define o convênio.
  - **`CONVENIO`** (*`String`*): Convênio.

- **`validateCNPJ(CNPJ)`** (*`Void`*): Valida um CNPJ.
  - **`CNPJ`** (*`String`*): CNPJ a ser validado.

- **`validateFebranID(FEBRAN)`** (*`Void`*): Valida um ID Febran.
  - **`FEBRAN`** (*`String`*): ID Febran a ser validado.

- **`setCompanyID(COMPANY_ID)`** (*`Void`*): Define o ID da empresa.
  - **`COMPANY_ID`** (*`String`*): ID da empresa.

- **`setSegment(AMBIENT)`** (*`Void`*): Define o ambiente.
  - **`AMBIENT`** (*`String`*): Ambiente.

- **`setAmbient(SEGMENT)`** (*`Void`*): Define o segmento.
  - **`SEGMENT`** (*`String`*): Segmento.

- **`getAmbientRoute()`** (*`String`*): Obtém a rota do ambiente atual.

- **`getOAuth()`** (*`String`*): Retorna o token de autenticação OAuth.
  - **`scope`** (*`String`*): Escopo do token de autenticação (padrão: 'pix.arrecadacao-requisicao pix.arrecadacao-info').

- **`getBarCodeMod10ValidateNumber()`** (*`String`*): Retorna o número de validação do código de barras usando o módulo 10.
  - **`bar`** (*`String`*): Código de barras a ser validado.

- **`getBarCode()`** (*`String`*): Retorna o código de barras a partir do valor e do ID da cobrança.
  - **`valor`** (*`Float`*): Valor da cobrança.
  - **`id`** (*`Int`*): ID da cobrança.

- **`validateDebtInfo()`** (*`Mixed`*): Valida as informações da dívida.
  - **`debt_info`** (*`Array`*): Informações da dívida.
  - **`needed`** (*`Array`*): Campos obrigatórios nas informações da dívida.

- **`SEND()`** (*`Mixed`*): Envia uma solicitação para a API de arrecadação.
  - **`link`** (*`String`*): Link da solicitação.
  - **`method`** (*`String`*): Método HTTP da solicitação.
  - **`title`** (*`String`*): Título da solicitação.
  - **`debt_info`** (*`Array`*): Informações da dívida.
  - **`exp`** (*`Int`*): Tempo de expiração em segundos (padrão: 0).

- **`RECIVE()`** (*`Mixed`*): Envia uma requisição de e recebe uma resposta da API de arrecadação.
  - **`link`** (*`String`*): Link da resposta.
  - **`method`** (*`String`*): Método HTTP da resposta.
  - **`debt_info`** (*`Array`*): Informações da dívida.

- **`requestPix()`** (*`Mixed`*): Realiza uma solicitação Pix para a API de arrecadação.
  - **`funciton`** (*`Callable`*): Função que realiza a solicitação Pix.

- **`createPIX()`** (*`Mixed`*): Cria uma transação Pix.
  - **`title`** (*`String`*): Título da transação.
  - **`debt_info`** (*`Array`*): Informações da dívida.

- **`modifyPIX()`** (*`Mixed`*): Modifica uma transação Pix.
  - **`title`** (*`String`*): Título da transação.
  - **`debt_info`** (*`Array`*): Informações da dívida.
  - **`exp`** (*`Int`*): Tempo de expiração em segundos.

- **`getPIX()`** (*`Mixed`*): Obtém as informações de uma transação Pix.
  - **`debt_info`** (*`Array`*): Informações da dívida.


> **Exceções**

- Os métodos `set` podem lançar uma exceção caso os valores não sejam válidos
- O método `validateDebtInfo` pode lançar uma exceção caso as informações da dívida não estejam completas
