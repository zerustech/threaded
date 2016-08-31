<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For full copyright and license information, please view the LICENSE file that
 * was distributed with this source code.
 */

namespace ZerusTech\Component\Threaded\Tests\Stream\Output;

use ZerusTech\Component\IO\Exception\IOException;
use ZerusTech\Component\Threaded\EventDispatcher\Event;
use ZerusTech\Component\Threaded\Stream\Input\PipedInputStream;
use ZerusTech\Component\Threaded\Stream\Output\PipedOutputStream;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcher;
use ZerusTech\Component\Threaded\EventDispatcher\MarkerListener;

/**
 * Test case for piped output stream.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class PipedOutputStreamTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->ref = new \ReflectionClass('ZerusTech\Component\Threaded\Stream\Output\PipedOutputStream');

        $this->output = $this->ref->getMethod('output');
        $this->output->setAccessible(true);
    }

    public function tearDown()
    {
        $this->output = null;
        $this->ref = null;
    }

    public function testConstructor()
    {
        $downstream = new PipedInputStream();
        $output = new PipedOutputStream($downstream);
        $this->assertSame($downstream, $output->downstream);
        $this->assertFalse($output->closed);
    }

    public function testConstructorWithNull()
    {
        $output = new PipedOutputStream();
        $this->assertNull($output->downstream);
        $this->assertFalse($output->isClosed());
    }

    /**
     * Connects piped output stream to a piped input stream.
     */
    public function testConnect()
    {
        $downstream = new PipedInputStream();

        $output = new PipedOutputStream();
        $output->connect($downstream);

        $this->assertSame($downstream, $output->downstream);
        $this->assertFalse($output->isClosed());
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
        $downstream = new PipedInputStream();
        $output = new PipedOutputStream();
        $output->connect($downstream);
        $output->connect($downstream);
    }

    /**
     * Connects a piped output stream to a piped input stream and allows the
     * connect() method of the piped input stream to be called.
     */
    public function testReverseConnect()
    {
        // Initializes a piped input stream.
        $downstream = new PipedInputStream();

        // Initializes an event dispatcher.
        $dispatcher = new EventDispatcher();

        // Initializes listener.
        $listener = [new MarkerListener(), 'mark'];

        // Adds dispatcher and listener to the piped input stream.
        $downstream
            ->setEventDispatcher($dispatcher)
            ->addListener($downstream::EVENT_CONNECT_BEFORE, $listener);

        // Initializes a piped output stream.
        $output = new PipedOutputStream();

        // Connects the piped output stream to the piped input stream.
        $output->connect($downstream);

        // Asserts the connect() method of the piped input stream has been
        // called.
        $this->assertSame($listener[0], $listener[0]->markers[0]);
    }

    /**
     * Connects a piped output stream to a piped input stream and disallows the
     * connect() method of the piped input stream to be called.
     */
    public function testNonReverseConnect()
    {
        // Initializes a piped input stream.
        $downstream = new PipedInputStream();

        // Initializes an event dispatcher.
        $dispatcher = new EventDispatcher();

        // Initializes a listener.
        $listener = [new MarkerListener(), 'mark'];

        // Adds the dispatcher and listener to the piped input stream.
        $downstream
            ->setEventDispatcher($dispatcher)
            ->addListener($downstream::EVENT_CONNECT_BEFORE, $listener);

        // Initializes a piped output stream.
        $output = new PipedOutputStream();

        // Connects the piped output stream to the piped input stream, but sets
        // the 'reverse' argument to false.
        $output->connect($downstream, false, false);

        // Asserts the connect() method of the piped input stream has not been
        // called.
        $this->assertNull($listener[0]->markers[0]);
    }

    /**
     * Force a piped output stream to connect to a piped input stream and its
     * connected input stream is overwritten.
     */
    public function testForceConnect()
    {
        $downstream = new PipedInputStream();

        $output = new PipedOutputStream();
        $output->connect($downstream);
        $output->connect($downstream, true);

        $this->assertSame($downstream, $output->downstream);
        $this->assertFalse($output->isClosed());
    }

    public function testFlush()
    {
        $output = new PipedOutputStream();
        $this->assertSame($output, $output->flush());
    }

    /**
     * Closes a connected piped output stream. A listener is registered at
     * 'close.notify.before', so when the listener is called, we know the
     * downstream is notified.
     */
    public function testClose()
    {
        // Initializes a piped input stream.
        $downstream = new PipedInputStream();

        // Initializes a piped output stream.
        $output = new PipedOutputStream($downstream);

        // Initializes an event dispatcher.
        $dispatcher = new EventDispatcher();

        // Initializes the listener.
        $listener = [new MarkerListener(), 'mark'];

        // Adds the dispacher and listener to the piped output stream.
        $output
            ->setEventDispatcher($dispatcher)
            ->addListener($output::EVENT_CLOSE_NOTIFY_BEFORE, $listener);

        // Closes the piped output stream.
        $output->close();

        // Asserts the stream has been closed.
        $this->assertTrue($output->isClosed());

        // Asserts notify() method of the piped input stream has been called.
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
        $output = new PipedOutputStream();
        $output->close();
        $output->close();
    }

    /**
     * Writes data to a piped output stream.
     * A listener is listening at write.receive before event, so if the listener
     * is triggered, we know the receive() method of the downstream is called.
     */
    public function testOutput()
    {
        // Initializes a piped input stream.
        $downstream = new PipedInputStream();

        // Initializes a piped output stream.
        $output = new PipedOutputStream($downstream);

        // Initializes an event dispatcher.
        $dispatcher = new EventDispatcher();

        // Initializes a listener.
        $listener = [new MarkerListener(), 'mark'];

        // Adds the dispatcher and listener to the piped output stream.
        $output
            ->setEventDispatcher($dispatcher)
            ->addListener($output::EVENT_WRITE_RECEIVE_BEFORE, $listener);

        // Writes '*' to the piped output stream.
        $data = '*';
        $this->output->invoke($output, $data);

        // Asserts the receive() method of the piped input stream has been
        // called.
        $this->assertSame($listener[0], $listener[0]->markers[0]);

        // Asserts '*' has been written to the piped output stream.
        $this->assertEquals($data, implode('', array_values((array)$downstream->buffer)));
    }

    /**
     * Writes data to a piped output stream, when the buffer of its downstream
     * is full.
     *
     * The producer thread is blocked first. This can be confirmed by checking
     * the 'waiting' status of the downstream object.
     *
     * To wake up the producer thread, a byte is shifted out of the buffer and
     * the downstream is notified.
     *
     * Finally, check the 'waiting' status again to confirm the producer thread
     * has completed its job.
     */
    public function testOutputWhenBufferIsFull()
    {
        // Initializes a piped input stream.
        $downstream = new PipedInputStream();

        // Initializes buffer and fills it up with '*'.
        $buffer = $downstream->buffer;
        for ($i = 0; $i < PipedInputStream::BUFFER_SIZE; $i++) {
            $buffer[] = '*';
        }

        $data = '*';

        // Initializes a piped output stream.
        $output = new PipedOutputStream($downstream);

        // Initializes a producer.
        $producer = new Producer($output, $data);

        // Starts the producer. Because buffer is full, the thread will wait.
        $producer->start();

        // Shifts one byte off the buffer and notify the thread.
        $downstream->synchronized(function(){

            $buffer = $this->buffer;

            $buffer->shift();

            $this->notify();
        });

        // Waits for the thread to finish its job.
        $producer->join();

        $this->assertTrue(true);

        // Now asserts the thread is not waiting.
        $this->assertFalse($downstream->isRunning());
    }

    /**
     * Writes data to a closed piped output stream.
     *
     * @expectedException ZerusTech\Component\IO\Exception\IOException
     * @expectedExceptionMessage Can't write to a closed stream.
     */
    public function testOutputOnClosedStream()
    {
        $output = new PipedOutputStream();
        $output->close();
        $this->output->invoke($output, 'hello');
    }

    /**
     * Writes data to a piped output stream, and the downstream of which is
     * null.
     *
     * @expectedException ZerusTech\Component\IO\Exception\IOException
     * @expectedExceptionMessage Current stream is not connected to any downstream.
     */
    public function testOutputWithNullDownstream()
    {
        $output = new PipedOutputStream();
        $this->output->invoke($output, 'hello');
    }

    public function testWrite()
    {
        $input = new PipedInputStream();
        $output = new PipedOutputStream($input);
        $output->write('hello');
        $this->assertEquals(str_split('hello', 1), array_values((array)$input->buffer));
    }

    /**
     * @dataProvider getDataForTestWriteSubstring
     */
    public function testWriteSubstring($sourceBytes, $offset, $length, $actualBytes)
    {
        $input = new PipedInputStream();
        $output = new PipedOutputStream($input);
        $output->writeSubstring($sourceBytes, $offset, $length);
        $this->assertEquals(str_split($actualBytes, 1), array_values((array)$input->buffer));
    }

    public function getDataForTestWriteSubstring()
    {
        return [
            ['hello', 0, 5, 'hello'],
            ['hello', 2, 3, 'llo'],
            ['hello', 2, 4, 'llo'],
            ['hello', -1, 1, 'o'],
            ['hello', -3, 3, 'llo'],
            ['hello', -3, 4, 'llo'],
            ['hello', -5, 5, 'hello'],
            ['hello', -6, 5, 'hello'],
            ['hello', 0, -1, 'hell'],
            ['hello', 0, -3, 'he'],
            ['hello', 1, -1, 'ell'],
            ['hello', 1, -2, 'el'],
            ['hello', 1, -2, 'el'],
            ['', 0, 0, ''],
            ['', 0, 3, ''],
            ['hello', 0, 0, ''],
            ['hello', 0, -5, ''],
            ['hello', 1, -4, ''],
            ['hello', 1, -5, ''],
            ['hello', -1, 0, ''],
            ['hello', -2, -2, ''],
            ['hello', -3, -4, ''],
        ];
    }

    /**
     * @dataProvider getDataForTestWriteSubstringException
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Invalid offset or length.
     */
    public function testWriteSubstringException($sourceBytes, $offset, $length)
    {
        $input = new PipedInputStream();
        $output = new PipedOutputStream($input);
        $output->writeSubstring($sourceBytes, $offset, $length);
    }

    public function getDataForTestWriteSubstringException()
    {
        return [
            ['hello', 5, 1],
            ['hello', 0, false],
            ['hello', 0, null],
        ];
    }
}
