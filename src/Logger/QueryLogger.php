<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Logger;

use Psr\Log\AbstractLogger;

final class QueryLogger  extends AbstractLogger
{
    private array $logs = [];

    private static ?QueryLogger $queryLogger = null;

    public function log($level, $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }

    public function getLogs(): array
    {
        // This method can be implemented to return the logs if needed.
        // For now, it just returns an empty array.
        return $this->logs;
    }

    public static function getInstance(): self
    {
        if (!self::$queryLogger instanceof \Knp\DoctrineBehaviors\Logger\QueryLogger) {
            self::$queryLogger = new self();
        }

        return self::$queryLogger;
    }
}
