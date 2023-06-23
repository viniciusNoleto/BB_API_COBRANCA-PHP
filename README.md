# <img src="https://github.com/viniciusNoleto/BB_API_COBRANCA-PHP/assets/85528669/b1ef041d-9eee-4620-bf52-bce425b6b5f9" alt="BB-PHP" height="30px" align="center"> **API de Cobrança do Banco do Brasil**

## **Utilization**

```php

    $bb = new ViniciusNoleto\BB_API_COBRANCA\BB(
        'app_key',
        'basic',
        'pix_key',
        'convenio',
        'company_id', // CNPJ or Febraban
        'segment_number',
        'T'
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

<br/>
<br/>

## **Issues**

### **Modify and Get Request**

> Eu estou tendo problemas com os requests de modificação e de informação de vias de cobrança. Esse problema não está acontecendo apenas na classe BB, mas também quando eu tento testar a rota no Insomnia.

> I'm having problems with requests for modification and get informations from 'vias de vobrança'. This problem is not only happening in the BB class, but also when I try test the route in Insomnia.

### **Bar Code**

> Eu estou tendo problemas com o código de barras gerado. Li o manual completo da Febraban sobre o código de barras e me parece estar certo. Testei outros códigos de geração de código de barras e deram o mesmo resultado, então acredito que minha lógica está correta, mas quando testo criar um pix com essa barra (tanto na classe BB quanto no Insomnia) dá erro por invalidação de código de barras.

> I'm having problems with the generated barcode. I read the complete Febraban manual about the barcode and it seems to be right. I tested other barcode generation codes and they gave the same result, so I believe my logic is correct, but when I try to create a pix with this bar (both in the BB class and in Insomnia) it gives an error due to barcode invalidation.

<br/>
<br/>

## **Classes Documentation** `pt-br`

    For the eng-us version go further in the readme

### **Class Cache**

Esta classe é responsável por lidar com armazenamento em cache de arquivos.

> **Métodos**

- **`getFileName()`** (*`String`*): Obtém o nome completo do arquivo com base na rota e no nome fornecidos.
  - **`route`** (*`String`*): A rota do arquivo.
  - **`name`** (*`String`*): O nome do arquivo.

- **`fileValidTime()`** (*`Bool`*): Verifica se o arquivo em cache ainda é válido com base no limite de tempo.
  - **`file`** (*`String`*): O nome completo do arquivo.
  - **`limit`** (*`Int`*): O limite de tempo em segundos.

- **`storeCache()`** (*`Mixed`*): Armazena o conteúdo em cache no arquivo especificado.
  - **`file`** (*`String`*): O nome completo do arquivo.
  - **`content`** (*`Mixed`*): O conteúdo a ser armazenado em cache.

- **`getFile()`** (*`Mixed`*): Obtém o conteúdo armazenado em cache do arquivo especificado.
  - **`file`** (*`String`*): O nome completo do arquivo.

- **`getCache()`** (*`Mixed`*): Obtém o conteúdo em cache com base na rota e no nome do arquivo.
  - **`route`** (*`String`*): A rota do arquivo.
  - **`name`** (*`String`*): O nome do arquivo.
  - **`limit`** (*`Int`*): O limite de tempo em segundos.
  - **`content`** (*`Mixed`*): O conteúdo a ser armazenado em cache, se necessário.

- **`getConditionalCache()`** (*`Mixed`*): Obtém o conteúdo em cache condicionalmente com base na rota, no nome do arquivo e em uma função condicional.
  - **`route`** (*`String`*): A rota do arquivo.
  - **`name`** (*`String`*): O nome do arquivo.
  - **`limit`** (*`Int`*): O limite de tempo em segundos.
  - **`function`** (*`Callable`*): A função a ser executada para obter o conteúdo em caso de falha no cache.
  - **`error_case`** (*`Callable`*): A função que avalia se a resposta é um caso de erro.
  
<br/>

### **Class Api**

Esta classe fornece métodos para realizar chamadas de API e obter dados.

> **Métodos**

- **`postFieldsEncode()`**: (*`String|Array`*): Codifica os campos de postagem com base no tipo de codificação especificado.
  - **`encode`** (*`String`*): O tipo de codificação, que pode ser "json" ou "x-www-form-urlencoded".
  - **`input`** (*`Array`*): Os campos de postagem a serem codificados.

- **`RUN()`**: (*`Array`*): Executa uma chamada de API usando o cURL.
  - **`link`** (*`String`*): O link da API.
  - **`method`** (*`String`*, *opcional*): O método da solicitação, padrão é "GET".
  - **`headers`** (*`Array`*, *opcional*): Os cabeçalhos da solicitação, padrão é uma matriz vazia.
  - **`post`** (*`Array`*, *opcional*): Os campos de postagem da solicitação, padrão é uma matriz vazia.
  - **`send_type`** (*`String`*, *opcional*): O tipo de envio dos dados, pode ser "json" ou "x-www-form-urlencoded", padrão é "json".
  - **`timeout`** (*`Int`*, *opcional*): O tempo limite da solicitação em segundos, padrão é 180.

- **`getOAuth()`**: (*`?Array`*): Obtém um token OAuth condicionalmente usando a classe Cache.
  - **`route`** (*`String`*): A rota da API para obter o token OAuth.
  - **`authorization`** (*`String`*): A string de autorização para autenticação.
  - **`grant_type`** (*`String`*): O tipo de concessão do token OAuth.
  - **`scope`** (*`String`*): O escopo do token OAuth.
  - **`limit`** (*`Int`*, *opcional*): O limite de tempo em segundos para manter o token em cache, padrão é 475.

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

<br/>
<br/>

## **Classes Documentation** `eng-us (translated by chat-gpt)`

### **Class Cache**

This class is responsible for handling file caching.

> **Methods**

- **`getFileName()`** (*`String`*): Retrieves the full file name based on the provided route and name.
  - **`route`** (*`String`*): The file route.
  - **`name`** (*`String`*): The file name.

- **`fileValidTime()`** (*`Bool`*): Checks if the cached file is still valid based on the time limit.
  - **`file`** (*`String`*): The full file name.
  - **`limit`** (*`Int`*): The time limit in seconds.

- **`storeCache()`** (*`Mixed`*): Stores the cached content in the specified file.
  - **`file`** (*`String`*): The full file name.
  - **`content`** (*`Mixed`*): The content to be stored in the cache.

- **`getFile()`** (*`Mixed`*): Retrieves the cached content from the specified file.
  - **`file`** (*`String`*): The full file name.

- **`getCache()`** (*`Mixed`*): Retrieves the cached content based on the file route and name.
  - **`route`** (*`String`*): The file route.
  - **`name`** (*`String`*): The file name.
  - **`limit`** (*`Int`*): The time limit in seconds.
  - **`content`** (*`Mixed`*): The content to be stored in the cache if needed.

- **`getConditionalCache()`** (*`Mixed`*): Retrieves the cached content conditionally based on the file route, name, and a conditional function.
  - **`route`** (*`String`*): The file route.
  - **`name`** (*`String`*): The file name.
  - **`limit`** (*`Int`*): The time limit in seconds.
  - **`function`** (*`Callable`*): The function to be executed to retrieve the content in case of cache failure.
  - **`error_case`** (*`Callable`*): The function that evaluates if the response is an error case.

<br/>

### **Class Api**

This class provides methods for making API calls and retrieving data.

> **Methods**

- **`postFieldsEncode()`**: (*`String|Array`*): Encodes the post fields based on the specified encoding type.
  - **`encode`** (*`String`*): The encoding type, which can be "json" or "x-www-form-urlencoded".
  - **`input`** (*`Array`*): The post fields to be encoded.

- **`RUN()`**: (*`Array`*): Executes an API call using cURL.
  - **`link`** (*`String`*): The API link.
  - **`method`** (*`String`*, *optional*): The request method, default is "GET".
  - **`headers`** (*`Array`*, *optional*): The request headers, default is an empty array.
  - **`post`** (*`Array`*, *optional*): The request post fields, default is an empty array.
  - **`send_type`** (*`String`*, *optional*): The data submission type, can be "json" or "x-www-form-urlencoded", default is "json".
  - **`timeout`** (*`Int`*, *optional*): The request timeout in seconds, default is 180.

- **`getOAuth()`**: (*`?Array`*): Retrieves an OAuth token conditionally using the Cache class.
  - **`route`** (*`String`*): The API route to retrieve the OAuth token.
  - **`authorization`** (*`String`*): The authorization string for authentication.
  - **`grant_type`** (*`String`*): The grant type of the OAuth token.
  - **`scope`** (*`String`*): The scope of the OAuth token.
  - **`limit`** (*`Int`*, *optional*): The time limit in seconds to cache the token, default is 475.

<br/>

### **Class BB**

Class responsible for interacting with the Banco do Brasil API.

> **Attributes**

- *`const`* **`BASIC_P_ROUTE`**: Basic route for production environment.
- *`const`* **`BASIC_T_ROUTE`**: Basic route for test environment.
- *`const`* **`ARRECADACAO_ROUTE`**: QR code collection route.
- **`APP_KEY`**: Application key.
- **`BASIC`**: Basic key.
- **`PIX_KEY`**: PIX key.
- **`CONVENIO`**: Agreement.
- **`COMPANY_ID`**: Company ID.
- **`AMBIENT`**: Environment.
- **`SEGMENT`**: Segment.

> **Methods**

- **`__construct(APP_KEY, BASIC, PIX_KEY, CONVENIO, COMPANY_ID, SEGMENT, AMBIENT = 'T')`**: Constructor of the BancoDoBrasilAPI class.
  - **`APP_KEY`** (*`String`*): Application key.
  - **`BASIC`** (*`String`*): Basic key.
  - **`PIX_KEY`** (*`String`*): PIX key.
  - **`CONVENIO`** (*`String`*): Agreement.
  - **`COMPANY_ID`** (*`String`*): Company ID.
  - **`SEGMENT`** (*`String`*): Segment.
  - **`AMBIENT`** (*`String`*): Environment (default: 'T').

- **`setAppKey(APP_KEY)`** (*`Void`*): Sets the application key.
  - **`APP_KEY`** (*`String`*): Application key.

- **`setBasic(BASIC)`** (*`Void`*): Sets the basic key.
  - **`BASIC`** (*`String`*): Basic key.

- **`setPixKey(PIX_KEY)`** (*`Void`*): Sets the PIX key.
  - **`PIX_KEY`** (*`String`*): PIX key.

- **`setConvenio(CONVENIO)`** (*`Void`*): Sets the agreement.
  - **`CONVENIO`** (*`String`*): Agreement.

- **`validateCNPJ(CNPJ)`** (*`Void`*): Validates a CNPJ.
  - **`CNPJ`** (*`String`*): CNPJ to validate.

- **`validateFebranID(FEBRAN)`** (*`Void`*): Validates a Febran ID.
  - **`FEBRAN`** (*`String`*): Febran ID to validate.

- **`setCompanyID(COMPANY_ID)`** (*`Void`*): Sets the company ID.
  - **`COMPANY_ID`** (*`String`*): Company ID.

- **`setSegment(AMBIENT)`** (*`Void`*): Sets the environment.
  - **`AMBIENT`** (*`String`*): Environment.

- **`setAmbient(SEGMENT)`** (*`Void`*): Sets the segment.
  - **`SEGMENT`** (*`String`*): Segment.

- **`getAmbientRoute()`** (*`String`*): Gets the current environment route.

- **`getOAuth()`** (*`String`*): Returns the OAuth authentication token.
  - **`scope`** (*`String`*): Authentication token scope (default: 'pix.arrecadacao-requisicao pix.arrecadacao-info').

- **`getBarCodeMod10ValidateNumber()`** (*`String`*): Returns the validation number of the barcode using modulus 10.
  - **`bar`** (*`String`*): Barcode to validate.

- **`getBarCode()`** (*`String`*): Returns the barcode from the value and collection ID.
  - **`valor`** (*`Float`*): Collection value.
  - **`id`** (*`Int`*): Collection ID.

- **`validateDebtInfo()`** (*`Mixed`*): Validates debt information.
  - **`debt_info`** (*`Array`*): Debt information.
  - **`needed`** (*`Array`*): Mandatory fields in the debt information.

- **`SEND()`** (*`Mixed`*): Sends a request to the collection API.
  - **`link`** (*`String`*): Request link.
  - **`method`** (*`String`*): Request HTTP method.
  - **`title`** (*`String`*): Request title.
  - **`debt_info`** (*`Array`*): Debt information.
  - **`exp`** (*`Int`*): Expiration time in seconds (default: 0).

- **`RECIVE()`** (*`Mixed`*): Sends a request and receives a response from the collection API.
  - **`link`** (*`String`*): Response link.
  - **`method`** (*`String`*): Response HTTP method.
  - **`debt_info`** (*`Array`*): Debt information.

- **`requestPix()`** (*`Mixed`*): Makes a Pix request to the collection API.
  - **`funciton`** (*`Callable`*): Function that makes the Pix request.

- **`createPIX()`** (*`Mixed`*): Creates a Pix transaction.
  - **`title`** (*`String`*): Transaction title.
  - **`debt_info`** (*`Array`*): Debt information.

- **`modifyPIX()`** (*`Mixed`*): Modifies a Pix transaction.
  - **`title`** (*`String`*): Transaction title.
  - **`debt_info`** (*`Array`*): Debt information.
  - **`exp`** (*`Int`*): Expiration time in seconds.

- **`getPIX()`** (*`Mixed`*): Gets information about a Pix transaction.
  - **`debt_info`** (*`Array`*): Debt information.


> **Exceptions**

- The `set` methods may throw an exception if the values are not valid.
- The `validateDebtInfo` method may throw an exception if the debt information is incomplete.