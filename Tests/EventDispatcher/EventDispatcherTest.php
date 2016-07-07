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

use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcher;
use ZerusTech\Component\Threaded\EventDispatcher\MarkerListener;
use ZerusTech\Component\Threaded\EventDispatcher\Event;

/**
 * Test case for EventDispatcher.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $indexes = new \Threaded();
        $keys = new \Threaded();
        $allListeners = new \Threaded();

        $dispatcher = new EventDispatcher($indexes, $keys, $allListeners);

        $this->assertSame($indexes, $dispatcher->indexes);
        $this->assertSame($keys, $dispatcher->keys);
        $this->assertSame($allListeners, $dispatcher->listeners);
    }

    /**
     * Test listeners related methods.
     */
    public function testListeners()
    {
        // Initializes dispatcher
        $indexes = new \Threaded();
        $keys = new \Threaded();
        $allListeners = new \Threaded();
        $dispatcher = new EventDispatcher($indexes, $keys, $allListeners);

        // Initializes three listeners
        $listenerObject1 = new \Threaded();
        $listenerObject2 = new \Threaded();
        $listenerObject3 = new \Threaded();
        $listenerObject4 = new \Threaded();

        // Listener 1
        $listener1 = new \Threaded();
        $listener1[] = $listenerObject1;
        $listener1[] = 'method1';

        // Listener 2
        $listener2 = new \Threaded();
        $listener2[] = $listenerObject2;
        $listener2[] = 'method2';

        // Listener 3
        $listener3 = new \Threaded();
        $listener3[] = $listenerObject3;
        $listener3[] = 'method3';

        // Listener 4
        $listener4 = new \Threaded();
        $listener4[] = $listenerObject4;
        $listener4[] = 'method4';

        // Tests addListener() and get Listeners()
        // ---------------------------------------
        // Adds listeners for event.1, with priority 0.
        $dispatcher->addListener('event.1', $listener1);
        $dispatcher->addListener('event.1', $listener2);

        // Listeners will be listed in the order they are added.
        $listeners = $dispatcher->getListeners('event.1');

        // Asserts listeners are listed correctly.
        $this->assertSame($listener1, $listeners[0]);
        $this->assertSame($listener2, $listeners[1]);

        // Adds listeners for event.2, with priority 10 and 5
        // listener1 => 10
        // listener2 => 5
        // listener3 => 5
        $dispatcher->addListener('event.2', $listener1, 10);
        $dispatcher->addListener('event.2', $listener2, 5);
        $dispatcher->addListener('event.2', $listener3, 5);

        // Lists listeners for event.2.
        // Listeners will be sorted by they priority in asc order first,
        // then sorted by the order they are added.
        // So listener2 is listed as the first element, and listener1 as the
        // last element.
        $listeners = $dispatcher->getListeners('event.2');

        // Asserts listeners are listed correctly.
        $this->assertSame($listener1, $listeners[0]);
        $this->assertSame($listener2, $listeners[1]);
        $this->assertSame($listener3, $listeners[2]);

        // Lists all listeners for all event names.
        $listeners = $dispatcher->getListeners();

        // Asserts listeners are listed correctly.
        $this->assertSame($listener1, $listeners['event.1'][0]);
        $this->assertSame($listener2, $listeners['event.1'][1]);
        $this->assertSame($listener1, $listeners['event.2'][0]);
        $this->assertSame($listener2, $listeners['event.2'][1]);
        $this->assertSame($listener3, $listeners['event.2'][2]);

        // Tests hasListeners()
        // --------------------
        $this->assertTrue($dispatcher->hasListeners('event.1'));
        $this->assertTrue($dispatcher->hasListeners('event.2'));
        $this->assertFalse($dispatcher->hasListeners('event.3'));

        // Tests getListenerPriority()
        // ---------------------------
        $this->assertEquals(0, $dispatcher->getListenerPriority('event.1', $listener1));
        $this->assertEquals(0, $dispatcher->getListenerPriority('event.1', $listener2));
        $this->assertEquals(10, $dispatcher->getListenerPriority('event.2', $listener1));
        $this->assertEquals(5, $dispatcher->getListenerPriority('event.2', $listener2));
        $this->assertEquals(5, $dispatcher->getListenerPriority('event.2', $listener3));

        // Tests removeListener()
        // ----------------------
        // Asserts listener1 for event.1 is removed correctly.
        $dispatcher->removeListener('event.1', $listener1);
        $listeners = $dispatcher->getListeners('event.1');
        $this->assertSame($listener2, $listeners[0]);

        // Tries to remove all listeners of event.1, and when no listener is
        // left, the listeners returned by getListeners() will be an empty array.
        $dispatcher->removeListener('event.1', $listener2);
        $listeners = $dispatcher->getListeners('event.1');
        $this->assertEmpty($listeners);

        // Adds listeners for event.3
        $dispatcher->addListener('event.3', $listener1);
        $dispatcher->addListener('event.3', $listener2);
        $dispatcher->addListener('event.3', $listener3);

        // Tries to remove a listener in middle of the list.
        // Asserts the left listeners are listed and ordered correctly.
        $dispatcher->removeListener('event.3', $listener2);
        $listeners = $dispatcher->getListeners('event.3');
        $this->assertSame($listener1, $listeners[0]);
        $this->assertSame($listener3, $listeners[1]);

        // Asserts the left listeners are listed and ordered correctly in all
        // listeners.
        $listeners = $dispatcher->getListeners();
        $this->assertSame($listener1, $listeners['event.3'][0]);
        $this->assertSame($listener3, $listeners['event.3'][1]);

        // Adds listeners for event.4 with different priorities.
        $dispatcher->addListener('event.4', $listener1, 5);
        $dispatcher->addListener('event.4', $listener2, 10);
        $dispatcher->addListener('event.4', $listener3, 5);
        $dispatcher->addListener('event.4', $listener4, 5);

        // Asserts the order of listeners for event.4
        $listeners = $dispatcher->getListeners('event.4');
        $this->assertSame($listener1, $listeners[1]);
        $this->assertSame($listener2, $listeners[0]);
        $this->assertSame($listener3, $listeners[2]);
        $this->assertSame($listener4, $listeners[3]);

        // Tries to remove listener3 and asserts the order of the remaining
        // listeners
        $dispatcher->removeListener('event.4', $listener3);
        $listeners = $dispatcher->getListeners('event.4');
        $this->assertSame($listener1, $listeners[1]);
        $this->assertSame($listener2, $listeners[0]);
        $this->assertSame($listener4, $listeners[2]);
    }

    public function testDispatch()
    {
        // Intializes a dispatcher
        $indexes = new \Threaded();
        $keys = new \Threaded();
        $allListeners = new \Threaded();
        $dispatcher = new EventDispatcher($indexes, $keys, $allListeners);

        // Initializes two listener objects
        $markers = new \Threaded();
        $listenerObject1 = new MarkerListener($markers);
        $listenerObject2 = new MarkerListener($markers);

        // Initializes listeners
        $listener1 = new \Threaded();
        $listener1[] = $listenerObject1;
        $listener1[] = 'mark';

        $listener2 = new \Threaded();
        $listener2[] = $listenerObject2;
        $listener2[] = 'mark';

        // Binds listeners to 'event.1'
        $dispatcher->addListener('event.1', $listener1);
        $dispatcher->addListener('event.1', $listener2);

        // Dispatches 'event.1'
        $dispatcher->dispatch('event.1');

        // Asserts that both listener objects have been marked by the marker
        // listener.
        $this->assertSame($listenerObject1, $markers[0]);
        $this->assertSame($listenerObject2, $markers[1]);
    }

    public function testPropagation()
    {
        // Initializes dispatcher
        $indexes = new \Threaded();
        $keys = new \Threaded();
        $allListeners = new \Threaded();
        $dispatcher = new EventDispatcher($indexes, $keys, $allListeners);

        // Initializes two listener objects.
        $markers = new \Threaded();
        $listenerObject1 = new MarkerListener($markers);
        $listenerObject2 = new MarkerListener($markers);

        // Initializes two listeners.
        $listener1 = new \Threaded();
        $listener1[] = $listenerObject1;
        $listener1[] = 'mark';

        $listener2 = new \Threaded();
        $listener2[] = $listenerObject2;
        $listener2[] = 'mark';

        // Binds both listeners to 'event.1'
        $dispatcher->addListener('event.1', $listener1);
        $dispatcher->addListener('event.1', $listener2);

        // Initializes an event and stops the propagation.
        $event = new Event();
        $event->stopPropagation();

        // Dispatches 'event.1'
        $dispatcher->dispatch('event.1', $event);

        // Asserts that only listener1 is called.
        // Listener2 is skiped because the event propagation has been stopped.
        $this->assertSame($listenerObject1, $markers[0]);
        $this->assertNull($markers[1]);
    }
}
