<?php

namespace App\Plugins\Handler;

/**
 * Handles calling of handlers
 */
class HandlerManager
{
    /**
     * The service container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * The logger instance
     */
    private $logger;

    /**
     * Public constuctor.
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->logger = $container->get('logger');
        $this->configuration = $container->get('configuration_manager');
    }

    /**
     * Handles a handler event
     *
     * @return mixed
     */
    public function getEvent($event)
    {
        $handler = $this->container->get("event_$event");

        $closure = function () use ($event, $handler) {
            $args = func_get_args();

            $this->logger->info('request', [
                'component' => 'Events',
                'source' => $event,
                'action' => 'Event',
                'object' => $args,
                'status' => "Event `$event` has been executed",
            ]);

            return $handler(...$args);
        };

        return $closure;
    }

    /**
     * Broadcasts an event
     *
     * @param string $event The evend ID
     * @param array $arguments Optinal arguments passed to the event
     */
    public function broadcast($event, $arguments)
    {
        $container = $this->container;

        $subscribers = $this->getSubscriberList();

        if (isset($subscribers[$event])) {
            $hooks = $subscribers[$event]['hooks'] ?? [];

            foreach ($hooks as $value) {
                $instance = new $value($container);

                // put argument validation here

                $instance(...$arguments);
            }
        }
    }

    /**
     * Gets subscriber configuration list
     *
     * @return array
     */
    private function getSubscriberList()
    {
        if (!isset($this->subscribers)) {
            $values = $this->configuration->getConfiguration('events');

            if (!empty($values['subscribers'])) {
                $this->subscribers = $values['subscribers'];
            } else {
                $this->subscribers = [];
            }
        }

        return $this->subscribers;
    }
}
