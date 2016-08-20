<?php

namespace Infraverse\Model;

class Server
{
    protected $name;
    //protected $services = [];
    protected $publicIp;
    protected $privateIp;
    protected $infrastructure;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function getInfrastructure()
    {
        return $this->infrastructure;
    }
    
    public function setInfrastructure($infrastructure)
    {
        $this->infrastructure = $infrastructure;
        return $this;
    }
    
    
    /*
    public function addService(ServiceInterface $service)
    {
        $this->services[$service->getName()] = $service;
    }
    
    public function getServices()
    {
        return $this->services;
    }
    */
    
    public function getPublicIp()
    {
        return $this->publicIp;
    }
    
    public function setPublicIp($publicIp)
    {
        $this->publicIp = $publicIp;
        return $this;
    }
    
    public function getPrivateIp()
    {
        return $this->privateIp;
    }
    
    public function setPrivateIp($privateIp)
    {
        $this->privateIp = $privateIp;
        return $this;
    }
}
