<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For full copyright and license information, please view the LICENSE file that
 * was distributed with this source code.
 */

namespace ZerusTech\Component\Threaded\Tests\Stream\Input;

use ZerusTech\Component\IO\Exception\IOException;
use ZerusTech\Component\Threaded\Stream\Input\PipedInputStream;
use ZerusTech\Component\Threaded\Stream\Output\PipedOutputStream;
use ZerusTech\Component\Threaded\EventDispatcher\Event;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcher;
use ZerusTech\Component\Threaded\EventDispatcher\MarkerListener;

/**
 * Test case for piped input stream.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class PipedInputStreamTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->ref = new \ReflectionClass('ZerusTech\Component\Threaded\Stream\Input\PipedInputStream');
        $this->upstream = $this->ref->getProperty('upstream');
        $this->upstream->setAccessible(true);
    }

    public function tearDown()
    {
        $this->upstream = null;
        $this->ref = null;
    }

    public function testConstructor()
    {
        $buffer = new \Threaded();
        $upstream = new PipedOutputStream();
        $input = new PipedInputStream($buffer, $upstream);
        $this->assertSame($upstream, $this->upstream->getValue($input));
        $this->assertFalse($input->isClosed());
    }

    public function testConstructorWithNullUpstream()
    {
        $buffer = new \Threaded();
        $input = new PipedInputStream($buffer);
        $this->assertNull($this->upstream->getValue($input));
        $this->assertFalse($input->isClosed());
    }

    /**
     * Connects piped output stream to a piped input stream.
     */
    public function testConnect()
    {
        $buffer = new \Threaded();
        $upstream = new PipedOutputStream();

        $input = new PipedInputStream($buffer);
        $input->connect($upstream);

        $this->assertSame($upstream, $this->upstream->getValue($input));
        $this->assertFalse($upstream->isClosed());
    }

    /**
     * Connects a piped output stream, which is already connected, to a piped
     * input stream.
     *
     * @expectedException ZerusTech\Component\IO\Exception\IOException
     * @expectedExceptionMessage Already connected.
     */
    public function testConnectOnConnectedStream()
    {
        $buffer = new \Threaded();
        $upstream = new PipedOutputStream();

        $input = new PipedInputStream($buffer);
        $input->connect($upstream);
        $input->connect($upstream);
    }

    /**
     * Connects a piped output stream to a piped input stream and allows the
     * connect() method of the piped output stream to be called.
     */
    public function testReverseConnect()
    {
        // Initializeds a piped output stream
        $upstream = new PipedOutputStream();

        // Initializes an event dispatcher
        $indexes = new \Threaded();
        $keys = new \Threaded();
        $allListeners = new \Threaded();
        $dispatcher = new EventDispatcher($indexes, $keys, $allListeners);

        // Initializes listener
        $markers = new \Threaded();
        $listenerObject = new MarkerListener($markers);
        $listener = new \Threaded();
        $listener[] = $listenerObject;
        $listener[] = 'mark';

        // Adds dispatcher and listener to the piped output stream
        $upstream
            ->setEventDispatcher($dispatcher)
            ->addListener($upstream::EVENT_CONNECT_BEFORE, $listener);

        // Initializes a piped input stream
        $buffer = new \Threaded();
        $input = new PipedInputStream($buffer);

        // Connects the piped input stream to the piped output stream
        $input->connect($upstream);

        // Asserts the connect() method of the piped output stream has been
        // called.
        $this->assertSame($listenerObject, $listenerObject->markers[0]);
    }

    /**
     * Connects a piped output stream to a piped input stream and disallows the
     * connect() method of the piped output stream to be called.
     */
    public function testNonReverseConnect()
    {
        // Initializes a piped output stream
        $upstream = new PipedOutputStream();

        // Initializes an event dispatcher
        $indexes = new \Threaded();
        $keys = new \Threaded();
        $allListeners = new \Threaded();
        $dispatcher = new EventDispatcher($indexes, $keys, $allListeners);

        // Initializes the listener
        $markers = new \Threaded();
        $listenerObject = new MarkerListener($markers);
        $listener = new \Threaded();
        $listener[] = $listenerObject;
        $listener[] = 'mark';

        // Adds dispatcher and listener to the piped output stream.
        $upstream
            ->setEventDispatcher($dispatcher)
            ->addListener($upstream::EVENT_CONNECT_BEFORE, $listener);

        // Initializes a piped input stream.
        $buffer = new \Threaded();
        $input = new PipedInputStream($buffer);

        // Connects the piped input stream to the piped output stream.
        // But sets 'reverse' to false.
        $input->connect($upstream, false, false);

        // Asserts the connect() method of the output stream has not been
        // called.
        $this->assertNull($listenerObject->markers[0]);
    }

    /**
     * Force a piped output stream to connect to a piped input stream and its
     * connected input stream is overwritten.
     */
    public function testForceConnect()
    {
        $buffer = new \Threaded();
        $upstream = new PipedOutputStream();

        $input = new PipedInputStream($buffer);
        $input->connect($upstream);
        $input->connect($upstream, true);

        $this->assertSame($upstream, $this->upstream->getValue($input));
        $this->assertFalse($upstream->isClosed());
    }

    /**
     * Closes a connected piped input stream. A listener is registered at
     * 'close.notify.before', so when the listener is called, we know the
     * upstream is notified.
     */
    public function testClose()
    {
        // Initializes a piped output stream.
        $upstream = new PipedOutputStream();

        // Initializes a piped input stream.
        $buffer = new \Threaded();
        $input = new PipedInputStream($buffer, $upstream);

        // Initializes an event dispatcher.
        $indexes = new \Threaded();
        $keys = new \Threaded();
        $allListeners = new \Threaded();
        $dispatcher = new EventDispatcher($indexes, $keys, $allListeners);

        // Initializes a listener.
        $markers = new \Threaded();
        $listenerObject = new MarkerListener($markers);
        $listener = new \Threaded();
        $listener[] = $listenerObject;
        $listener[] = 'mark';

        // Adds dispatcher and listener to the piped input stream.
        $input
            ->setEventDispatcher($dispatcher)
            ->addListener($input::EVENT_CLOSE_NOTIFY_BEFORE, $listener);

        // Closes the piped input stream.
        $input->close();

        // Asserts closed is true.
        $this->assertTrue($input->isClosed());

        // Asserts notify() of the piped input stream has been called.
        $this->assertSame($listenerObject, $listenerObject->markers[0]);
    }

    /**
     * Tries to close a piped output stream that is already closed.
     *
     * @expectedException ZerusTech\Component\IO\Exception\IOException
     * @expectedExceptionMessage Already closed.
     */
    public function testCloseOnClosedStream()
    {
        $buffer = new \Threaded();
        $input = new PipedInputStream($buffer);
        $input->close();
        $input->close();
    }

    /**
     * Tests the read() method of the piped input stream.
     */
    public function testRead()
    {
        $buffer = new \Threaded();

        $buffer[] = '*';

        $buffer[] = '*';

        $input = new PipedInputStream($buffer);

        $data = $input->read(2);

        $this->assertEquals($data, '**');
    }

    /**
     * Tests wait() and notify() when buffer is empty.
     */
    public function testReadWhenBufferIsEmpty()
    {
        // Initializes a piped input stream.
        $buffer = new \Threaded();
        $input = new PipedInputStream($buffer);

        // Initializes a consumer that reads 1 byte.
        $consumer = new Consumer($input, 1);

        $this->assertEquals(0, $input->available());

        // When started, it will wait because the buffer is empty.
        $consumer->start();

        // Adds contents to the buffer and notify the thread.
        $input->synchronized(function($self, $buffer){

            $buffer[] = '*';
            $buffer[] = '*';
            $self->notify();

        }, $input, $buffer);

        // Waits till the thread has finished its job.
        $consumer->join();

        $this->assertEquals(1, $input->available());

        // Asserts the thread is not running.
        $this->assertFalse($input->isRunning());
    }
}
