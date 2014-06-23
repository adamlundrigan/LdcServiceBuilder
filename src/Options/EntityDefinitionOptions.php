<?php
namespace LdcServiceBuilder\Options;

use Zend\Stdlib\AbstractOptions;

class EntityDefinitionOptions extends AbstractOptions
{
    protected $namespace;

    protected $path;

    protected $type;

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

        public function getPath()
    {
        return $this->path;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

}
