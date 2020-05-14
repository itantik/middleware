<?php

declare(strict_types=1);

namespace Tests\Assets;

abstract class Loggable
{
    /** @var string[] */
    private $logs = [];

    public function addLog(string $message): void
    {
        $this->logs[] = $message;
    }

    /**
     * @return string[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
