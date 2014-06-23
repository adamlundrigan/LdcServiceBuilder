<?php
namespace LdcServiceBuilder\Action\BuildEntityGraph;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\ListenerAggregateTrait;
use LdcServiceBuilder\Options\EntityDefinitionOptions;

class YamlFileParser implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;
    
    public function attach(EventManagerInterface $em)
    {
        $this->listeners[] = $em->attach('build_entity_graph.get_driver', array($this, 'getDriverForSource'));
    }
        
    public function getDriverForSource(EventInterface $e)
    {
        $builder = $e->getTarget();
        $builder instanceof \LdcServiceBuilder\Builder;
        
        $record = $e->getParam('source');
        if ( ! $record instanceof EntityDefinitionOptions ) {
            $builder->getLogger()->crit('Invalid entity source definition!');
            $e->stopPropagation(true);
            return;
        }
        
        // We're only interested in YAML files
        if ( ! $record->getType() == 'yaml' ) {
            return;
        }
        
        $parser = new \Doctrine\ORM\Mapping\Driver\YamlDriver($record->getPath());
        
        $e->stopPropagation();
        return $parser;
    }
    
}