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
     * @var \GooglePosta\Entity\GoogleTokenSet
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
    public function ignore()
    {
        return array('returnUrl', 'lastUpdate');
    }
}
