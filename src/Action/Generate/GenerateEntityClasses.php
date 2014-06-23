<?php
namespace LdcServiceBuilder\Action\Generate;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Doctrine\ORM\Tools\EntityGenerator;

/**
 * Generate entity classes from Doctrine metadata
 */
class GenerateEntityClasses implements ListenerAggregateInterface
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

        $metadataSet = $builder->getJob()->getEntityMetadata();
        if (empty($metadataSet)) {
            $builder->getLogger()->crit('Invalid class metadata set!');
            $e->stopPropagation();
        }

        $builder->getLogger()->debug('Generating entity class definitions...');

        $generator = new EntityGenerator();
        $generator->setGenerateAnnotations(true);
        $generator->setGenerateStubMethods(true);

        $generator->generate($metadataSet, $builder->getOptions()->getOutputDir());
    }

}
