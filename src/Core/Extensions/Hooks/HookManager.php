<?php
declare(strict_types=1);

namespace IslamWiki\Core\Extensions\Hooks;

/**
 * Hook Manager
 * 
 * Manages hooks for the extension system, allowing extensions to
 * register callbacks that are executed at specific points in the application.
 */
class HookManager
{
    /**
     * @var array Registered hooks
     */
    private array $hooks = [];

    /**
     * @var array Hook priorities
     */
    private array $priorities = [];

    /**
     * Register a hook
     *
     * @param string $hookName The name of the hook
     * @param callable $callback The callback function
     * @param int $priority The priority (lower numbers = higher priority)
     */
    public function register(string $hookName, callable $callback, int $priority = 10): void
    {
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
            $this->priorities[$hookName] = [];
        }

        $this->hooks[$hookName][] = $callback;
        $this->priorities[$hookName][] = $priority;

        // Sort by priority
        array_multisort($this->priorities[$hookName], SORT_ASC, $this->hooks[$hookName]);
    }

    /**
     * Run a hook
     *
     * @param string $hookName The name of the hook
     * @param array $args Arguments to pass to the hook callbacks
     * @return array Results from all hook callbacks
     */
    public function run(string $hookName, array $args = []): array
    {
        $results = [];

        if (!isset($this->hooks[$hookName])) {
            return $results;
        }

        foreach ($this->hooks[$hookName] as $callback) {
            try {
                $result = call_user_func_array($callback, $args);
                if ($result !== null) {
                    $results[] = $result;
                }
            } catch (\Exception $e) {
                // Log the error but continue with other hooks
                error_log("Hook error in {$hookName}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Run a hook and return the first non-null result
     *
     * @param string $hookName The name of the hook
     * @param array $args Arguments to pass to the hook callbacks
     * @return mixed The first non-null result, or null if no results
     */
    public function runFirst(string $hookName, array $args = [])
    {
        if (!isset($this->hooks[$hookName])) {
            return null;
        }

        foreach ($this->hooks[$hookName] as $callback) {
            try {
                $result = call_user_func_array($callback, $args);
                if ($result !== null) {
                    return $result;
                }
            } catch (\Exception $e) {
                // Log the error but continue with other hooks
                error_log("Hook error in {$hookName}: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Run a hook and return the last result
     *
     * @param string $hookName The name of the hook
     * @param array $args Arguments to pass to the hook callbacks
     * @return mixed The last result, or null if no results
     */
    public function runLast(string $hookName, array $args = [])
    {
        if (!isset($this->hooks[$hookName])) {
            return null;
        }

        $lastResult = null;

        foreach ($this->hooks[$hookName] as $callback) {
            try {
                $result = call_user_func_array($callback, $args);
                if ($result !== null) {
                    $lastResult = $result;
                }
            } catch (\Exception $e) {
                // Log the error but continue with other hooks
                error_log("Hook error in {$hookName}: " . $e->getMessage());
            }
        }

        return $lastResult;
    }

    /**
     * Check if a hook has any registered callbacks
     *
     * @param string $hookName The name of the hook
     * @return bool True if the hook has callbacks
     */
    public function hasHook(string $hookName): bool
    {
        return isset($this->hooks[$hookName]) && !empty($this->hooks[$hookName]);
    }

    /**
     * Get all registered hooks
     *
     * @return array Array of hook names
     */
    public function getHooks(): array
    {
        return array_keys($this->hooks);
    }

    /**
     * Get callbacks for a specific hook
     *
     * @param string $hookName The name of the hook
     * @return array Array of callbacks
     */
    public function getHookCallbacks(string $hookName): array
    {
        return $this->hooks[$hookName] ?? [];
    }

    /**
     * Remove all callbacks for a hook
     *
     * @param string $hookName The name of the hook
     */
    public function clearHook(string $hookName): void
    {
        unset($this->hooks[$hookName]);
        unset($this->priorities[$hookName]);
    }

    /**
     * Remove all hooks
     */
    public function clearAllHooks(): void
    {
        $this->hooks = [];
        $this->priorities = [];
    }

    /**
     * Get hook statistics
     *
     * @return array Statistics about registered hooks
     */
    public function getStatistics(): array
    {
        $stats = [
            'total_hooks' => count($this->hooks),
            'total_callbacks' => 0,
            'hooks' => [],
        ];

        foreach ($this->hooks as $hookName => $callbacks) {
            $stats['total_callbacks'] += count($callbacks);
            $stats['hooks'][$hookName] = [
                'callbacks' => count($callbacks),
                'priorities' => $this->priorities[$hookName] ?? [],
            ];
        }

        return $stats;
    }
} 