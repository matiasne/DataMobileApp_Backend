<?php  
require 'vendor/autoload.php';  
use Ratchet\Server\IoServer;

require 'chat.php';

$server = IoServer::factory(
        new Chat(),
        8080
    );

    $server->run();