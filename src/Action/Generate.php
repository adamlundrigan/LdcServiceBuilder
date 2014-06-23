<?php
namespace LdcServiceBuilder\Action;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\ListenerAggregateTrait;

class Generate implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $em)
    {
        $this->listeners[] = $em->attach('generate', array($this, 'generate'));
    }

    public function generate(EventInterface $e)
    {
        $builder = $e->getTarget();
        $builder instanceof \LdcServiceBuilder\Builder;

        $em = $builder->getEventManager();

        $metadataSet = $builder->getJob()->getEntityMetadata();
        foreach ($metadataSet as $key => $metadata) {
            $params = new \ArrayObject();
            $params['metadata'] = $metadata;

            $result = $em->trigger('generate.entity', $this, $params);
        }
    }

}
