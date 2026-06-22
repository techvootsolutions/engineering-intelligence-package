<?php

namespace Dev\EipAgent\DTOs;

class Metrics
{
    public function __construct(
        public int $controllers = 0,
        public int $models = 0,
        public int $requests = 0,
        public int $middleware = 0,
        public int $policies = 0,
        public int $events = 0,
        public int $listeners = 0,
        public int $jobs = 0,
        public int $notifications = 0,
        public int $mail = 0,
        public int $providers = 0,
        public int $services = 0,
        public int $traits = 0,
        public int $helpers = 0,
        public int $commands = 0,
        public int $routes = 0,
        public int $configs = 0,
        public int $bootstrap = 0,
        public int $enums = 0,
        public int $observers = 0,
        public int $factories = 0,
        public int $seeders = 0,
        public int $migrations = 0,
        public int $channels = 0,
        public int $other = 0
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
    
    public function increment(string $type): void
    {
        if (property_exists($this, $type)) {
            $this->{$type}++;
        } else {
            $this->other++;
        }
    }
}
