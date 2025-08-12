<?php

declare(strict_types=1);

namespace IslamWiki\Models;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Query\Builder;
use DateTime;

/**
 * Islamic Page Model
 *
 * Enhanced page model with Islamic content features including:
 * - Islamic content categorization and tagging
 * - Scholar verification and approval workflow
 * - Islamic content templates and formatting
 * - Content moderation and quality control
 * - Islamic references and citations
 */
class IslamicPage extends Page
{
    /**
     * Islamic-specific fillable attributes.
     */
    protected array $islamicFillable = [
        'islamic_category',
        'islamic_tags',
        'scholar_verified',
        'verified_by',
        'verified_at',
        'verification_notes',
        'islamic_references',
        'islamic_citations',
        'content_quality_score',
        'islamic_template',
        'arabic_title',
        'arabic_content',
        'islamic_metadata',
        'islamic_permissions',
        'moderation_status',
        'moderated_by',
        'moderated_at',
        'moderation_notes',
    ];

    /**
     * Islamic-specific hidden attributes.
     */
    protected array $islamicHidden = [
        'verification_notes',
        'moderation_notes',
        'islamic_metadata',
    ];

    /**
     * Islamic-specific casts.
     */
    protected array $islamicCasts = [
        'scholar_verified' => 'boolean',
        'verified_at' => 'datetime',
        'islamic_references' => 'array',
        'islamic_citations' => 'array',
        'content_quality_score' => 'integer',
        'islamic_metadata' => 'array',
        'islamic_permissions' => 'array',
        'moderated_at' => 'datetime',
    ];

    /**
     * Islamic content categories.
     */
    protected array $islamicCategories = [
        'quran' => 'Quran Studies',
        'hadith' => 'Hadith Studies',
        'fiqh' => 'Islamic Jurisprudence',
        'aqeedah' => 'Islamic Creed',
        'seerah' => 'Prophetic Biography',
        'islamic_history' => 'Islamic History',
        'islamic_philosophy' => 'Islamic Philosophy',
        'islamic_ethics' => 'Islamic Ethics',
        'islamic_science' => 'Islamic Science',
        'islamic_art' => 'Islamic Art & Architecture',
        'islamic_literature' => 'Islamic Literature',
        'islamic_education' => 'Islamic Education',
        'islamic_society' => 'Islamic Society & Culture',
        'islamic_economics' => 'Islamic Economics',
        'islamic_medicine' => 'Islamic Medicine',
        'other' => 'Other Islamic Topics',
    ];

    /**
     * Islamic content templates.
     */
    protected array $islamicTemplates = [
                    'quran_ayah' => 'Quran Ayah Analysis',
        'hadith_study' => 'Hadith Study',
        'fiqh_ruling' => 'Fiqh Ruling',
        'scholar_biography' => 'Scholar Biography',
        'islamic_concept' => 'Islamic Concept',
        'historical_event' => 'Historical Event',
        'islamic_practice' => 'Islamic Practice',
        'comparative_study' => 'Comparative Study',
        'research_paper' => 'Research Paper',
        'general_article' => 'General Article',
    ];

    /**
     * Moderation statuses.
     */
    protected array $moderationStatuses = [
        'draft' => 'Draft',
        'pending' => 'Pending Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'needs_revision' => 'Needs Revision',
        'under_review' => 'Under Review',
    ];

    /**
     * Create a new Islamic page instance.
     */
    public function __construct(Connection $connection, array $attributes = [])
    {
        parent::__construct($connection, $attributes);

        // Merge Islamic fillable attributes
        $this->fillable = array_merge($this->fillable, $this->islamicFillable);
        $this->casts = array_merge($this->casts, $this->islamicCasts);
    }

    /**
     * Get the Islamic category name.
     */
    public function getIslamicCategoryName(): string
    {
        $category = $this->getAttribute('islamic_category');
        return $this->islamicCategories[$category] ?? 'Uncategorized';
    }

    /**
     * Get the Islamic template name.
     */
    public function getIslamicTemplateName(): string
    {
        $template = $this->getAttribute('islamic_template');
        return $this->islamicTemplates[$template] ?? 'General Article';
    }

    /**
     * Get the moderation status name.
     */
    public function getModerationStatusName(): string
    {
        $status = $this->getAttribute('moderation_status') ?? 'draft';
        return $this->moderationStatuses[$status] ?? 'Unknown';
    }

    /**
     * Check if the page is scholar verified.
     */
    public function isScholarVerified(): bool
    {
        return $this->getAttribute('scholar_verified') ?? false;
    }

    /**
     * Check if the page is approved.
     */
    public function isApproved(): bool
    {
        return $this->getAttribute('moderation_status') === 'approved';
    }

