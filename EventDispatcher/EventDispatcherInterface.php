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
 * The generic interface for all event dispatcher classes.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
interface EventDispatcherInterface
{
    /**
     * Dispatches an event for the given event name.
     * @param string $eventName The eventName.
     * @param Event $event The event to be dispatched.
     * @return Event The event dispatched.
     */
    public function dispatch($eventName, Event $event = null);

    /**
     * Binds an event listener to the event dispatcher and makes it listening at
     * event ``$eventName``.
     *
     * @param string $eventName The event name.
     * @param array $listener A callable array: [listener, method name].
     * @param int priority The priority of the listener. The smaller the value
     * is, the higher the priority.
     * @return EventDispatcherInterface Current instance.
     */
    public function addListener($eventName, array $listener, $priority = 0);

    /**
     * Unbinds a listener for the given event name from the dispatcher.
     * @param string @eventName The event name.
     * @param array $listener The listener to be removed.
     * @return EventDispatcherInterface Current instance.
     */
    public function removeListener($eventName, array $listener);

    /**
     * Gets all listeners for the given event name. If event name is omitted,
     * all listeners for all event names will be returned.
     * @param string|null $eventName The event name.
     * @return array The listeners for the given event name or all.
     */
    public function getListeners($eventName = null);

    /**
     * Checks if there is any listener that is listening at the given event
     * name.
     * @param string $eventName The event name.
     * @return bool True if at leaste one listener is found, and false otherwise.
     */
    public function hasListeners($eventName = null);
}
