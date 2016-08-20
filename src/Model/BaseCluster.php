<?php

namespace Infraverse\Model;

abstract class BaseCluster
{
    protected $name;
    protected $type;
    protected $members = [];
    
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
    
    public function addMember(ClusterMember $member)
    {
        $this->members[] = $member;
    }
    
    public function getMembers()
    {
        return $this->members;
    }
}
