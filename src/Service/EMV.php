<?php

namespace Nexy\Pix\Service;

/**
 * An EMVCo Specification DataRecord Implementation
 *
 * @author Ricardo Coelho <ricardo@nexy.com.br>
 * 
 * @package Nexy\Pix\Service
 */
class EMV
{
    /**
     * @var array(string => mixed) DataRecord Fields (Key/Value)
     */
    private $fields;

    public function __construct()
    {
        $this->fields = [];
    }

    /**
     * Adds or updates a key/value pair
     *
     * @param string $key
     * @param mixed $value
     * @return EMV
     */
    public function set($key, $value)
    {
        $this->fields[$key] = $value;
        return $this;
    }

    /**
     * Checks for the existence of a given key
     *
     * @param string $key
     * @return boolean
     */
    public function exists($key)
    {
        return isset($this->fields[$key]);
    }

    /**
     * Retrieves the value of a given key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->fields[$key];
    }

    /**
     * Outputs the Record under EMVCo Specification
     *
     * @see https://www.emvco.com/wp-content/uploads/documents/EMVCo-Merchant-Presented-QR-Specification-v1-1.pdf
     *
     * @return string
     */    
    public function __toString()
    {
        ksort($this->fields);
        $stream = '';
        foreach ($this->fields as $key => $value) {
            $key = str_pad("{$key}", 2, '0', STR_PAD_LEFT);
            $value = "{$value}"; // Se um sub EMV, dispara __toString()
            $length = mb_strlen($value);
            $length = str_pad("{$length}", 2, '0', STR_PAD_LEFT);
            $stream .= "{$key}{$length}{$value}";
        }
        if (isset($this->fields[CRC16])) {
            $stream = mb_substr($stream, 0, -4);
            $crcCalculator = new CRC16();
            $crc16 = $crcCalculator->calculate($stream);
            $stream .= $crc16;
        }
        return $stream;
    }
}
