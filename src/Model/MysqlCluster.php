<?php

namespace Infraverse\Model;

class MysqlCluster extends BaseCluster implements ClusterInterface
{

    protected $replicationCredential;
    
    public function getCredentialReplication()
    {
        return $this->replicationCredential;
    }
    
    public function setCredentialReplication(Credential $credential)
    {
        $this->replicationCredential = $credential;
        return $this;
    }
}
