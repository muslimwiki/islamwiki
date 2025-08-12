<?php

namespace IslamWiki\Core\Logging;

class Shahid
{
    public function info(string $message, array $context = []): void
    {
        echo "[INFO] $message\n";
        if (!empty($context)) {
            echo "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n";
        }
    }

    public function error(string $message, array $context = []): void
    {
        echo "[ERROR] $message\n";
        if (!empty($context)) {
            echo "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n";
        }
    }
}
