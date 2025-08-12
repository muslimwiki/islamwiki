<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing simplified NizamApplication...\n";

try {
    // Create basic components
    $container = new \IslamWiki\Core\Container\AsasContainer();
    $logger = new \IslamWiki\Core\Logging\ShahidLogger(__DIR__ . '/logs');
    $connection = new \IslamWiki\Core\Database\Connection([]);
    $session = new \IslamWiki\Core\Session\WisalSession([]);

    echo "✓ Basic components created\n";

    // Test system initialization (without service providers)
    echo "Testing system initialization...\n";

    $auth = new \IslamWiki\Core\Auth\AmanSecurity($session, $connection);
    echo "✓ Auth initialized\n";

    $cache = new \IslamWiki\Core\Caching\RihlahCaching($container, $logger, $connection);
    echo "✓ Cache initialized\n";

    $queue = new \IslamWiki\Core\Queue\SabrQueue($container, $logger, $connection);
    echo "✓ Queue initialized\n";

    $knowledge = new \IslamWiki\Core\Knowledge\UsulKnowledge($container, $logger, $connection);
    echo "✓ Knowledge initialized\n";

    $search = new \IslamWiki\Core\Search\IqraSearch($connection);
    echo "✓ Search initialized\n";

    $formatter = new \IslamWiki\Core\Formatter\BayanFormatter($connection, $logger);
    echo "✓ Formatter initialized\n";

    $config = new \IslamWiki\Core\Configuration\TadbirConfiguration($logger);
    echo "✓ Config initialized\n";

    echo "✓ All systems initialized successfully!\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "Simplified app test completed.\n";
