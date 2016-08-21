<?php

namespace Infraverse\Model;

abstract class BaseService
{
    protected $name;
    protected $type;
    protected $server;
    protected $port;
    protected $credential;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    public function getServer()
    {
        return $this->server;
    }
    
    public function setServer($server)
    {
        $this->server = $server;
        return $this;
    }
    
    public function getPort()
    {
        return $this->port;
    }
    
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }
    
    public function getCredential()
    {
        return $this->credential;
    }
    
    public function setCredential(Credential $credential)
    {
        $this->credential = $credential;
        return $this;
    }
}
