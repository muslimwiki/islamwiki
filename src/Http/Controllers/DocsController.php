<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

class DocsController extends Controller
{
    public function index(Request $request): Response
    {
        // Default to README.md
        return $this->show($request, 'README.md');
    }

    public function show(Request $request, string ...$segments): Response
    {
        $path = implode('/', $segments);
        $basePath = dirname(__DIR__, 3); // project root
        $docsDir = $basePath . '/docs';

        // Sanitize and resolve path
        $relativePath = trim($path, '/');
        if ($relativePath === '') {
            $relativePath = 'README.md';
        }
        $fullPath = realpath($docsDir . '/' . $relativePath);

        // Prevent path traversal
        $docsRoot = realpath($docsDir) ?: $docsDir;
        $prefix = $docsRoot . DIRECTORY_SEPARATOR;
        if ($fullPath === false || strncmp($fullPath, $prefix, strlen($prefix)) !== 0) {
            return new Response(404, ['Content-Type' => 'text/html'], '<h1>Doc not found</h1>');
        }

        // Build sidebar tree (nested) and expand ancestors of current path
        $currentRel = $this->relativePathFrom($fullPath, $docsDir);
        $tree = $this->buildDocsTree($docsDir, $currentRel);

        // Read file or fallback to index if directory
        if (is_dir($fullPath)) {
            $indexMd = $fullPath . '/README.md';
            if (file_exists($indexMd)) {
                $fullPath = $indexMd;
            } else {
                // Render directory listing as simple index
                $contentHtml = $this->renderDirectoryIndex($fullPath, $docsDir);
                return $this->view('docs/show', [
                    'title' => 'Docs',
                    'tree' => $tree,
                    'content' => $contentHtml,
                    'current' => $this->relativePathFrom($fullPath, $docsDir),
                ]);
            }
        }

        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            return new Response(404, ['Content-Type' => 'text/html'], '<h1>Doc not found</h1>');
        }

        $markdown = file_get_contents($fullPath) ?: '';

        // Let extensions preprocess/augment the markdown (e.g., EnhancedMarkdown)
        try {
            if ($this->container->has(\IslamWiki\Core\Extensions\ExtensionManager::class)) {
                $extMgr = $this->container->get(\IslamWiki\Core\Extensions\ExtensionManager::class);
                $hook = $extMgr->getHookManager();
                $pre = $hook->runLast('ContentParse', [$markdown, 'markdown']);
                if (is_string($pre) && $pre !== '') {
                    $markdown = $pre;
                }
            }
        } catch (\Throwable $e) {
            // non-fatal
        }

        // Basic markdown to HTML (lightweight) to avoid adding runtime deps
        $contentHtml = $this->basicMarkdownToHtml($markdown);

        // Allow post-render HTML enhancement by extensions
        try {
            if (isset($extMgr) && isset($hook)) {
                $post = $hook->runLast('ContentPostRender', [$contentHtml, 'docs']);
                if (is_string($post) && $post !== '') {
                    $contentHtml = $post;
                }
            }
        } catch (\Throwable $e) {
            // non-fatal
        }

