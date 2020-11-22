<?php

namespace Nexy\Pix;

define('PAYLOAD_FORMAT_INDICATOR', 0);
define('POINT_OF_INITIATION_METHOD', 1);
define('MERCHANT_ACCOUNT_INFORMATION', 26);
define('MERCHANT_CATEGORY_CODE', 52);
define('TRANSACTION_CURRENCY', 53);
define('TRANSACTION_AMOUNT', 54);
define('COUNTRY_CODE', 58);
define('MERCHANT_NAME', 59);
define('MERCHANT_CITY', 60);
define('ADDITIONAL_DATA_FIELD_TEMPLATE', 62);
define('CRC16', 63);
define('GUI', 0);
define('CHAVE', 1);
define('INFO', 2);
define('TXID', 5);

define('METHOD_ONCE', 12);

/**
 * QRCode Generator Class based on BR Code Spec
 *
 * @see https://www.bcb.gov.br/content/estabilidadefinanceira/spb_docs/ManualBRCode.pdf
 * @see https://www.bcb.gov.br/content/estabilidadefinanceira/forumpireunioes/AnexoI-PadroesParaIniciacaodoPix.pdf
 *
 * @author Ricardo Coelho <ricardo@nexy.com.br>
 * 
 * @package Nexy\Pix
 */
class QRCode
{
    /**
     * Constructor function to initialize default values
     */
    public function __construct()
    {
        $merchant_account_information = new Service\EMV();
        $merchant_account_information->set(GUI, 'br.gov.bcb.pix');
        $merchant_account_information->set(CHAVE, '');

        $additional_data_field_template = new Service\EMV();
        $additional_data_field_template->set(TXID, '***');

        $this->emv = new Service\EMV();
        $this->emv->set(PAYLOAD_FORMAT_INDICATOR, '01');
        $this->emv->set(MERCHANT_ACCOUNT_INFORMATION, $merchant_account_information);
        $this->emv->set(ADDITIONAL_DATA_FIELD_TEMPLATE, $additional_data_field_template);
        $this->emv->set(MERCHANT_CATEGORY_CODE, '0000');
        $this->emv->set(TRANSACTION_CURRENCY, '986');
        $this->emv->set(COUNTRY_CODE, 'BR');
        $this->emv->set(CRC16, 'FFFF');
    }

    /**
     * Updates the merchant key
     *
     * @param string $chave Chave PIX (CPF, CNPJ, E-Mail, Telefone ou EVP)
     * @return QRCode This object for chaining
     */
    public function chave($chave)
    {
        $merchant_account_information = $this->emv->get(MERCHANT_ACCOUNT_INFORMATION);
        $merchant_account_information->set(CHAVE, $chave);
        $this->emv->set(MERCHANT_ACCOUNT_INFORMATION, $merchant_account_information);
        return $this;
    }

    /**
     * Updates the transaction currency
     *
     * @see https://pt.wikipedia.org/wiki/ISO_4217
     *
     * @param integer $moeda ISO 4217 Currency Code
     * @return QRCode This object for chaining
     */
    public function moeda($moeda)
    {
        $this->emv->set(TRANSACTION_CURRENCY, strpad($moeda, 3, '0', STR_PAD_LEFT));
        return $this;
    }

    /**
     * Updates the merchant name
     *
     * @param string $lojista
     * @return QRCode This object for chaining
     */
    public function lojista($lojista)
    {
        $this->emv->set(MERCHANT_NAME, $lojista);
        return $this;
    }

    /**
     * Updates the merchant city
     *
     * @param string $cidade
     * @return QRCode This object for chaining
     */
    public function cidade($cidade)
    {
        $this->emv->set(MERCHANT_CITY, $cidade);
        return $this;
    }

    /**
     * Updates the merchant country
     *
     * @see https://www.iban.com/country-codes
     *
     * @param string $pais Alpha-2 Country Code as of ISO 3166
     * @return QRCode This object for chaining
     */
    public function pais($pais)
    {
        $this->emv->set(COUNTRY_CODE, mb_substr($pais, 0, 2));
        return $this;
    }

    /**
     * Updates the transaction amount
     *
     * @param float $valor
     * @return QRCode This object for chaining
     */
    public function valor($valor)
    {
        $valor = number_format($valor, 2, '.', '');
        $this->emv->set(TRANSACTION_AMOUNT, $valor);
        return $this;
    }

    /**
     * Updates the additional information field
     *
     * @param string $info
     * @return QRCode This object for chaining
     */
    public function info($info)
    {
        $merchant_account_information = $this->emv->get(MERCHANT_ACCOUNT_INFORMATION);
        $merchant_account_information->set(INFO, $info);
        $this->emv->set(MERCHANT_ACCOUNT_INFORMATION, $merchant_account_information);
        return $this;
    }

    /**
     * Updates the transaction ID
     *
     * @param string $txId
     * @return QRCode This object for chaining
     */
    public function txId($txId)
    {
        $additional_data_field_template = $this->emv->get(ADDITIONAL_DATA_FIELD_TEMPLATE);
        $additional_data_field_template->set(TXID, $txId);
        $this->emv->set(ADDITIONAL_DATA_FIELD_TEMPLATE, $additional_data_field_template);
        return $this;
    }

    /**
     * Updates the merchant category code
     *
     * @param string $codigoCategoria 4-digit string
     * @return QRCode This object for chaining
     */
    public function codigoCategoria($codigoCategoria)
    {
        $this->emv->set(
            MERCHANT_CATEGORY_CODE, 
            mb_substr(strpad($codigoCategoria, 4, '0', STR_PAD_LEFT), 0, 4)
        );
        return $this;
    }

    /**
     * Outputs the DataRecord under EMVCo Specification
     *
     * @see https://www.emvco.com/wp-content/uploads/documents/EMVCo-Merchant-Presented-QR-Specification-v1-1.pdf
     *
     * @return string
     */
    public function __toString()
    {
        return $this->emv->__toString();
    }

    /**
     * Outputs the Payment Link
     *
     * @see https://www.bcb.gov.br/content/estabilidadefinanceira/forumpireunioes/AnexoI-PadroesParaIniciacaodoPix.pdf
     *
     * @return string
     */
    public function toLink()
    {
        return 'https://pix.bcb.gov.br/qr/' . base64_encode($this->toString());
    }

    /**
     * Renders and saves the QRCode image to a file
     *
     * @param string $filename The output filename
     * @return QRCode This object for chaining
     */
    public function toFile($filename)
    {
        $options = new \chillerlan\QRCode\QROptions([
            'version' => \chillerlan\QRCode\QRCode::VERSION_AUTO,
            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => \chillerlan\QRCode\QRCode::ECC_L,
            'imageBase64' => false,
            'imageTransparent' => false,
        ]);
        $qrCode = new \chillerlan\QRCode\QRCode($options);
        $image = $qrCode->render($this->__toString());
        file_put_contents($filename, $image);

        return $this;
    }    
}
