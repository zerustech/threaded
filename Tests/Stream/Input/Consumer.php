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

use ZerusTech\Component\Threaded\Stream\Input\PipedInputStreamInterface;

/**
 * This is a simple consumer class that reads bytes from the piped input stream.
 * It's used by the test case to do unit testing.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class Consumer extends \Thread
{
    /**
     * Constructor.
     *
     * @param PipedInputStreamInterface $downstream The piped input stream.
     * @param int $length The number of bytes to be read.
     */
    public function __construct(PipedInputStreamInterface $downstream, $length)
    {
        $this->downstream = $downstream;

        $this->length = $length;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $ref = new \ReflectionClass(get_class($this->downstream));
        $input = $ref->getMethod('input');
        $input->setAccessible(true);

        for ($i = 0; $i < $this->length; $i++) {

            $input->invokeArgs($this->downstream, [&$bytes, 1]);
        }
    }
}
