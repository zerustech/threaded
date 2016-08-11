<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace ZerusTech\Component\Threaded\Stream\Input;

use ZerusTech\Component\IO\Exception\IOException;
use ZerusTech\Component\Threaded\Stream\Output\PipedOutputStream;
use ZerusTech\Component\Threaded\Stream\Output\PipedOutputStreamInterface;
use ZerusTech\Component\Threaded\EventDispatcher\Event;
use ZerusTech\Component\Threaded\EventDispatcher\EventDispatcher;

/**
 * A piped input stream can be connected to a piped output stream to create a
 * communications pipe.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 * @see PipedOutputStream
 */
class PipedInputStream extends AbstractInputStream implements PipedInputStreamInterface
{
    /**
     * @var PipedOutputStreamInterface The output stream to connect.
     */
    private $upstream;

    /**
     * @var \Threaded The queue shared by the input stream and the output
     * stream.
     */
    private $buffer;

    /**
     * The maximum number of bytes the buffer can hold.
     */
    const BUFFER_SIZE = 1024;

    /**
     * Name of the event before notification in close() method.
     */
    const EVENT_CLOSE_NOTIFY_BEFORE = 'close.notify.before';

    /**
     * Constructor.
     *
     * @param PipedOutputStreamInterface $upstream The piped output stream to
     * connect.
     */
    public function __construct(PipedOutputStreamInterface $upstream = null)
    {
        $this->buffer = new \Threaded();

        $this->upstream = $upstream;

        $this->closed = false;

        if (null !== $upstream) {

            $this->connect($upstream, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function connect(PipedOutputStreamInterface $upstream, $force = false, $reverse = true)
    {
        $this->dispatch(self::EVENT_CONNECT_BEFORE);

        if (false === $force && null !== $this->upstream && false === $this->closed) {

            throw new IOException(sprintf("Already connected."));
        }

        $this->upstream = $upstream;

        $this->closed = false;

        if (true === $reverse) {

            $this->upstream->connect($this, true, false);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length = 1)
    {
        return $this->synchronized(

            function($length){

                $remaining = $length;

                $data = '';

                while ($remaining > 0) {

                    while (0 === $this->buffer->count()) {

                        if (null !== $this->upstream && $this->upstream->isClosed()) {

                            // printf("The upstream is closed, so the downstream should stop waiting ... \n");

                            break 2;
                        }

                        // printf("The buffer is empty, so the downstream should wait ... \n");

                        $this->wait();
                        // Buffer is empty
                        // Waiting for upstream to produce more bytes.
                    }

                    // printf("The buffer is not empty, reading [%s] from the downstream ... \n", $this->buffer[0]);

                    $data .= $this->buffer->shift();

                    // Notify upstream to continue producing bytes.
                    // printf("The buffer is not full, so wake up the upstream ...\n");

                    $this->notify();

                    $remaining--;
                }

                return $data;

        }, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function receive($string)
    {
        return $this->synchronized(

            function($string){

                $data = str_split($string);

                foreach ($data as $byte) {

                    while ($this::BUFFER_SIZE === $this->buffer->count()) {

                        if ($this->isClosed()) {

                            // printf("The downstream is closed, so the upstream should stop waiting ... \n");

                            break 2;
                        }

                        // printf("The buffer is full, so the upstream should wait ... \n");

                        $this->wait();
                        // Buffer is full.
                        // Waiting for downstream to consume more bytes.
                    }

                    // printf("The buffer is not full, writing [%s] ... \n", $byte);

                    $this->buffer[] = $byte;

                    // Notify downstream to continue consuming bytes.
                    // printf("The buffer is not empty, so wake up the downstream ...\n");

                    $this->notify();
                }

                return $this;

        }, $string);
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

        $this->dispatch(self::EVENT_CLOSE_NOTIFY_BEFORE);
        // Notify waiting upstream that the downstream is closed, so that they
        // can stop waiting forever.
        $this->notify();

        return $this;
    }

    /**
     * {@inherit}
     */
    public function available()
    {
        return $this->synchronized(
            function () {
                return $this->buffer->count();
            }
        );
    }
}
