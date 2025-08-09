<?php

declare(strict_types=1);

namespace IslamWiki\Core\Wiki;

/**
 * NamespaceManager
 *
 * Lightweight registry and parser for MediaWiki-style namespaces.
 */
class NamespaceManager
{
    /**
     * List of recognized content namespaces. Excludes Special.
     * Keys are canonical names; values are arrays of aliases (case-insensitive).
     */
    private const CONTENT_NAMESPACES = [
        'Main' => [''],
        'wiki' => ['Wiki'],
        'Quran' => ['Qur\'an', 'Qur_an', 'QURAN'],
        'Hadith' => ['Ahadith', 'HADITH'],
    ];

    /**
     * Parse a raw title string like "Quran:Ayat al-Kursi" into [namespace, title].
     * If no explicit namespace, returns ['Main', rawTitle].
     */
    public static function parseTitle(string $rawTitle): array
    {
        $rawTitle = ltrim($rawTitle, '/');
        $parts = explode(':', $rawTitle, 2);
        if (count($parts) === 2) {
            [$nsPart, $titlePart] = $parts;
            $ns = self::normalizeNamespace($nsPart);
            if ($ns !== null) {
                return [$ns, $titlePart];
            }
        }
        return ['Main', $rawTitle];
    }

    /**
     * Determine if the given namespace is the Special namespace.
     */
    public static function isSpecial(string $ns): bool
    {
        return strcasecmp($ns, 'Special') === 0;
    }

    /**
     * Return list of canonical namespaces, including Special.
     */
    public static function listNamespaces(): array
    {
        $list = array_keys(self::CONTENT_NAMESPACES);
        // Ensure canonical casing
        sort($list);
        return array_merge(['Special'], $list);
    }

    /**
     * Normalize an input namespace string to its canonical form, or null if unknown.
     */
    public static function normalizeNamespace(string $input): ?string
    {
        $inputLower = strtolower(trim($input));
        if ($inputLower === 'special') {
            return 'Special';
        }
        foreach (self::CONTENT_NAMESPACES as $canonical => $aliases) {
            if (strtolower($canonical) === $inputLower) {
                return $canonical;
            }
            foreach ($aliases as $alias) {
                if (strtolower($alias) === $inputLower) {
                    return $canonical;
                }
            }
        }
        return null;
    }
}
