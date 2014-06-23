<?php
namespace LdcServiceBuilder\Action;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\ListenerAggregateTrait;
use LdcServiceBuilder\Options\EntityDefinitionOptions;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;

class LoadEntityMetadata implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $em)
    {
        $this->listeners[] = $em->attach('load_entity_metadata', array($this, 'load'));
    }

    public function load(EventInterface $e)
    {
        $builder = $e->getTarget();
        $builder instanceof \LdcServiceBuilder\Builder;

        $builder->getLogger()->debug('Processing entity definition records...');

        $mdt = new MappingDriverChain();

        $entitySources = $builder->getOptions()->getEntityDefinitions();
        foreach ($entitySources as $key => $entitySource) {
            if (! $entitySource instanceof EntityDefinitionOptions) {
                continue;
            }

            $params = new \ArrayObject();
            $params['source'] = $entitySource;

            $result = $builder->getEventManager()->trigger(
                'load_entity_metadata.get_driver',
                $builder,
                $params
            );

            if ( ! $result->stopped() ) {
                $builder->getLogger()->crit('Driver search failed for ' . $entitySource->getType());
                continue;
            }
            $mdt->addDriver($result->last(), $entitySource->getNamespace());
        }

        $allClasses = $mdt->getAllClassNames();
        $builder->getLogger()->debug('Located ' . count($allClasses) . ' entity class names!');

        // @TODO OMGWTF mocking up an EntityManager...really?
        $cfg = \Mockery::mock('Doctrine\ORM\Configuration');
        $cfg->shouldReceive('getNamingStrategy')->andReturnNull();
        $cfg->shouldReceive('getMetadataDriverImpl')->andReturn($mdt);
        $cfg->shouldReceive('getQuoteStrategy')->andReturn(new \Doctrine\ORM\Mapping\DefaultQuoteStrategy());

        $eventmgr = \Mockery::mock('Doctrine\Common\EventManager');
        $eventmgr->shouldReceive('hasListeners')->andReturn(false);

        $plat = \Mockery::mock('Doctrine\DBAL\Platforms\AbstractPlatform');
        $plat->shouldReceive('prefersSequences')->andReturn(true);
        $plat->shouldReceive('fixSchemaElementName')->andReturnUsing(function ($arg) { return $arg; });

        $em = \Mockery::mock('Doctrine\ORM\EntityManager');
        $em->shouldReceive('getConfiguration')->andReturn($cfg);
        $em->shouldReceive('getConnection->getDatabasePlatform')->andReturn($plat);
        $em->shouldReceive('getEventManager')->andReturn($eventmgr);

        $mf = new MyClassMetadataFactory();
        $mf->setEntityManager($em);
        pritn_r($mf->getAllMetadata());
    }

}

// We have to override ClassMetadata::addDiscriminatorMapClass
// because it run a class_exists check on the given class name,
// which fails because we don't have any entity classes yet

class MyClassMetadataFactory extends DisconnectedClassMetadataFactory
{
    protected function newClassMetadataInstance($className)
    {
        return new MyClassMetadataInfo($className);
    }
}

class MyClassMetadataInfo extends ClassMetadata
{
    public function addDiscriminatorMapClass($name, $className)
    {
        $className = $this->fullyQualifiedClassName($className);
        $className = ltrim($className, '\\');
        $this->discriminatorMap[$name] = $className;

        if ($this->name == $className) {
            $this->discriminatorValue = $name;
        } else {
            $this->subClasses[] = $className;
        }
    }
}
