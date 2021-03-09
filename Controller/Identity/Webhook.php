<?php

namespace Bluem\Integration\Controller\Identity;

use Bluem\Integration\Controller\BluemAction;

require_once __DIR__ . '/../BluemAction.php';

class Webhook extends BluemAction
{
    public function execute()
    {
        echo "webhook" ;
    }
}
