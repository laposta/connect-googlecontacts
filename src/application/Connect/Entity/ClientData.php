<?php

namespace Connect\Entity;

use Connect\Entity\Abstraction\SecureEntity;

class ClientData extends SecureEntity
{
    /**
     * @var string
     */
    public $token;

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
     * @var \Connect\Entity\GoogleTokenSet
     */
    public $googleTokenSet;

    /**
     * @var string
     */
    public $googleRefreshToken;

    /**
     * @var int
     */
    public $lastImport;

    /**
     * @inheritdoc
     */
    protected function ignore()
    {
        return array('returnUrl', 'lastImport', 'email');
    }
}
