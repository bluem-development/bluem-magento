<?php

namespace Bluem\Integration\Controller\Identity;

use Bluem\Integration\Controller\BluemAction;

require_once __DIR__ . '/../BluemAction.php';

class Webhook extends BluemWebhookAction
{
    public function execute()
    {
        echo "webhook" ;
    }
}
