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

/**
 * Test case for Event.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class EventTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $subject = new \Threaded();
        $arguments = new \Threaded();
        $event = new Event($subject, $arguments);

        $this->assertSame($subject, $event->getSubject());
        $this->assertSame($arguments, $event->getArguments());
    }

    /**
     * Test getter and setters.
     */
    public function testGettersAndSetters()
    {
        $subject = new \Threaded();
        $arguments = new \Threaded();
        $event = new Event($subject, $arguments);

        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());

        $arguments = new \Threaded();
        $event->setArguments($arguments);
        $this->assertSame($arguments, $event->getArguments());
    }

    /**
     * Tests arguments related methods.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument "none" not found.
     */
    public function testArguments()
    {
        $subject = new \Threaded();
        $arguments = new \Threaded();
        $event = new Event($subject, $arguments);

        $event->setArgument('scalar', 'scalarValue');

        $event->setArgument('array', ['a', 'b', 'c']);

        $foo = new \Stdclass();
        $foo->value = 'plain value';
        $event->setArgument('plain', $foo);

        $bar = new \Threaded();
        $bar['value'] = 'threaded value';
        $event->setArgument('threaded', $bar);

        $this->assertTrue($event->hasArgument('scalar'));
        $this->assertTrue($event->hasArgument('array'));
        $this->assertTrue($event->hasArgument('plain'));
        $this->assertTrue($event->hasArgument('threaded'));

        $this->assertEquals('scalarValue', $event->getArgument('scalar'));
        $this->assertSame(['a', 'b', 'c'], array_values((array)$event->getArgument('array')));
        $this->assertEquals($foo, $event->getArgument('plain'));
        $this->assertNotSame($foo, $event->getArgument('plain'));
        $this->assertSame($bar, $event->getArgument('threaded'));

        $event->getArgument('none');
    }
}
