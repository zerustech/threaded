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
 * This listener registers itself in a shared threaded object, when it's
 * triggered.
 *
 * Sometimes, this is useful for testing threaded objects because threaded
 * objects can't be mocked. So if the listener is 'marked' in the listener
 * container, we will know that the listener has been triggered.
 *
 * Due to the local variable issue of pthreads v2.x, the threaded object that
 * holds the listeners must be initialized and passed from the outside.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class MarkerListener extends \Threaded
{
    /**
     * @var \Threaded $markers The listener container.
     */
    private $markers;

    /**
     * Constructor.
     *
     * @param \Threaded $markers The listener container.
     */
    public function __construct(\Threaded $markers)
    {
        $this->markers = $markers;
    }

    /**
     * Gets the markers.
     *
     * @return \Threaded The listener container.
     */
    public function getMarkers()
    {
        return $this->markers;
    }

    /**
     * Marks current listener in the listener container.
     *
     * @param Event $event The event passed to the listener.
     * @param string $eventName The event name.
     * @param EventDispatcherInterface The event dispatcher.
     * @return void
     */
    public function mark(Event $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $this->markers[] = $this;
    }
}
