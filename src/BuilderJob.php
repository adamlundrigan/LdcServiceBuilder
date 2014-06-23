<?php
namespace LdcServiceBuilder;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class BuilderJob
{
    /**
     * @var array<\Doctrine\Common\Persistence\Mapping\ClassMetadata>
     */
    protected $entityMetadata;

    /**
     * @return array<\Doctrine\Common\Persistence\Mapping\ClassMetadata>
     */
    public function getEntityMetadata()
    {
        return $this->entityMetadata;
    }

    /**
     * @param  array<\Doctrine\Common\Persistence\Mapping\ClassMetadata> $entityMetadata
     * @return \LdcServiceBuilder\BuilderJob
     */
    public function setEntityMetadata(array $entityMetadata)
    {
        $this->entityMetadata = array_filter($entityMetadata, function ($obj) {
            return $obj instanceof ClassMetadata;
        });

        return $this;
    }

}
