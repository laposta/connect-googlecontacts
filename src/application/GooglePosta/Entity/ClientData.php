<?php

namespace GooglePosta\Entity;

use GooglePosta\Entity\Abstraction\SecureEntity;

class ClientData extends SecureEntity
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $lapostaApiToken;

    /**
     * @var string
     */
    public $returnUrl;

    /**
     * @var string
     */
    public $googleAccessToken;

    /**
     * @var string
     */
    public $googleRefreshToken;

    /**
     * @var int
     */
    public $lastImport;
}
