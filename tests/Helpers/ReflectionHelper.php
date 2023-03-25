<?php

namespace RebelCode\Atlas\Test\Helpers;

use ReflectionClass;

trait ReflectionHelper
{
    /**
     * @template T
     * @param T $object
     */
    public function expose($object)
    {
        return new class($object) {
            protected object $object;
            protected ReflectionClass $ref;

            public function __construct(object $object)
            {
                $this->object = $object;
                $this->ref = new ReflectionClass($object);
            }

            public function __get(string $name)
            {
                $prop = $this->ref->getProperty($name);
                $prop->setAccessible(true);
                return $prop->getValue($this->object);
            }

            public function __call(string $name, array $args)
            {
                $method = $this->ref->getMethod($name);
                $method->setAccessible(true);

                return $method->invokeArgs($this->object, $args);
            }
        };
    }
}
