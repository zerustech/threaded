<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For full copyright and license information, please view the LICENSE file that
 * was distributed to this source code.
 */

namespace ZerusTech\Component\Threaded\EventDispatcher;

/**
 * The default event dispatcher that supports pthread v3.x.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class EventDispatcher extends \Threaded implements EventDispatcherInterface
{
    /**
     * A volatile instance that holds the listeners added to current dispatcher
     * as follows:
     *
     *     [
     *         'event name 1' => [
     *             'priority 1' => [listener 1.1, ...],
     *             'priority 2' => [listener 2.1, ...],
     *             ...
     *         ],
     *         ...
     *     ]
     *
     * @var \Volatile The volatile instance that stores the listeners.
     */
    private $listeners;

    /**
     * A volatile instance that holds the listeners sorted by priority in
     * descended order:
     *
     *     [
     *         'event name 1' => [
     *             10 => [listener 1.1, ...],
     *             5 => [listener 2.1, ...],
     *             ...
     *         ],
     *         ...
     *     ]
     *
     * @var \volatile The volatile instance that stores sorted listeners.
     */
    private $sorted;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->listeners = new \Volatile();

        $this->sorted = new \Volatile();
    }

    /**
     * {@inheritdoc}
     */
    public function addListener($eventName, array $listener, $priority = 0)
    {
        // Intializes indexes in $this->listeners
        if (false === isset($this->listeners[$eventName])) {

            $this->listeners[$eventName] = new \Volatile();
        }

        if (false === isset($this->listeners[$eventName][$priority])) {

            $this->listeners[$eventName][$priority] = new \Volatile();
        }

        // Adds listener to $this->listeners
        $this->listeners[$eventName][$priority][] = $listener;

        // Since new listener is added, the sorted listeners must be
        // regenerated.
        unset($this->sorted[$eventName]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener($eventName, array $listener)
    {
        $all = (array)$this->listeners[$eventName];

        $matched = false;

        // Loops all listeners for event name and unsets all listener that is
        // identical to the given listener.
        foreach ($all as $priority => $listeners) {

            foreach ($listeners as $index => $element) {

                if ($listener[0] === $element[0] && $listener[1] === $element[1]) {

                    unset($this->listeners[$eventName][$priority][$index]);

                    $matched = true;
                }
            }
        }

        if ($matched) {

            // Unsets empty indexes for the given event name.
            foreach ($this->listeners[$eventName] as $priority => $listeners) {

                if (0 === $listeners->count()) {

                    unset($this->listeners[$eventName][$priority]);
                }
            }

            if (0 === $this->listeners[$eventName]->count()) {

                unset($this->listeners[$eventName]);
            }

            // Unsets sorted listeners for the given event name.
            unset($this->sorted[$eventName]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName = null)
    {
        // Sorts listeners
        $this->sortListeners($eventName);

        return null === $eventName ? (array)$this->sorted : (array)$this->sorted[$eventName];
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners($eventName = null)
    {
        return (bool) count($this->getListeners($eventName));
    }

    /**
     * Finds priority of the given listener for the event name.
     *
     * @param string $eventName The event name.
     * @param array $listener The listener.
     * @return int|null The priority of the listener, or null if the listener is
     * not listening at the event name.
     */
    public function getListenerPriority($eventName, array $listener)
    {
        $result = null;

        $all = (array)$this->listeners[$eventName];

        foreach ($all as $priority => $listeners) {

            foreach ($listeners as $index => $element) {

                if ($listener[0] === $element[0] && $listener[1] === $element[1]) {

                    $result = $priority;

                    break 2;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, Event $event = null)
    {
        $event = null === $event ? new Event() : $event;

        if ($listeners = $this->getListeners($eventName)) {

            $this->doDispatch($listeners, $eventName, $event);
        }

        return $event;
    }

    /**
     * Dispatches an event to all listeners listening at the event name.
     * @param array $listeners The listeners to be triggered.
     * @param string $eventName The event name.
     * @param Event $event The event.
     * @return EventDispatcherInterface Current instance.
     */
    protected function doDispatch(array $listeners, $eventName, Event $event)
    {
        foreach ($listeners as $listener) {

            $listener = array_values((array)$listener);

            call_user_func($listener, $event, $eventName, $this);

            if ($event->isPropagationStopped()) {

                break;
            }
        }

        return $this;
    }

    /**
     * Sorts listeners of the given event name or all, if event name is omitted,
     * by priority in descended order.
     * @param string $eventName The event name.
     * @return EventDispatcherInterface Current instance.
     */
    private function sortListeners($eventName = null)
    {
        $names = null === $eventName ? array_keys((array)$this->listeners) : [$eventName];

        foreach ($names as $name) {

            if (isset($this->sorted[$name]) || !isset($this->listeners[$name])) {

                continue;
            }

            $listeners = (array)$this->listeners[$name];

            krsort($listeners);

            $this->sorted[$name] = new \Volatile();

            foreach ($listeners as $priority => $elements) {

                foreach ($elements as $element) {

                    $this->sorted[$name][] = $element;
                }
            }
        }

        return $this;
    }
}
