<?php
interface BaseProvider {
    public function name(): string;
    public function models(): array;
    /**
     * @param array $messages Array of [role => 'system'|'user'|'assistant', content => string]
     * @param array $options  Provider specific options like model, temperature, etc.
     * @return array          [ok => bool, reply => string, raw => mixed]
     */
    public function chat(array $messages, array $options = []): array;
}
?>


