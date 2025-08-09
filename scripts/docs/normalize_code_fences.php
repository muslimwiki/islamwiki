<?php
declare(strict_types=1);

// Normalize code fences in Markdown files under docs/
// - Add language to ``` fences missing a language when it can be guessed

function guess_language(array $lines, int $startIndex): string {
    $i = $startIndex + 1; // first line after the opening ```
    $max = count($lines);
    while ($i < $max) {
        $line = rtrim($lines[$i], "\r\n");
        if (preg_match('/^```/', $line)) {
            // empty block
            return '';
        }
        if ($line !== '') {
            $l = ltrim($line);
            // PHP
            if (str_starts_with($l, '<?php') || preg_match('/\bnamespace\s+[A-Za-z_\\\\]/', $l)) {
                return 'php';
            }
            // Bash/CLI
            if (
                $l[0] === '$' ||
                preg_match('/^(sudo\s+)?(git|php|composer|npm|yarn|pnpm|make|curl|wget|apt|apt-get|service|systemctl|bash|sh)\b/', $l)
            ) {
                return 'bash';
            }
            // INI / env
            if (preg_match('/^[A-Z0-9_]+\s*=\s*[^\s]/', $l)) {
                return 'ini';
            }
            // JSON
            if (preg_match('/^\{\s*"|^\[\s*\{/', $l)) {
                return 'json';
            }
            // SQL
            if (preg_match('/^(SELECT|INSERT|UPDATE|DELETE|CREATE|ALTER|DROP)\b/i', $l)) {
                return 'sql';
            }
            // JS/TS
            if (preg_match('/\b(const|let|function|import|export)\b/', $l)) {
                return 'js';
            }
            // Default: leave unlabeled
            return '';
        }
        $i++;
    }
    return '';
}

$root = dirname(__DIR__, 2);
$docsDir = $root . '/docs';

$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($docsDir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$changed = [];
foreach ($iter as $file) {
    $path = $file->getPathname();
    if (!preg_match('/\.(md|markdown)$/i', $path)) {
        continue;
    }
    $contents = file_get_contents($path);
    if ($contents === false) {
        continue;
    }
    $lines = preg_split('/\R/', $contents, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    // preg_split with flags preserves separators; instead, split by lines preserving EOL via explode on "\n" and manage CR.
    $lines = explode("\n", str_replace("\r\n", "\n", $contents));

    $out = $lines;
    $modified = false;
    $i = 0;
    $n = count($lines);
    while ($i < $n) {
        $line = $lines[$i];
        if (preg_match('/^```(\s*)$/', $line)) {
            // unlabeled fence
            $lang = guess_language($lines, $i);
            if ($lang !== '') {
                $out[$i] = "```" . $lang;
                $modified = true;
            }
        }
        $i++;
    }

    if ($modified) {
        file_put_contents($path, implode("\n", $out));
        $changed[] = $path;
        echo "Updated fences: {$path}\n";
    }
}

echo "\nTotal updated files: " . count($changed) . "\n";


