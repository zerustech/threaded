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

use ZerusTech\Component\Threaded\Stream\Output\PipedOutputStreamInterface;

/**
 * This is a simple producer class that writes bytes into the piped output
 * stream. It's used by the test case to do unit testing.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class Producer extends \Thread
{
    /**
     * Constructor.
     *
     * @param PipedOutputStreamInterface $upstream The piped output stream.
     * @param string $data The string to be written.
     */
    public function __construct(PipedOutputStreamInterface $upstream, $data)
    {
        $this->upstream = $upstream;

        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->upstream->write($this->data);
    }
}
