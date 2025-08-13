<?php

namespace QuranExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BreadcrumbsExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('quran_breadcrumbs', [$this, 'generateBreadcrumbs'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Generate breadcrumbs for Quran navigation
     *
     * @param int|null $surahNumber
     * @param int|null $ayahNumber
     * @return string
     */
    public function generateBreadcrumbs(?int $surahNumber = null, ?int $ayahNumber = null): string
    {
        $breadcrumbs = [];
        
        // Always add the Quran home link
        $breadcrumbs[] = $this->generateBreadcrumbItem('Quran', '/quran');
        
        if ($surahNumber) {
            $surahName = $this->getSurahName($surahNumber);
            $breadcrumbs[] = $this->generateBreadcrumbItem(
                "Surah $surahNumber $surahName",
                "/quran/$surahNumber"
            );
            
            if ($ayahNumber) {
                $breadcrumbs[] = $this->generateBreadcrumbItem(
                    "Ayah $ayahNumber",
                    "/quran/$surahNumber/$ayahNumber",
                    true
                );
            }
        }
        
        return implode(' : ', $breadcrumbs);
    }
    
    /**
     * Generate a single breadcrumb item
     */
    private function generateBreadcrumbItem(string $label, string $url, bool $isActive = false): string
    {
        if ($isActive) {
            return sprintf('<span class="breadcrumb-item active">%s</span>', htmlspecialchars($label));
        }
        
        return sprintf(
            '<a href="%s" class="breadcrumb-link">%s</a>',
            htmlspecialchars($url),
            htmlspecialchars($label)
        );
    }
    
    /**
     * Get surah name by number (simplified - in a real app, this would come from a repository)
     */
    private function getSurahName(int $surahNumber): string
    {
        $surahs = [
            1 => 'Al-Fatihah',
            2 => 'Al-Baqarah',
            // Add all 114 surahs in a real implementation
        ];
        
        return $surahs[$surahNumber] ?? "Surah $surahNumber";
    }
}
