<?php

namespace Infraverse\Model;

use PDO;

class MysqlService extends BaseService implements ServiceInterface
{
    protected $port = 3306;

    public function getPdo()
    {
        $server = $this->getServer();
        $ip = $server->getPublicIp();
        $port = $this->getPort();
        $infrastructure = $server->getInfrastructure();
        $credential = $this->getCredential();
        $username = $credential->getUsername();
        $password = $credential->getPassword();
        
        echo "Connecting to $username:$password@$ip:$port\n";
        $connection = "mysql:host=" . $ip . ";port=" . $port;
        $pdo = new PDO($connection, $username, $password);
        return $pdo;
    }
}
