<?php

namespace IPS\Controller;

class Top extends Base
{
    public function action()
    {
        $code = $this->param('code');

        // codeがある = Twitchの同意を踏んで飛んできた場合
        if(!is_null($code)) {
            $token = $this->accessToken->getTokenByCode($code);
            $this->twitch->specifyLogin($token);

            $this->accessToken->saveTokens($token);
        }

    }
}
