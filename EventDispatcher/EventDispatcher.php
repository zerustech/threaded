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
 * The default event dispatcher that supports pthread v2.x.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class EventDispatcher extends \Threaded implements EventDispatcherInterface
{
    /**
     * A threaded instance that keeps track of the index of the next listener
     * for a given event name and priority. The key of this instance is:
     * ``event name / priority``, and the value is an integer.
     *
     * @var \Threaded The threaded instance that stores the indexes.
     */
    private $indexes;

    /**
     * A threaded instance that contains keys of listeners. The key of this
     * instance is: ``event name``, and the value is a string consists of keys
     * of all listeners for the given event name, separated by comma ``,`` and
     * sorted natrually.
     *
     * @var \Threaded The threaded instance that stores the keys of listeners.
     */
    private $keys;

    /**
     * A threaded instance that contains all listeners. The key of this instance
     * is: ``event name / priority / index from the indexes``.
     *
     * @var \Threaded The threaded instance that stores the listeners.
     */
    private $listeners;

    /**
     * Constructor.
     *
     * Due to the local variable issue in pthreads v2.x. The threaded instances
     * must be passed from outside.
     *
     * @param \Threaded $indexes The threaded indexes instance..
     * @param \Threaded $keys The threaded keys instance.
     * @param \Threaded $listeners The threaded listeners instance.
     */
    public function __construct(\Threaded $indexes, \Threaded $keys, \Threaded $listeners)
    {
        $this->indexes = $indexes;

        $this->keys = $keys;

        $this->listeners = $listeners;
    }

    /**
     * {@inheritdoc}
     */
    public function addListener($eventName, \Threaded $listener, $priority = 0)
    {
        // The key of the index in the threaded indexes.
        $indexKey = $eventName.'/'.$priority;

        // Increases the index by 1
        $this->indexes[$indexKey] = isset($this->indexes[$indexKey]) ? $this->indexes[$indexKey] + 1 : 0;

        // The key of the listener: event naem / priority / index
        $key = $eventName.'/'.$priority.'/'.$this->indexes[$indexKey];

        // Restores keys for the given event name from the threaded keys.
        $keys = isset($this->keys[$eventName]) ? explode(',', $this->keys[$eventName]) : [];

        // Adds the new key to the threaded keys
        $keys[] = $key;

        // Sorts keys naturally.
        sort($keys, SORT_NATURAL);

        // Stores keys back to the threaded keys
        $this->keys[$eventName] = implode(',', $keys);

        // Adds listener to the threaded listeners with the new key.
        $this->listeners[$key] = $listener;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener($eventName, \Threaded $listener)
    {
        // If listener is not listening at the given event name, returns.
        if (!isset($this->keys[$eventName])) {

            return;
        }

        // Restores keys for the given event name from the threaded keys
        $keys = explode(',', $this->keys[$eventName]);

        foreach ($keys as $key) {

            // If finds a match in the threaded listeners
            if ($listener === $this->listeners[$key]) {

                // Unsets it from the threaded listeners.
                unset($this->listeners[$key]);

                $index = array_search($key, $keys, true);

                // Unsets its key from the threaded keys
                unset($keys[$index]);

                if (0 === count($keys)) {

                    // If no key left for the given event name, unsets its keys
                    // from the threaded keys.
                    unset($this->keys[$eventName]);

                } else {

                    // Stores the keys back.
                    $this->keys[$eventName] = implode(',', $keys);
                }

                break;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName = null)
    {
        // If event name is null, tries to find all listeners.
        if (null === $eventName) {

            // Gets all event names.
            $eventNames = array_keys((array)$this->keys);

            $listeners = [];

            foreach ($eventNames as $eventName) {

                // Gets listeners for each event name and adds the listeners to
                // the threaded listeners.
                $listeners[$eventName] = $this->getListeners($eventName);
            }

            // returns the threaded listeners.
            return $listeners;
        }

        if (!isset($this->keys[$eventName])) {

            // If no key is found for the given event name, returns an empty
            // array.
            return array();
        }

        // Returns all listeners for the given event name.
        $listeners = [];

        $keys = explode(',', $this->keys[$eventName]);

        foreach ($keys as $key) {

            $listeners[] = $this->listeners[$key];
        }

        return $listeners;
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
     * @param \Threaded $listener The listener.
     */
    public function getListenerPriority($eventName, \Threaded $listener)
    {
        // If no listener exists for the event name, returns.
        if (!isset($this->keys[$eventName])) {

            return;
        }

        $priority = null;

        // Restores keys for the event name.
        $keys = explode(',', $this->keys[$eventName]);

        foreach ($keys as $key) {

            // If finds a match, parses its priority from the key.
            if ($listener === $this->listeners[$key]) {

                $meta = explode('/', $key);

                $priority = $meta[1];

                break;
            }
        }

        // Returns the priority.
        return $priority;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, Event $event = null)
    {
        if (null === $event) {

            $event = new Event();
        }

        if ($listeners = $this->getListeners($eventName)) {

            $this->doDispatch($listeners, $eventName, $event);
        }

        return $event;
    }

    /**
     * Dispatches an event to all listeners listening at the event name.
     * @param \Threaded[] $listeners The listeners to be triggered.
     * @param string $eventName The event name.
     * @param Event $event The event.
     * @return void
     */
    protected function doDispatch(array $listeners, $eventName, Event $event)
    {
        foreach ($listeners as $listener) {

            $listener = array_values((array)$listener);

            call_user_func($listener, $event, $eventName, $this);

            if (null !== $event && $event->isPropagationStopped()) {

                break;
            }
        }
    }
}
