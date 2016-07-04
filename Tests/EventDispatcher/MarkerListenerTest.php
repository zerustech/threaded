<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For full copyright and license information, please view the LICENSE file that
 * was distributed with this source code.
 */
namespace ZerusTech\Component\Threaded\Tests\EventDispatcher;

use ZerusTech\Component\Threaded\EventDispatcher\Event;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcher;
use ZerusTech\Component\Threaded\EventDispatcher\MarkerListener;

/**
 * Test case for marker listener.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class MarkerListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $markers = new \Threaded();
        $listener = new MarkerListener($markers);

        $this->assertSame($markers, $listener->getMarkers());
    }

    public function testMark()
    {
        // Initializes a listener
        $markers = new \Threaded();
        $listener = new MarkerListener($markers);

        // Initializes an event
        $event = new Event();

        // Initializes an event dispatcher
        $indexes = new \Threaded();
        $keys = new \Threaded();
        $listeners = new \Threaded();
        $dispatcher = new EventDispatcher($indexes, $keys, $listeners);

        // Calls mark() method.
        $listener->mark($event, 'event.1', $dispatcher);

        // Asserts the result
        $this->assertSame($listener, $markers[0]);
    }
}
