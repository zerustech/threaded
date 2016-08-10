<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace ZerusTech\Component\Threaded\Stream\Output;

use ZerusTech\Component\IO\Exception\IOException;
use ZerusTech\Component\Threaded\Stream\Input\PipedInputStreamInterface;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcher;
use ZerusTech\Component\Threaded\EventDispatcher\Event;

/**
 * A piped output stream can be connected to a piped input stream to create a
 * communication pipe.
 *
 * The piped output stream is the sending end of the pipe. Typically, data is
 * written to a piped output stream object by one thread and data is read from
 * the connected piped input stream by some other thread.
 *
 * Attempting to use both objects from a single thread is not recommended as it
 * may deadlock the thread.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class PipedOutputStream extends AbstractOutputStream implements PipedOutputStreamInterface
{
    /**
     * @var PipedInputStream The input stream to connect.
     */
    private $downstream;

    /**
     * Name of the event before calling receive() in write() method.
     */
    const EVENT_WRITE_RECEIVE_BEFORE = 'write.receive.before';

    /**
     * Name of the event before notification in close() method.
     */
    const EVENT_CLOSE_NOTIFY_BEFORE = 'close.notify.before';

    /**
     * Constructor.
     *
     * @param PipedInputStreamInterface $downstream The input stream to connect.
     */
    public function __construct(PipedInputStreamInterface $downstream = null)
    {
        $this->downstream = $downstream;

        $this->closed = false;

        if (null !== $this->downstream) {

            // Forces downstream to connect to current stream no matter it
            // is already connected or not and allows the downstream to call
            // the ``connect()`` method of current stream to complete the
            // connection.
            $this->downstream->connect($this, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function connect(PipedInputStreamInterface $downstream, $force = false, $reverse = true)
    {
        $this->dispatch(self::EVENT_CONNECT_BEFORE);

        if (false === $force && null !== $this->downstream && false === $this->closed) {

            throw new IOException(sprintf("Already connected."));
        }

        $this->downstream = $downstream;

        $this->closed = false;

        if (true === $reverse) {

            $downstream->connect($this, true, false);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        // Do nothing.
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        if (true === $this->closed) {

            throw new IOException(sprintf("Can't write to a closed stream."));
        }

        if (null === $this->downstream) {

            throw new IOException(sprintf("Current stream is not connected to any downstream."));
        }

        if (null !== $string) {

            $data = str_split($string);

            foreach ($data as $byte) {

                $this->dispatch(self::EVENT_WRITE_RECEIVE_BEFORE);
                // Writes byte to downstream.
                // The "writer" thread will be blocked if downstream is full.
                $this->downstream->receive($byte);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (true === $this->closed) {

            throw new IOException(sprintf("Already closed."));
        }

        $this->closed = true;

        if (null !== $this->downstream) {

            $this->dispatch(self::EVENT_CLOSE_NOTIFY_BEFORE);
            // Notify the waiting "reader" thread that the upstream is now closed,
            // so that they should stop waiting.
            $this->downstream->notify();
        }

        return $this;
    }
}
