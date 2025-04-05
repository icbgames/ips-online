<?php

namespace IPS\Model\Oauth;

use IPS\Model\Config as Config;

class Factory
{
    public static function create($access = null, $refresh = null, $expire = null)
    {
        $token = new Token();

        if(!is_null($access)) {
            $token->setAccess($access);
        }

        if(!is_null($refresh)) {
            $token->setRefresh($refresh);
        } else {
            $token->setRefresh(Config::get('refresh_token'));
        }

        if(!is_null($expire)) {
            $token->setExpire($expire);
        }

        return $token;
    }
}
