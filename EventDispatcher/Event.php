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
class Event extends \Volatile
{
    /**
     * @var bool Whether no further event listeners should be triggered.
     */
    private $propagationStopped = false;

    /**
     * @var \Threaded The event subject.
     */
    private $subject;

    /**
     * @var \Threaded The event arguments.
     */
    private $arguments;

    /**
     * Constructor.
     * @param \Threaded $subject The event subject.
     * @param \Threaded $arguments The event arguments.
     */
    public function __construct(\Threaded $subject = null, $arguments = null)
    {
        $this->subject = $subject;

        $this->arguments = (null !== $arguments ? $arguments : new \Threaded());
    }

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

    /**
     * Gets the event subject.
     * @returns \Threaded The event subject.
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Gets argument by key from the event arguments.
     * @param string $key The argument key.
     * @return mixed The argument value.
     */
    public function getArgument($key)
    {
        if ($this->hasArgument($key)) {
            return $this->arguments[$key];
        }

        throw new \InvalidArgumentException(sprintf('Argument "%s" not found.', $key));
    }

    /**
     * Adds an argument to event.
     * @param string $key The key of the argument.
     * @param mixed $value The argument value.
     * @return Event Current instance.
     */
    public function setArgument($key, $value)
    {
        $this->arguments[$key] = $value;

        return $this;
    }

    /**
     * Returns all arguments of current event.
     * @return \Threaded The arguments.
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Set arguments for current event.
     * @param \Threaded The arguments.
     * @return Event Current instance.
     */
    public function setArguments(\Threaded $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Checks if event contains an argument with key ``$key``.
     * @rturns bool True if argument ``$key`` exists, and false otherwise.
     */
    public function hasArgument($key)
    {
        return array_key_exists($key, $this->arguments);
    }
}
