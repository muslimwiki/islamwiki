<?php
declare(strict_types=1);

namespace IslamWiki\Core\Knowledge\Interfaces;

/**
 * Root System Interface
 * 
 * Defines the contract for root systems that extract principles
 * from Islamic texts like Qur'anic verses and Hadith.
 */
interface RootSystemInterface
{
    /**
     * Extract principles from text.
     * 
     * @param string $text The text to analyze
     * @return array Array of extracted principles
     */
    public function extractPrinciples(string $text): array;
    
    /**
     * Get root system type.
     * 
     * @return string The type of root system
     */
    public function getType(): string;
    
    /**
     * Validate text for this root system.
     * 
     * @param string $text The text to validate
     * @return bool True if valid for this root system
     */
    public function validateText(string $text): bool;
} 