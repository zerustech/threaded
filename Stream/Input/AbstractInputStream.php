<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For full copyright and license information, please view the LICENSE file that
 * was distributed with this source code.
 */

namespace ZerusTech\Component\Threaded\Stream\Input;

use ZerusTech\Component\IO\Stream\Input\InputStreamInterface;
use ZerusTech\Component\IO\Stream\ClosableInterface;
use ZerusTech\Component\IO\Exception\IOException;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcherContainer;

/**
 * The abstract class is the superclass of all classes representing a
 * thread-safe input stream.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
abstract class AbstractInputStream extends EventDispatcherContainer implements InputStreamInterface, ClosableInterface
{
    /**
     * Name of the event before connection.
    */
    const EVENT_CONNECT_BEFORE = 'connect.before';

    /**
     * @var bool A boolean that indicates whether current stream is closed.
     */
    protected $closed;

    /**
     * Create a new input stream instance.
     */
    public function __construct()
    {
        $this->closed = false;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function read($length = 1);

    /**
     * {@inheritdoc}
     */
    public function available()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function mark($readLimit)
    {
        // Do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function markSupported()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        throw new IOException(sprintf("%s", "mark/reset not supported."));
    }

    /**
     * This method skips the specified number of bytes in the stream. It retruns
     * the actual number of bytes skipped, which may be less than the requred
     * amount.
     *
     * @param int $byteCount The requested number of bytes to skip.
     * @return int The actual number of bytes skipped.
     * @throws IOException If an error occurs.
     */
    public function skip($byteCount)
    {
        return strlen($this->read($byteCount));
    }

    /**
     * Closes current stream.
     */
    public function close()
    {
        if (true === $this->closed) {

            throw new IOException(sprintf("Stream is already closed, can't be closed again."));
        }

        $this->closed = true;
    }

    /**
     * Checks whether current stream is closed or not.
     *
     * @return bool True if current stream is closed, and false otherwise.
     */
    public function isClosed()
    {
        return $this->closed;
    }
}
