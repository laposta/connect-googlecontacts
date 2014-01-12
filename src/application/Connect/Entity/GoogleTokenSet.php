<?php

namespace GooglePosta\Entity;

use GooglePosta\Entity\Abstraction\SecureEntity;

class GoogleTokenSet extends SecureEntity
{
    /**
     * @var string
     */
    public $access_token;

    /**
     * @var string
     */
    public $token_type;

    /**
     * @var int
     */
    public $expires_in;

    /**
     * @var string
     */
    public $refresh_token;

    /**
     * @var int
     */
    public $created;

    /**
     * @inheritdoc
     */
    protected function ignore()
    {
        return array('token_type', 'expires_in', 'created');
    }
}
