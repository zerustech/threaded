<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace ZerusTech\Component\Threaded\EventDispatcher;

use ZerusTech\Component\Threaded\EventDispatcher\Event;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcher;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcherInterface;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcherContainerInterface;


/**
 * This class represents a thread-safe container for event dispatcher.
 *
 * A thread-safe stream can be accessed by multiple threads and maintain the
 * data consistency.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class EventDispatcherContainer extends \Threaded implements EventDispatcherContainerInterface
{
    /**
     * @var EventDispatcherInterface The event dispatcher.
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     */
    public function __construct($dispatcher)
    {
        $this->dispatcher = $dispatcher;
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
        return null !== $this->dispatcher ? $this->dispatcher->dispatch($eventName, $event) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function addListener($eventName, \Threaded $listener, $priority = 0)
    {
        if (null !== $this->dispatcher) {

            $this->dispatcher->addListener($eventName, $listener, $priority);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener($eventName, \Threaded $listener)
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
        return null !== $this->dispatcher ? $this->dispatcher->getListeners($eventName) : [];
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners($eventName = null)
    {
        return null !== $this->dispatcher ? $this->dispatcher->hasListeners($eventName) : false;
    }
}
