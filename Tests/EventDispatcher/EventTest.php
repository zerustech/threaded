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
    /**
     * Test getter and setters.
     */
    public function testGettersAndSetters()
    {
        $event = new Event();

        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }
}
