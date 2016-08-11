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
interface PipedOutputStreamInterface
{
    /**
     * Connects current stream to a piped input stream.
     *
     * @param PipedInputStreamInterface $downstream The piped input stream to
     * connect.
     * @param bool $force Controls whether to override the existing connection
     * or not. Default to false.
     * @param bool $reverse Controls whether the ``connect()`` method of
     * ``$downstream`` should be called to setup reverse connection.
     * @return PipedOutputStreamInterface Current instance.
     * @throws IOException If ``$force`` is false and current stream
     * has already connected to a piped input stream.
     */
    public function connect(PipedInputStreamInterface $downstream, $force = false, $reverse = true);
}
