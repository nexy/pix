# Nexy - Gerador de QRCode PIX

Esta biblioteca tem o objetivo de gerar o QRCode de recebimento para uma chave PIX. 

Esta versão implementa QRCodes e links de pagamento estáticos em conformidade com a versão 3.0.2 do Manual de Padrões para Iniciação do PIX.

Referência: https://www.bcb.gov.br/content/estabilidadefinanceira/forumpireunioes/AnexoI-PadroesParaIniciacaodoPix.pdf


## Instalação

Para instalar a biblioteca no seu projeto, utilize o `composer`:

```
composer require nexy/pix:1.0.0
```


### Criar um QRCode estático

```php
<?php

require 'vendor/autoload.php';

$pix = new Nexy\Pix\QRCode();

// Informe a chave Pix

// CPF
$pix->chave('12345678900'); // CPF sem pontos ou traço

// Ou CNPJ
$pix->chave('00038166000105'); // CNPJ sem pontos, barra ou traço

// Ou E-Mail
$pix->chave('fulano_da_silva.recebedor@example.com');

// Ou Telefone
$pix->chave('+5561912345678'); // O telefone deve ter código do país e DDD, sem traços, espaços ou parênteses

// Ou chave aleatória (EVP - Endereço virtual de pagamento)
$pix->chave('123e4567-e12b-12d1-a456-426655440000'); // A chave EVP é case insensitive

// Receber R$ 1.000,00
$pix->valor(1000); // A moeda padrão é o real brasileiro. Para mudar, veja Campos Opcionais.
```

### Campos Opcionais

```php
$pix->lojista('Fulano de Tal');
$pix->cidade('BRASILIA');
$pix->pais('BR');
$pix->moeda(986); // Real brasileiro (BRL) - Conforme ISO 4217: https://pt.wikipedia.org/wiki/ISO_4217
$pix->info('Descritivo');
$pix->txId('***'); // Utilize o número do pedido/parcela ou outro campo único para o pagamento.
```

### Obter o link de pagamento

```php
$link = $pix->toLink();
```

### Salvar o QRCode em um arquivo PNG

```php
$pix->toFile('qrcode.png');
```
