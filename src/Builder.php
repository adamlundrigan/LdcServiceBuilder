<?php
namespace LdcServiceBuilder;

use LdcServiceBuilder\Options\BuilderOptions;
use Zend\Log\LoggerInterface;
use Zend\Log\Logger;
use Zend\EventManager\EventManagerAwareTrait;

class Builder
{
    use EventManagerAwareTrait;

    /**
     * @var BuilderOptions
     */
    protected $options;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var BuilderJob
     */
    protected $job;

    /**
     * @param BuilderOptions|NUL $config
     */
    public function __construct(BuilderOptions $config = NULL)
    {
        $this->setOptions($config ?: new BuilderOptions());
    }

    public function run()
    {
        $this->getLogger()->info('Starting...');

        $this->setJob(new BuilderJob());

        $em = $this->getEventManager();

        // Step 1: Build the entity graph
        $this->getLogger()->info('Loading entity metadata...');
        $result = $em->trigger('load_entity_metadata', $this, []);
        if ($result->stopped()) {
            return $result->last();
        }
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager($force = false)
    {
        if ($force || !$this->events instanceof EventManagerInterface) {
            $em = new \Zend\EventManager\EventManager();

            // Attach all the built-in aggregate event listeners
            $em->attachAggregate(new Action\LoadEntityMetadata());
            $em->attachAggregate(new Action\LoadEntityMetadata\XmlFileParser());
            $em->attachAggregate(new Action\LoadEntityMetadata\YamlFileParser());

            $this->setEventManager($em);
        }

        return $this->events;
    }

    /**
     * @return BuilderOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     *
     * @param BuilderOptions
     * @return self
     */
    public function setOptions(BuilderOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if ( is_null($this->logger) ) {
            $this->logger = new Logger();
            $this->logger->addWriter(
                new \Zend\Log\Writer\Stream('php://output')
            );
        }

        return $this->logger;
    }

    /**
     * Override the default logger
     *
     * @param  LoggerInterface $logger
     * @return self
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Storage for job-in-progress
     *
     * @return BuilderJob
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * Override object for storing job-in-progress
     *
     * @param  BuilderJob                 $job
     * @return \LdcServiceBuilder\Builder
     */
    public function setJob(BuilderJob $job)
    {
        $this->job = $job;

        return $this;
    }

}
