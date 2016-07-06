[![Build Status](https://api.travis-ci.org/zerustech/threaded.svg?branch=v2.0.x)](https://travis-ci.org/zerustech/threaded)

ZerusTech Threaded Component
================================================
The *ZerusTech Threaded Component* is a library that provides some commonly used
thread-safe classes, such as piped input stream and piped output stream.

Thanks to the [krakjoe/pthreads][2] extension, it's eventually possible to
develop multi-threaded applications in php-cli.

::: info-box note

This is version 2.x, which works with PHP 7.x and pthreads 3.x.

If you want to use pthreads 2.x, please install version 1.x instead.

:::

Installation
-------------

You can install this component in 2 different ways:

* Install it via Composer
```bash
$ cd <project-root-directory>
$ composer require zerustech/threaded
```

* Use the official Git repository [zerustech/threaded][4]

Examples
-------------

### Piped Streams ###

A piped input stream connects itself with a piped output stream, the upstream,
and reads data from the upstream, and a piped output stream connects itself to
a piped input stream, the downstream and writes data to the downstream.

Both of them are thread-safe classes, therefore they can be used to build
multi-threaded applications.

When the upstream is empty, the piped input stream will be blocked, and if the
downstream is full, the piped output stream wil be blocked as well.

The blocked piped input stream will be notified, as soon as the piped output
stream writes any data to it and is closed, and the blocked piped output stream
will be notified, as soon as the piped input stream reads any data from the
upstream or is closed.

```php
<?php

require_once __DIR__.'/vendor/autoload.php';

use ZerusTech\Component\Threaded\Stream\Input\PipedInputStream;
use ZerusTech\Component\Threaded\Stream\Output\PipedOutputStream;

/**
 * The consumer class that reads data from a piped input stream.
 *
 * @author Michael Lee <michae.lee@zerustech.com>
 */
class Consumer extends \Thread
{
    /**
     * @var PipedInputStream $input The piped input stream.
     */
    private $input;

    /**
     * @var int $lenght The number of bytes to be read. 
     */
    private $length;

    /**
     * Constructor.
     *
     * @param PipedInputStream $input The piped input stream.
     * @param int $length The number of bytes to be read.
     */
    public function __construct(PipedInputStream $input, $length)
    {
        $this->input = $input;

        $this->length = $length;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $remaining = $this->length;

        while (0 < $remaining--) {

            // If the piped input stream is empty, the thread will be blocked.
            $data = $this->input->read();

            printf("%s", $data);
        }
    }
}

// Initializes a piped output stream.
$output = new PipedOutputStream();

// Initializes a piped input stream.
$input = new PipedInputStream();

// Connects the piped output stream with the piped input stream.
$input->connect($output);

// Initializes a consumer.
$consumer = new Consumer($input, 5);

// Starts the consumer thread.
// It will try to read up to 5 bytes from the piped input stream.
$consumer->start();

for ($i = 0; $i < 5; $i++) {

    // Writes one byte per time to the piped output stream
    // The blocked consumer thread will be notified as soon as the byte is 
    // written to the piped output stream.
    $output->write('*');

    sleep(1);
}

// Waits till the consumer thread ends.
$consumer->join();

```

References
----------
* [The krakjoe/pthreads project][2]
* [The zerustech/io project][3]
* [The zerustech/threaded project][4]
* [The zerustech/terminal][5]


[1]:  https://opensource.org/licenses/MIT "The MIT License (MIT)"
[2]:  https://github.com/krakjoe/pthreads "The krakjoe/pthreads Project"
[3]:  https://github.com/zerustech/io "The zerustech/io Project"
[4]:  https://github.com/zerustech/threaded "The zerustech/threaded Project"
[5]:  https://github.com/zerustech/terminal "The zerustech/terminal Project"

License
-------
The *ZerusTech Threaded Component* is published under the [MIT License][1].
