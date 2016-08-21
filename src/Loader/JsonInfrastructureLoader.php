<?php

namespace Infraverse\Loader;

use Infraverse\Model\Infrastructure;
use Infraverse\Model\Server;
use Infraverse\Model\ClusterMember;
use RuntimeException;

class JsonInfrastructureLoader
{
    public function autoload()
    {
        $filename = __DIR__ . '/../../infraverse.json';
        return $this->loadFile($filename);
    }
    
    public function loadFile($filename)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("File not found: " . $filename);
        }
        $json = file_get_contents($filename);
        $data = json_decode($json, true);
        if (!$data) {
            throw new RuntimeException('JSON parse error: ' . json_last_error_msg());
        }
        return $this->loadData($data);
    }
    
    public function loadData($data)
    {
        $infrastructure = new Infrastructure();
        $infrastructure->setName($data['name']);

        foreach ($data['credentials'] as $cData) {
            $type = $cData['type'];
            $className = '\\Infraverse\\Model\\Credential';
            $credential = new $className();
            $credential->setName($cData['name']);
            $credential->setType($type);
            $credential->setUsername($cData['username']);
            $credential->setPassword($cData['password']);
            $infrastructure->addCredential($credential);
        }
        
        foreach ($data['servers'] as $sData) {
            $className = '\\Infraverse\\Model\\Server';
            $server = new $className();
            $server->setName($sData['name']);
            $server->setPublicIp($sData['public_ip']);
            $server->setPrivateIp($sData['private_ip']);
            $infrastructure->addServer($server);
        }
        
        foreach ($data['services'] as $sData) {
            $type = $sData['type'];
            $className = '\\Infraverse\\Model\\' . ucfirst($type) . 'Service';
            $service = new $className();
            $service->setName($sData['name']);
            $service->setType($sData['type']);
            $service->setRole($sData['role']);
            $service->setServer($infrastructure->getServer($sData['server']));
            $service->setCredential($infrastructure->getCredential($sData['credential']));
            $infrastructure->addService($service);
        }

        foreach ($data['clusters'] as $cData) {
            $type = $cData['type'];
            $className = '\\Infraverse\\Model\\' . ucfirst($type) . 'Cluster';
            $cluster = new $className();
            $cluster->setName($cData['name']);
            $cluster->setType($cData['type']);
            $cluster->setCredentialReplication($infrastructure->getCredential($cData['credential_replication']));
            
            foreach ($cData['members'] as $mData) {
                $member = new ClusterMember();
                $service = $infrastructure->getService($mData['service']);
                $member->setService($service);
                $cluster->addMember($member);
            }
            
            $infrastructure->addCluster($cluster);
        }
        return $infrastructure;
    }
}
