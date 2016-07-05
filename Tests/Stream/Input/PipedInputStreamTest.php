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
    public function testConstructor()
    {
        $upstream = new PipedOutputStream();
        $input = new PipedInputStream($upstream);
        $this->assertSame($upstream, $input->getUpstream());
        $this->assertFalse($input->isClosed());
    }

    public function testConstructorWithNullUpstream()
    {
        $input = new PipedInputStream();
        $this->assertNull($input->getUpstream());
        $this->assertFalse($input->isClosed());
    }

    /**
     * Connects piped output stream to a piped input stream.
     */
    public function testConnect()
    {
        $upstream = new PipedOutputStream();

        $input = new PipedInputStream();
        $input->connect($upstream);

        $this->assertSame($upstream, $input->getUpstream());
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
        $upstream = new PipedOutputStream();

        $input = new PipedInputStream();
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
        $dispatcher = new EventDispatcher();

        // Initializes listener
        $listener = [new MarkerListener(), 'mark'];

        // Adds dispatcher and listener to the piped output stream
        $upstream
            ->setEventDispatcher($dispatcher)
            ->addListener($upstream::EVENT_CONNECT_BEFORE, $listener);

        // Initializes a piped input stream
        $input = new PipedInputStream();

        // Connects the piped input stream to the piped output stream
        $input->connect($upstream);

        // Asserts the connect() method of the piped output stream has been
        // called.
        $this->assertSame($listener[0], $listener[0]->markers[0]);
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
        $dispatcher = new EventDispatcher();

        // Initializes the listener
        $listener = [new MarkerListener(), 'mark'];

        // Adds dispatcher and listener to the piped output stream.
        $upstream
            ->setEventDispatcher($dispatcher)
            ->addListener($upstream::EVENT_CONNECT_BEFORE, $listener);

        // Initializes a piped input stream.
        $input = new PipedInputStream();

        // Connects the piped input stream to the piped output stream.
        // But sets 'reverse' to false.
        $input->connect($upstream, false, false);

        // Asserts the connect() method of the output stream has not been
        // called.
        $this->assertNull($listener[0]->markers[0]);
    }

    /**
     * Force a piped output stream to connect to a piped input stream and its
     * connected input stream is overwritten.
     */
    public function testForceConnect()
    {
        $upstream = new PipedOutputStream();

        $input = new PipedInputStream();
        $input->connect($upstream);
        $input->connect($upstream, true);

        $this->assertSame($upstream, $input->getUpstream());
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
        $input = new PipedInputStream($upstream);

        // Initializes an event dispatcher.
        $dispatcher = new EventDispatcher();

        // Initializes a listener.
        $listener = [new MarkerListener(), 'mark'];

        // Adds dispatcher and listener to the piped input stream.
        $input
            ->setEventDispatcher($dispatcher)
            ->addListener($input::EVENT_CLOSE_NOTIFY_BEFORE, $listener);

        // Closes the piped input stream.
        $input->close();

        // Asserts closed is true.
        $this->assertTrue($input->isClosed());

        // Asserts notify() of the piped input stream has been called.
        $this->assertSame($listener[0], $listener[0]->markers[0]);
    }

    /**
     * Tries to close a piped output stream that is already closed.
     *
     * @expectedException ZerusTech\Component\IO\Exception\IOException
     * @expectedExceptionMessage Already closed.
     */
    public function testCloseOnClosedStream()
    {
        $input = new PipedInputStream();
        $input->close();
        $input->close();
    }

    /**
     * Tests the read() method of the piped input stream.
     */
    public function testRead()
    {
        $input = new PipedInputStream();

        $buffer = $input->getBuffer();

        $buffer[] = '*';

        $buffer[] = '*';

        $data = $input->read(2);

        $this->assertEquals($data, '**');
    }

    /**
     * Tests wait() and notify() when buffer is empty.
     */
    public function testReadWhenBufferIsEmpty()
    {
        // Initializes a piped input stream.
        $input = new PipedInputStream();

        // Initializes a consumer that reads 1 byte.
        $consumer = new Consumer($input, 1);

        // When started, it will wait because the buffer is empty.
        $consumer->start();

        // Adds contents to the buffer and notify the thread.
        $input->synchronized(function(){

            $buffer = $this->getBuffer();
            $buffer[] = '*';
            $this->notify();

        });

        // Waits till the thread has finished its job.
        $consumer->join();

        // Asserts the thread is not waiting.
        $this->assertFalse($input->isRunning());
    }
}