        return $this->view('docs/show', [
            'title' => 'Docs',
            'tree' => $tree,
            'content' => $contentHtml,
            'current' => $this->relativePathFrom($fullPath, $docsDir),
        ]);
    }

    private function buildDocsTree(string $docsDir, ?string $currentRel = null): array
    {
        $root = rtrim($docsDir, DIRECTORY_SEPARATOR);
        $tree = $this->buildDirNode($root, '', $currentRel ?? '');
        // We only need children at root level for rendering (hide the synthetic root)
        return $tree['children'];
    }

    private function buildDirNode(string $absDir, string $relDir, string $currentRel): array
    {
        $entries = scandir($absDir) ?: [];
        $children = [];
        $isAncestorOpen = false;

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $absPath = $absDir . DIRECTORY_SEPARATOR . $entry;
            $relPath = ltrim($relDir . '/' . $entry, '/');

            // Skip hidden and vendor-like
            if (preg_match('/^(vendor|node_modules|\.git)$/', $entry)) {
                continue;
            }

            if (is_dir($absPath)) {
                $node = $this->buildDirNode($absPath, $relPath, $currentRel);
                $children[] = $node;
                if ($node['open']) {
                    $isAncestorOpen = true;
                }
            } elseif (preg_match('/\.(md|markdown)$/i', $entry)) {
                $children[] = [
                    'type' => 'file',
                    'path' => $relPath,
                    'name' => $entry,
                ];
                // If this file is the current, mark ancestor open
                if ($currentRel !== '' && $relPath === $currentRel) {
                    $isAncestorOpen = true;
                }
            }
        }

        // Sort: directories first by name, then files by name
        usort($children, function ($a, $b) {
            $aIsDir = ($a['type'] ?? 'file') === 'dir';
            $bIsDir = ($b['type'] ?? 'file') === 'dir';
            if ($aIsDir !== $bIsDir) {
                return $aIsDir ? -1 : 1;
            }
            return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
        });

        $node = [
            'type' => 'dir',
            'path' => $relDir,
            'name' => ($relDir === '') ? '' : basename($relDir),
            'children' => $children,
            'open' => $isAncestorOpen,
        ];

        // Root should be open by default
        if ($relDir === '') {
            $node['open'] = true;
        }

        return $node;
    }

    private function relativePathFrom(string $absolute, string $base): string
    {
        $abs = str_replace('\\', '/', realpath($absolute) ?: $absolute);
        $bas = str_replace('\\', '/', realpath($base) ?: $base);
        $rel = ltrim(substr($abs, strlen($bas)), '/');
        return $rel;
    }

    private function renderDirectoryIndex(string $dir, string $docsDir): string
    {
        $items = scandir($dir) ?: [];
        $links = [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            $rel = $this->relativePathFrom($path, $docsDir);
            $href = '/docs/' . str_replace(' ', '%20', $rel);
            $icon = is_dir($path) ? '📁' : '📄';
            $links[] = sprintf('<li><a href="%s">%s %s</a></li>', htmlspecialchars($href), $icon, htmlspecialchars($item));
        }
        $listItems = implode('', $links);
        $list = '<ul class="doc-index">' . $listItems . '</ul>';
        return '<h1>Index</h1>' . $list;
    }

    private function basicMarkdownToHtml(string $md): string
    {
        // Pre-process complex blocks (tables) and store placeholders
        $tables = [];
        $md = preg_replace_callback(
            '/(^\s*\|.*\|\s*$\n^\s*\|\s*[:\- ]+\|[\s:\-\|]*$\n(?:^\s*\|.*\|\s*$\n?)+)/m',
            function ($m) use (&$tables) {
                $block = trim($m[1]);
                $lines = preg_split('/\n/', $block);
                if (count($lines) < 2) {
                    return $block;
                }
                $header = array_shift($lines);
                $align = array_shift($lines);
                $headers = array_map('trim', array_map('strip_tags', explode('|', trim($header, '| '))));
                $aligns = array_map('trim', explode('|', trim($align, '| ')));
                $cols = count($headers);
                $alignMap = [];
                for ($i = 0; $i < $cols; $i++) {
                    $a = $aligns[$i] ?? '';
                    if (str_starts_with($a, ':') && str_ends_with($a, ':')) {
                        $alignMap[$i] = 'center';
                    } elseif (str_ends_with($a, ':')) {
                        $alignMap[$i] = 'right';
                    } else {
                        $alignMap[$i] = 'left';
                    }
                }
                $thead = '<thead><tr>';
                foreach ($headers as $i => $h) {
                    $thAlign = htmlspecialchars($alignMap[$i]);
                    $thText = htmlspecialchars($h);
                    $thead .= '<th style="text-align:' . $thAlign . '">' . $thText . '</th>';
                }
                $thead .= '</tr></thead>';
                $tbody = '<tbody>';
                foreach ($lines as $row) {
                    $row = trim($row);
                    if ($row === '') {
                        continue;
                    }
                    $cells = array_map('trim', explode('|', trim($row, '| ')));
                    $tbody .= '<tr>';
                    foreach ($cells as $i => $c) {
                        $tdAlign = htmlspecialchars($alignMap[$i] ?? 'left');
                        $tdText = htmlspecialchars($c);
                        $tbody .= '<td style="text-align:' . $tdAlign . '">' . $tdText . '</td>';
                    }
                    $tbody .= '</tr>';
                }
                $tbody .= '</tbody>';
                $htmlTable = '<table class="md-table">' . $thead . $tbody . '</table>';
                $key = '[[[TABLE_' . count($tables) . ']]]';
                $tables[$key] = $htmlTable;
                return $key;
            },
            $md
        );

        // Extremely small MD renderer:
        // headings, code fences, inline code, bold/italic, strikethrough,
        // links, images, lists, blockquotes, hr, progress
        $html = htmlspecialchars($md, ENT_QUOTES, 'UTF-8');
        // code fences
        $html = preg_replace('/```([\w-]*)\n([\s\S]*?)```/m', '<pre><code class="lang-$1">$2</code></pre>', $html);
        // indented code blocks (4 spaces)
        $html = preg_replace_callback(
            '/(^ {4}.+(?:\n {4}.+)*)/m',
            function ($m) {
                $block = preg_replace('/^ {4}/m', '', $m[1]);
                return '<pre><code>' . $block . '</code></pre>';
            },
            $html
        );
        // images ![alt](src)
        $html = preg_replace('/!\[([^\]]*)\]\(([^\)]+)\)/', '<img alt="$1" src="$2" class="md-image"/>', $html);
        // headings
        $html = preg_replace('/^######\s*(.+)$/m', '<h6>$1</h6>', $html);
        $html = preg_replace('/^#####\s*(.+)$/m', '<h5>$1</h5>', $html);
        $html = preg_replace('/^####\s*(.+)$/m', '<h4>$1</h4>', $html);
        $html = preg_replace('/^###\s*(.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^##\s*(.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^#\s*(.+)$/m', '<h1>$1</h1>', $html);
        // emphasis
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html);
        $html = preg_replace('/~~(.+?)~~/s', '<del>$1</del>', $html);
        // inline code
        $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
        // links [text](url)
        $html = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $html);
        // unordered lists
        $html = preg_replace('/^\s*[-*]\s+(.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>\n?)+/m', '<ul class="md-list">$0</ul>', $html);
        // blockquotes (group consecutive lines)
        $html = preg_replace_callback(
            '/(^&gt;\s.*(?:\n&gt;\s.*)*)/m',
            function ($m) {
                $block = preg_replace('/^&gt;\s?/m', '', $m[1]);
                return '<blockquote>' . $block . '</blockquote>';
            },
            $html
        );
        // horizontal rules
        $html = preg_replace('/^---$/m', '<hr/>', $html);
        // paragraphs
        $html = preg_replace('/^(?!<h\d|<ul>|<li>|<pre>|<\/ul>|<\/li>)([^\n<][^\n]*)$/m', '<p>$1</p>', $html);

        // Progress placeholders from EnhancedMarkdown pre-pass
        $html = preg_replace_callback(
            '/\[\[\[PROGRESS;percent=([0-9]+\.[0-9]+);label=([^;\]]*);classes=([^\]]*)\]\]\]/',
            function ($m) {
                $percent = (float)$m[1];
                $label = htmlspecialchars($m[2]);
                $classes = trim($m[3]);
                $cls = 'progress ';
                if ($classes !== '') {
                    foreach (explode(',', $classes) as $c) {
                        $c = trim($c);
                        if ($c !== '') {
                            $cls .= $c . ' ';
                        }
                    }
                }
                $bucket = ((int) floor($percent / 20)) * 20; // 0,20,40,60,80,100
                $cls .= 'progress-' . $bucket . 'plus';
                $bar = '<div class="progress-bar" style="width:' . number_format($percent, 2) . '%">'
                     . '<p class="progress-label">' . $label . '</p>'
                     . '</div>';
                return '<div class="' . trim($cls) . '">' . $bar . '</div>';
            },
            $html
        );

        // Restore tables
        if (!empty($tables)) {
            foreach ($tables as $key => $tableHtml) {
                $html = str_replace(htmlspecialchars($key, ENT_QUOTES, 'UTF-8'), $tableHtml, $html);
                $html = str_replace($key, $tableHtml, $html);
            }
        }
        return $html;
    }
}
