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
    protected $googleAccessToken;

    /**
     * @var string
     */
    protected $googleRefreshToken;

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
}
