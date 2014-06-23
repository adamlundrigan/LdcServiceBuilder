<?php
namespace LdcServiceBuilder\Options;

use Zend\Stdlib\AbstractOptions;

class BuilderOptions extends AbstractOptions
{
    protected $entityDefinitions;

    protected $outputDir;

    protected $outputNamespace;

    public function getEntityDefinitions()
    {
        return $this->entityDefinitions;
    }

    public function getOutputDir()
    {
        return $this->outputDir;
    }

    public function getOutputNamespace()
    {
        return $this->outputNamespace;
    }

    public function setEntityDefinitions($entityDefinitions)
    {
        if ( ! is_array($entityDefinitions) ) {
            //@TODO error
            return $this;
        }

        $this->entityDefinitions = array();
        foreach ($entityDefinitions as $def) {
            $this->entityDefinitions[] = is_array($def)
                    ? new EntityDefinitionOptions($def)
                    : $def;
        }

        return $this;
    }

    public function setOutputDir($outputDir)
    {
        $this->outputDir = $outputDir;

        return $this;
    }

    public function setOutputNamespace($outputNamespace)
    {
        $this->outputNamespace = $outputNamespace;

        return $this;
    }

}
