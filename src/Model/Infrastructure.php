<?php

namespace Infraverse\Model;

use RuntimeException;

class Infrastructure
{
    protected $name;
    protected $credentials = [];
    protected $servers = [];
    protected $services = [];
    protected $clusters = [];
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function addCredential(Credential $credential)
    {
        $this->credentials[$credential->getName()] = $credential;
    }
    
    public function getCredentials()
    {
        return $this->credentials;
    }
    
    public function hasCredential($name)
    {
        return isset($this->credentials[$name]);
    }
    
    public function getCredential($name)
    {
        if (!$this->hasCredential($name)) {
            throw new RuntimeException("Unknown credential: " . $name);
        }
        return $this->credentials[$name];
    }
    
    public function addServer(Server $server)
    {
        $this->servers[$server->getName()] = $server;
    }
    
    public function getServers()
    {
        return $this->servers;
    }
    
    public function hasServer($name)
    {
        return isset($this->servers[$name]);
    }
    
    public function getServer($name)
    {
        if (!$this->hasServer($name)) {
            throw new RuntimeException("Unknown server: " . $name);
        }
        return $this->servers[$name];
    }
    
    public function addCluster(ClusterInterface $cluster)
    {
        $this->clusters[$cluster->getName()] = $cluster;
    }
    
    public function getClusters()
    {
        return $this->clusters;
    }
    
    public function hasCluster($name)
    {
        return isset($this->clusters[$name]);
    }
    
    public function getCluster($name)
    {
        if (!$this->hasCluster($name)) {
            throw new RuntimeException("Unknown cluster: " . $name);
        }
        return $this->clusters[$name];
    }
    
    public function addService(ServiceInterface $service)
    {
        $this->services[$service->getName()] = $service;
    }
    
    public function getServices()
    {
        return $this->services;
    }
    
    public function hasService($name)
    {
        return isset($this->services[$name]);
    }
    
    public function getService($name)
    {
        if (!$this->hasService($name)) {
            throw new RuntimeException("Unknown service: " . $name);
        }
        return $this->services[$name];
    }
}
