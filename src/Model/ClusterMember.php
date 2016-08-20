<?php

namespace Infraverse\Model;

class ClusterMember
{
    protected $service;
    
    public function getService()
    {
        return $this->service;
    }
    
    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }
}
