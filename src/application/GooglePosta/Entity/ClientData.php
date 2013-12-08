<?php

namespace GooglePosta\Entity;

class ClientData
{
    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $lapostaApiToken;

    /**
     * @var string
     */
    protected $returnUrl;

    /**
     * @var string
     */
    protected $googleAccessToken;

    /**
     * @var string
     */
    protected $googleRefreshToken;

    /**
     * @var int
     */
    protected $lastUpdate;

    /**
     * @var array
     */
    protected $mappings;

    /**
     * @param string $email
     *
     * @return ClientData
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $googleAccessToken
     *
     * @return ClientData
     */
    public function setGoogleAccessToken($googleAccessToken)
    {
        $this->googleAccessToken = $googleAccessToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleAccessToken()
    {
        return $this->googleAccessToken;
    }

    /**
     * @param string $googleRefreshToken
     *
     * @return ClientData
     */
    public function setGoogleRefreshToken($googleRefreshToken)
    {
        $this->googleRefreshToken = $googleRefreshToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleRefreshToken()
    {
        return $this->googleRefreshToken;
    }

    /**
     * @param string $lapostaApiToken
     *
     * @return ClientData
     */
    public function setLapostaApiToken($lapostaApiToken)
    {
        $this->lapostaApiToken = $lapostaApiToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getLapostaApiToken()
    {
        return $this->lapostaApiToken;
    }

    /**
     * @param string $returnUrl
     *
     * @return ClientData
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param int $lastUpdate
     *
     * @return ClientData
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * @param array $mappings
     *
     * @return ClientData
     */
    public function setMappings($mappings)
    {
        $this->mappings = $mappings;

        return $this;
    }

    /**
     * @return array
     */
    public function getMappings()
    {
        return $this->mappings;
    }
}
