<?php

namespace Liuggio\Concurrent\Producer;

use Liuggio\Concurrent\Exception\StdInMustBeAValidResourceException;
use Liuggio\Concurrent\Queue\QueueInterface;

class StdInProducer implements ProducerInterface
{
    /** @var string */
    private $stdIn;
    /** @var resource */
    private $resource;

    public function __construct($stdIn = 'php://stdin')
    {
        $this->stdIn = $stdIn;
        $this->resource = null;
    }

    /**
     * {@inheritdoc}
     */
    public function produce(QueueInterface $queue)
    {
        $this->resource = @fopen($this->stdIn, 'r');
        $this->assertResourceIsValid();

        while (false !== ($line = fgets($this->resource))) {
            $this->addLineIfNotEmpty($queue, $line);
        }
        $queue->freeze();
    }

    public function __destruct()
    {
        if (null !== $this->resource) {
            @fclose($this->resource);
        }
    }

    private function addLineIfNotEmpty(QueueInterface $queue, $line)
    {
        $line = trim($line);
        if (!empty($line)) {
            $queue->enqueue($line);
        }
    }

    private function assertResourceIsValid()
    {
        if (!$this->resource) {
            throw new StdInMustBeAValidResourceException($this->stdIn);
        }
    }
}
