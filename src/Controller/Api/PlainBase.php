<?php

namespace IPS\Controller\Api;

class PlainBase extends RestBase
{
    public function render()
    {
        http_response_code($this->status);
        header('Content-Type: text/plain');

        echo  $this->response;
    }
}
