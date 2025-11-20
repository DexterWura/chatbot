<?php

namespace Chatbot\Core;

/**
 * Event Dispatcher - Observer Pattern
 */
class EventDispatcher {
    private array $listeners = [];

    public function subscribe(string $event, callable $listener): void {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }
        $this->listeners[$event][] = $listener;
    }

    public function unsubscribe(string $event, callable $listener): void {
        if (!isset($this->listeners[$event])) {
            return;
        }
        $this->listeners[$event] = array_filter(
            $this->listeners[$event],
            fn($l) => $l !== $listener
        );
    }

    public function dispatch(string $event, array $data = []): void {
        if (!isset($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $listener) {
            call_user_func($listener, $data);
        }
    }

    public function clear(string $event = null): void {
        if ($event === null) {
            $this->listeners = [];
        } else {
            unset($this->listeners[$event]);
        }
    }
}
