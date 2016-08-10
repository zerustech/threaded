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

use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcherContainer;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcher;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcherInterface;
use ZerusTech\Component\Threaded\EventDispatcher\Event;
use ZerusTech\Component\Threaded\EventDispatcher\MarkerListener;

/**
 * Test case for EventDispatcherContainer
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class EventDispatcherContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $indexes = new \Threaded();
        $keys = new \Threaded();
        $listeners = new \Threaded();

        $dispatcher = new EventDispatcher($indexes, $keys, $listeners);

        $instance = new EventDispatcherContainer($dispatcher);

        $this->assertSame($dispatcher, $instance->getEventDispatcher());
    }

    public function testSetters()
    {
        $indexes = new \Threaded();
        $keys = new \Threaded();
        $listeners = new \Threaded();

        $dispatcher = new EventDispatcher($indexes, $keys, $listeners);

        $instance = new EventDispatcherContainer(null);

        $instance->setEventDispatcher($dispatcher);

        $this->assertSame($dispatcher, $instance->getEventDispatcher());
    }

    public function testBasicFlow()
    {
        $event = new Event();

        $markers = new \Threaded();
        $markListener = new MarkerListener($markers);
        $listener = new \Threaded();
        $listener[] = $markListener;
        $listener[] = 'mark';

        $indexes = new \Threaded();
        $keys = new \Threaded();
        $listeners = new \Threaded();
        $dispatcher = new EventDispatcher($indexes, $keys, $listeners);

        $instance = new EventDispatcherContainer($dispatcher);

        $this->assertFalse($instance->hasListeners('event.test', $listener));
        $instance->addListener('event.test', $listener, 10);
        $this->assertTrue($instance->hasListeners('event.test', $listener));

        $instance->getListeners('event.test');

        $instance->dispatch('event.test', $event);

        $instance->removeListener('event.test', $listener);
        $this->assertFalse($instance->hasListeners('event.test', $listener));
    }
}
