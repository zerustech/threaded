<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For full copyright and license information, please view the LICENSE file that
 * was distributed with this source code.
 */
namespace ZerusTech\Component\Threaded\EventDispatcher;

/**
 * The generic interface for event dispatcher container.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
interface EventDispatcherContainerInterface extends EventDispatcherInterface
{
    /**
     * Sets an event dispatcher to the container.
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     * @return EventDispatcherContainerInterface Current instance.
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher);

    /**
     * Gets the event dispatcher.
     * @return EventDispatcherInterface The event dispatcher.
     */
    public function getEventDispatcher();
}
