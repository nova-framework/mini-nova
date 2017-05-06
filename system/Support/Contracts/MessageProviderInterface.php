<?php

namespace Mini\Support\Contracts;


interface MessageProviderInterface
{
    /**
     * Get the messages for the instance.
     *
     * @return \Mini\Support\MessageBag
     */
    public function getMessageBag();
}
