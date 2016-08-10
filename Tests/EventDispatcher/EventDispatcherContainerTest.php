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
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcherContainer;
use ZerusTech\Component\Threaded\EventDispatcher\MarkerListener;
use ZerusTech\Component\Threaded\EventDispatcher\Event;

/**
 * Test case for EventDispatcherContainer.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class EventDispatcherContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $dispatcher = new EventDispatcher();

        $instance = new EventDispatcherContainer($dispatcher);

        $this->assertSame($dispatcher, $instance->getEventDispatcher());
    }

    public function testSetters()
    {
        $dispatcher = new EventDispatcher();

        $instance = new EventDispatcherContainer();

        $this->assertNull($instance->getEventDispatcher());

        $instance->setEventDispatcher($dispatcher);

        $this->assertSame($dispatcher, $instance->getEventDispatcher());
    }

    public function testBasicFlow()
    {
        $event = new Event();

        $marker = new MarkerListener();

        $listener = [$marker, 'mark'];

        $dispatcher = new EventDispatcher();

        $instance = new EventDispatcherContainer($dispatcher);

        $this->assertSame($dispatcher, $instance->getEventDispatcher());

        $this->assertFalse($instance->hasListeners('event.test'));

        $instance->addListener('event.test', $listener, 10);

        $this->assertTrue($instance->hasListeners('event.test'));

        $tmp = $instance->getListeners('event.test');

        $this->assertSame($marker, $tmp[0][0]);
        $this->assertEquals('mark', $tmp[0][1]);

        $instance->dispatch('event.test', $event);

        $markers = $marker->getMarkers();
        $this->assertSame($marker, $markers[0]);

        $instance->removeListener('event.test', $listener);

        $this->assertFalse($instance->hasListeners('event.test'));
    }
}
