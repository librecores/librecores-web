<?php

namespace TestUtils\Mocks;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class MockProducer implements ProducerInterface
{
    /**
     * @var array
     */
    private $publishedMessages = [];


    /**
     * @return array
     */
    public function getPublishedMessages(): array
    {
        return $this->publishedMessages;
    }

    /**
     * @inheritDoc
     */
    public function publish($msgBody, $routingKey = '', $additionalProperties = array())
    {
        $this->publishedMessages[] = [
            'body' => $msgBody,
            'routingKey' => $routingKey,
            'additionalProperties' => $additionalProperties,
        ];
    }
}
