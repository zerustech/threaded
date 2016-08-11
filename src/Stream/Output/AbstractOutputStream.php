<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For full copyright and license information, please view the LICENSE file that
 * was distributed with this source code.
 */

namespace ZerusTech\Component\Threaded\Stream\Output;

use ZerusTech\Component\IO\Stream\ClosableInterface;
use ZerusTech\Component\IO\Stream\FlushableInterface;
use ZerusTech\Component\IO\Exception\IOException;
use ZerusTech\Component\IO\Stream\Output\OutputStreamInterface;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcherContainer;

/**
 * The abstract class is the superclass of all classes representing a
 * thread-safe output stream.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
abstract class AbstractOutputStream extends EventDispatcherContainer implements OutputStreamInterface, ClosableInterface, FlushableInterface
{
    /**
     * Name of the event before connection.
    */
    const EVENT_CONNECT_BEFORE = 'connect.before';

    /**
     * @var bool This is a boolean that indicates if current stream has been
     * closed.
     */
    protected $closed;

    /**
     * This method creates a new output stream.
     */
    public function __construct()
    {
        $this->closed = false;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (true === $this->closed) {

            throw new IOException(sprintf("Stream is already closed, can't be closed again."));
        }

        $this->closed = true;
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed()
    {
        return $this->closed;
    }
}
