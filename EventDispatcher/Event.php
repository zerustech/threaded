<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For full copyright and license information, please view the LICENSE file that
 * was distributed with this source code.
 */
namespace ZerusTech\Component\Threaded\EventDispatcher;

/**
 * Generic threaded event class.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class Event extends \Threaded
{
    /**
     * @var bool Whether no further event listeners should be triggered.
     */
    private $propagationStopped = false;

    /**
     * Checks if no further event listeners should be triggered.
     * @return True If no further listeners should be triggered, and false
     * otherwise.
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }

    /**
     * Stops event propagation.
     * @return Event Current instance.
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;

        return $this;
    }
}
