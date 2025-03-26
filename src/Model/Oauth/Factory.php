<?php

namespace IPS\Model\Oauth;

use IPS\Model\Config as Config;

class Factory
{
    public static function create()
    {
        $token = new Token();
        $token->setRefresh(Config::get('refresh_token'));
        return $token;
    }
}
