<?php

namespace IPS\Model\Oauth;

class Token
{
    protected $access;
    protected $expire;
    protected $refresh;
    protected $login;

    public function setAccess($access)
    {
        $this->access = $access;
    }

    public function setExpire($expire)
    {
        $this->expire = $expire;
    }
    
    public function setRefresh($refresh)
    {
        $this->refresh = $refresh;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }
    
    public function getAccess()
    {
        return $this->access;
    }

    public function getExpire()
    {
        return $this->expire;
    }

    public function getRefresh()
    {
        return $this->refresh;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function isExpired()
    {
        if(is_null($this->expire)) {
            return true;
        }

        $current = time();
        if($current >= $this->expire) {
            return true;
        }
        return false;
    }
}