    /**
     * Check if the page needs moderation.
     */
    public function needsModeration(): bool
    {
        $status = $this->getAttribute('moderation_status');
        return in_array($status, ['draft', 'pending', 'needs_revision']);
    }

    /**
     * Get Islamic tags.
     */
    public function getIslamicTags(): array
    {
        $tags = $this->getAttribute('islamic_tags');
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($tags) ? $tags : [];
    }

    /**
     * Add Islamic tag.
     */
    public function addIslamicTag(string $tag): self
    {
        $tags = $this->getIslamicTags();
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->setAttribute('islamic_tags', json_encode($tags));
        }
        return $this;
    }

    /**
     * Remove Islamic tag.
     */
    public function removeIslamicTag(string $tag): self
    {
        $tags = $this->getIslamicTags();
        $tags = array_filter($tags, fn($t) => $t !== $tag);
        $this->setAttribute('islamic_tags', json_encode(array_values($tags)));
        return $this;
    }

    /**
     * Get Islamic references.
     */
    public function getIslamicReferences(): array
    {
        $references = $this->getAttribute('islamic_references');
        if (is_string($references)) {
            $decoded = json_decode($references, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($references) ? $references : [];
    }

    /**
     * Add Islamic reference.
     */
    public function addIslamicReference(array $reference): self
    {
        $references = $this->getIslamicReferences();
        $references[] = $reference;
        $this->setAttribute('islamic_references', json_encode($references));
        return $this;
    }

    /**
     * Set Islamic references.
     */
    public function setIslamicReferences(array $references): self
    {
        $this->setAttribute('islamic_references', json_encode($references));
        return $this;
    }

    /**
     * Override setAttribute to handle JSON fields properly.
     */
    public function setAttribute(string $key, mixed $value): self
    {
        // Handle JSON fields
        if (in_array($key, ['islamic_references', 'islamic_citations', 'islamic_metadata', 'islamic_permissions', 'islamic_tags'])) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Override getDirty to handle JSON fields properly.
     */
    public function getDirty(): array
    {
        $dirty = parent::getDirty();

        // Ensure JSON fields are serialized
        foreach (['islamic_references', 'islamic_citations', 'islamic_metadata', 'islamic_permissions', 'islamic_tags'] as $jsonField) {
            if (isset($dirty[$jsonField]) && is_array($dirty[$jsonField])) {
                $dirty[$jsonField] = json_encode($dirty[$jsonField]);
            }
        }

        return $dirty;
    }

    /**
     * Get Islamic citations.
     */
    public function getIslamicCitations(): array
    {
        $citations = $this->getAttribute('islamic_citations');
        if (is_string($citations)) {
            $decoded = json_decode($citations, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($citations) ? $citations : [];
    }

    /**
     * Add Islamic citation.
     */
    public function addIslamicCitation(array $citation): self
    {
        $citations = $this->getIslamicCitations();
        $citations[] = $citation;
        $this->setAttribute('islamic_citations', json_encode($citations));
        return $this;
    }

    /**
     * Set Islamic citations.
     */
    public function setIslamicCitations(array $citations): self
    {
        $this->setAttribute('islamic_citations', json_encode($citations));
        return $this;
    }

    /**
     * Get Islamic metadata.
     */
    public function getIslamicMetadata(): array
    {
        $metadata = $this->getAttribute('islamic_metadata');
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($metadata) ? $metadata : [];
    }

    /**
     * Set Islamic metadata.
     */
    public function setIslamicMetadata(array $metadata): self
    {
        $this->setAttribute('islamic_metadata', json_encode($metadata));
        return $this;
    }

    /**
     * Get Islamic permissions.
     */
    public function getIslamicPermissions(): array
    {
        $permissions = $this->getAttribute('islamic_permissions');
        if (is_string($permissions)) {
            $decoded = json_decode($permissions, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($permissions) ? $permissions : [];
    }

    /**
     * Set Islamic permissions.
     */
    public function setIslamicPermissions(array $permissions): self
    {
        $this->setAttribute('islamic_permissions', json_encode($permissions));
        return $this;
    }

    /**
     * Get the Arabic title.
     */
    public function getArabicTitle(): ?string
    {
        return $this->getAttribute('arabic_title');
    }

    /**
     * Get the Arabic content.
     */
    public function getArabicContent(): ?string
    {
        return $this->getAttribute('arabic_content');
    }

    /**
     * Get content quality score.
     */
    public function getContentQualityScore(): int
    {
        return $this->getAttribute('content_quality_score') ?? 0;
    }

    /**
     * Set content quality score.
     */
    public function setContentQualityScore(int $score): self
    {
        $this->setAttribute('content_quality_score', max(0, min(100, $score)));
        return $this;
    }

    /**
     * Get the Islamic profile data.
     */
    public function getIslamicProfile(): array
    {
        return [
            'id' => $this->getAttribute('id'),
            'title' => $this->getAttribute('title'),
            'arabic_title' => $this->getArabicTitle(),
            'slug' => $this->getAttribute('slug'),
            'namespace' => $this->getAttribute('namespace'),
            'islamic_category' => $this->getAttribute('islamic_category'),
            'islamic_category_name' => $this->getIslamicCategoryName(),
            'islamic_template' => $this->getAttribute('islamic_template'),
            'islamic_template_name' => $this->getIslamicTemplateName(),
            'islamic_tags' => $this->getIslamicTags(),
            'scholar_verified' => $this->isScholarVerified(),
            'verified_by' => $this->getAttribute('verified_by'),
            'verified_at' => $this->getAttribute('verified_at'),
            'moderation_status' => $this->getAttribute('moderation_status'),
            'moderation_status_name' => $this->getModerationStatusName(),
            'moderated_by' => $this->getAttribute('moderated_by'),
            'moderated_at' => $this->getAttribute('moderated_at'),
            'content_quality_score' => $this->getContentQualityScore(),
            'islamic_references' => $this->getIslamicReferences(),
            'islamic_citations' => $this->getIslamicCitations(),
            'islamic_metadata' => $this->getIslamicMetadata(),
            'islamic_permissions' => $this->getIslamicPermissions(),
            'is_approved' => $this->isApproved(),
            'needs_moderation' => $this->needsModeration(),
        ];
    }

    /**
     * Approve the page.
     */
    public function approve(int $approvedBy, string $notes = ''): bool
    {
        $this->setAttribute('moderation_status', 'approved');
        $this->setAttribute('moderated_by', $approvedBy);
        $this->setAttribute('moderated_at', (new DateTime())->format('Y-m-d H:i:s'));
        $this->setAttribute('moderation_notes', $notes);

        return $this->save();
    }

    /**
     * Reject the page.
     */
    public function reject(int $rejectedBy, string $reason): bool
    {
        $this->setAttribute('moderation_status', 'rejected');
        $this->setAttribute('moderated_by', $rejectedBy);
        $this->setAttribute('moderated_at', (new DateTime())->format('Y-m-d H:i:s'));
        $this->setAttribute('moderation_notes', $reason);

        return $this->save();
    }

    /**
     * Request revision.
     */
    public function requestRevision(int $requestedBy, string $notes): bool
    {
        $this->setAttribute('moderation_status', 'needs_revision');
        $this->setAttribute('moderated_by', $requestedBy);
        $this->setAttribute('moderated_at', (new DateTime())->format('Y-m-d H:i:s'));
        $this->setAttribute('moderation_notes', $notes);

        return $this->save();
    }

    /**
     * Verify by scholar.
     */
    public function verifyByScholar(int $scholarId, string $notes = ''): bool
    {
        $this->setAttribute('scholar_verified', true);
        $this->setAttribute('verified_by', $scholarId);
        $this->setAttribute('verified_at', (new DateTime())->format('Y-m-d H:i:s'));
        $this->setAttribute('verification_notes', $notes);

        return $this->save();
    }

    /**
     * Get Islamic categories.
     */
    public static function getIslamicCategories(): array
    {
        return [
            'quran' => 'Quran Studies',
            'hadith' => 'Hadith Studies',
            'fiqh' => 'Islamic Jurisprudence',
            'aqeedah' => 'Islamic Creed',
            'seerah' => 'Prophetic Biography',
            'islamic_history' => 'Islamic History',
            'islamic_philosophy' => 'Islamic Philosophy',
            'islamic_ethics' => 'Islamic Ethics',
            'islamic_science' => 'Islamic Science',
            'islamic_art' => 'Islamic Art & Architecture',
            'islamic_literature' => 'Islamic Literature',
            'islamic_education' => 'Islamic Education',
            'islamic_society' => 'Islamic Society & Culture',
            'islamic_economics' => 'Islamic Economics',
            'islamic_medicine' => 'Islamic Medicine',
            'other' => 'Other Islamic Topics',
        ];
    }

    /**
     * Get Islamic templates.
     */
    public static function getIslamicTemplates(): array
    {
        return [
            'quran_ayah' => 'Quran Ayah Analysis',
            'hadith_study' => 'Hadith Study',
            'fiqh_ruling' => 'Fiqh Ruling',
            'scholar_biography' => 'Scholar Biography',
            'islamic_concept' => 'Islamic Concept',
            'historical_event' => 'Historical Event',
            'islamic_practice' => 'Islamic Practice',
            'comparative_study' => 'Comparative Study',
            'research_paper' => 'Research Paper',
            'general_article' => 'General Article',
        ];
    }

    /**
     * Get moderation statuses.
     */
    public static function getModerationStatuses(): array
    {
        return [
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'needs_revision' => 'Needs Revision',
            'under_review' => 'Under Review',
        ];
    }
}
