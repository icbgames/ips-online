<?php

namespace IPS\Controller;

class Error extends Base
{
    public function action()
    {
        $this->template = 'error.twig';
    }

}
