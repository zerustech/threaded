<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace ZerusTech\Component\Threaded\Stream;

use ZerusTech\Component\IO\Stream\ClosableInterface;
use ZerusTech\Component\Threaded\EventDispatcher\Event;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcher;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcherInterface;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcherContainerInterface;


/**
 * This abstract class is the superclass of all classes representing a
 * thread-safe input or output stream.
 *
 * A thread-safe stream can be accessed by multiple threads and maintain the
 * data consistency.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
abstract class AbstractStream extends \Volatile implements ClosableInterface, EventDispatcherContainerInterface
{
    /**
     * @var resource The resource being held by current stream.
     */
    protected $resource;

    /**
     * @var bool True if current resource is closed, and false otherwise.
     */
    protected $closed;

    /**
     * @var EventDispatcherInterface The event dispatcher.
     */
    protected $dispatcher;

    /**
     * Name of the event before connection.
     */
    const EVENT_CONNECT_BEFORE = 'connect.before';

    /**
     * Constructor.
     *
     * @param resource $resource The underlying resource.
     */
    public function __construct($resource)
    {
        $this->resource = $resource;

        $this->closed = false === $this->resource ? true : false;
    }

    /**
     * Gets the underlying resource.
     * @return resource The underlying resource.
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * {@inheritdoc}
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, Event $event = null)
    {
        $event = (null !== $event ? $event : new Event($this));

        return null !== $this->dispatcher ? $this->dispatcher->dispatch($eventName, $event) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function addListener($eventName, array $listener, $priority = 0)
    {
        if (null !== $this->dispatcher) {

            $this->dispatcher->addListener($eventName, $listener, $priority);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener($eventName, array $listener)
    {
        if (null !== $this->dispatcher) {

            $this->dispatcher->removeListener($eventName, $listener);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName = null)
    {
        return null !== $this->dispatcher ? $this->dispatcher->getListener($eventName) : [];
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners($eventName = null)
    {
        return null !== $this->dispatcher ? $this->dispatcher->hasListener($eventName) : false;
    }
}
