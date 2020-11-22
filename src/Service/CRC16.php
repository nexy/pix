<?php

namespace Nexy\Pix\Service;

/**
 * Utility class for validating CRC16 CCITT False
 *
 * @author Ricardo Coelho <ricardo@nexy.com.br>
 *
 * @package Nexy\Pix\Service
 */
class CRC16
{
    /**
     * Returns the ASCII value of the nth char in the given string
     *
     * @param string $texto The string
     * @param integer $ordem Char position
     * @return interger
     */    
    public function byte($texto, $ordem) {
        return ord(substr($texto, $ordem, 1));
    }

    /**
     * Calculates CRC16 CCITT False with 0x1021 polynomial and 0xFFFF as initial value
     * 
     * @see https://gist.github.com/tijnkooijmans/10981093
     *
     * @param string $texto The payload
     * @return string(4) The 4 bytes string containing the hex CRC16 representation
     */    
    public function calculate($texto) {
        // Conforme seção 4.7.3 da especificação QR Code EMVCo-Merchant-Presented v.1.1
        $crc = $valorInicial = 0xFFFF;
        $polinomio = 0x1021;

        // Conforme ISO/IEC 13239
        $tam = mb_strlen($texto);
        for ($contador = 0; $contador < $tam; $contador++) {
            $crc ^= $this->byte($texto, $contador) << 8;
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ $polinomio;
                } else {
                    $crc = $crc << 1;
                }
            }
        }
        return strtoupper(dechex($crc & $valorInicial));
    }
}
