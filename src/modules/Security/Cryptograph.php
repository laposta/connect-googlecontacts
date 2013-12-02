<?php

namespace Security;

require_once dirname(__FILE__) . '/CryptographInterface.php';

class Cryptograph implements CryptographInterface
{
    /**
     * @var string
     */
    protected $cypher;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var int
     */
    protected $keySize;

    /**
     * @var int
     */
    protected $ivSize;

    /**
     * @param string $key
     * @param string $cypher
     * @param string $mode
     */
    function __construct($key, $cypher = MCRYPT_BLOWFISH, $mode = MCRYPT_MODE_CFB)
    {
        $this->key    = $key;
        $this->cypher = $cypher;
        $this->mode   = $mode;
    }

    /**
     * Check if encryption is available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return function_exists('mcrypt_module_open');
    }

    /**
     * Open the mcrypt module
     */
    protected function open()
    {
        if (is_resource($this->resource)) {
            return;
        }

        $this->resource = mcrypt_module_open($this->cypher, '', $this->mode, '');
        $this->keySize  = mcrypt_enc_get_key_size($this->resource);
        $this->ivSize   = mcrypt_enc_get_iv_size($this->resource);
    }

    /**
     * Close the mcrypt module
     */
    protected function close()
    {
        if (!is_resource($this->resource)) {
            return;
        }

        mcrypt_module_close($this->resource);

        $this->resource = null;
    }

    /**
     * Initialize the module. If an initialization vector is
     * not provided one will be generated.
     *
     * @param string $iv
     *
     * @return string The initialization vector used for initialization.
     */
    protected function init($iv = null)
    {
        $key = substr(md5($this->key), 0, $this->keySize);

        if (empty($iv)) {
            $iv = mcrypt_create_iv($this->ivSize, MCRYPT_RAND);
        }

        mcrypt_generic_init($this->resource, $key, $iv);

        return $iv;
    }

    /**
     * Deinitialize the module
     */
    protected function deinit()
    {
        mcrypt_generic_deinit($this->resource);
    }

    /**
     * Encode the given string.
     *
     * @param string $string
     *
     * @return string
     */
    public function encode($string)
    {
        $this->open();

        $iv      = $this->init();
        $encoded = $iv . mcrypt_generic($this->resource, $string);

        $this->deinit();

        return $encoded;
    }

    /**
     * Decode the given encoded string.
     *
     * @param string $string
     *
     * @return string
     */
    public function decode($string)
    {
        $this->open();

        $this->init(substr($string, 0, $this->ivSize));

        $decoded = mdecrypt_generic($this->resource, substr($string, $this->ivSize));

        $this->deinit();

        return $decoded;
    }

    /**
     * @param string $key
     *
     * @return Cryptograph
     */
    public function setKey($key)
    {
        $this->close();

        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $cypher
     *
     * @return Cryptograph
     */
    public function setCypher($cypher)
    {
        $this->close();

        $this->cypher = $cypher;

        return $this;
    }

    /**
     * @return string
     */
    public function getCypher()
    {
        return $this->cypher;
    }

    /**
     * @param string $mode
     *
     * @return Cryptograph
     */
    public function setMode($mode)
    {
        $this->close();

        $this->mode = $mode;

        return $this;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }
}
