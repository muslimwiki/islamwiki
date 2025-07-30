<?php
declare(strict_types=1);

namespace IslamWiki\Models;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Islamic\IslamicDatabaseManager;
use DateTime;

/**
 * Islamic User Model
 * 
 * Enhanced user model with Islamic community features including:
 * - Scholar verification system
 * - Islamic credentials and qualifications
 * - Community roles and permissions
 * - Islamic content contribution tracking
 */
class IslamicUser extends User
{
    /**
     * Islamic-specific fillable attributes.
     */
    protected array $islamicFillable = [
        'scholar_id',
        'islamic_role',
        'qualification_level',
        'madhab',
        'specialization',
        'verification_status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'islamic_bio',
        'arabic_name',
        'kunyah',
        'laqab',
        'nasab',
        'birth_place',
        'birth_year',
        'death_year',
        'era',
        'is_sahabi',
        'is_scholar',
        'is_verified_scholar',
        'islamic_credentials',
        'islamic_works',
        'islamic_contributions',
    ];

    /**
     * Islamic-specific hidden attributes.
     */
    protected array $islamicHidden = [
        'verification_notes',
        'islamic_credentials',
    ];

    /**
     * Islamic-specific casts.
     */
    protected array $islamicCasts = [
        'verified_at' => 'datetime',
        'is_sahabi' => 'boolean',
        'is_scholar' => 'boolean',
        'is_verified_scholar' => 'boolean',
        'islamic_credentials' => 'array',
        'islamic_works' => 'array',
        'islamic_contributions' => 'array',
    ];

    /**
     * Islamic roles and their permissions.
     */
    protected array $islamicRoles = [
        'user' => [
            'read_pages',
            'edit_pages',
            'create_pages',
            'comment',
        ],
        'moderator' => [
            'read_pages',
            'edit_pages',
            'create_pages',
            'comment',
            'moderate_comments',
            'review_edits',
            'flag_content',
        ],
        'scholar' => [
            'read_pages',
            'edit_pages',
            'create_pages',
            'comment',
            'moderate_comments',
            'review_edits',
            'flag_content',
            'verify_content',
            'approve_edits',
            'manage_scholars',
        ],
        'verified_scholar' => [
            'read_pages',
            'edit_pages',
            'create_pages',
            'comment',
            'moderate_comments',
            'review_edits',
            'flag_content',
            'verify_content',
            'approve_edits',
            'manage_scholars',
            'verify_scholars',
            'approve_fatwas',
            'manage_islamic_content',
        ],
        'admin' => [
            'read_pages',
            'edit_pages',
            'create_pages',
            'comment',
            'moderate_comments',
            'review_edits',
            'flag_content',
            'verify_content',
            'approve_edits',
            'manage_scholars',
            'verify_scholars',
            'approve_fatwas',
            'manage_islamic_content',
            'manage_users',
            'manage_system',
        ],
    ];

    /**
     * Create a new Islamic user instance.
     */
    public function __construct(Connection $connection, array $attributes = [])
    {
        parent::__construct($connection, $attributes);
        
        // Merge Islamic fillable attributes
        $this->fillable = array_merge($this->fillable, $this->islamicFillable);
        $this->hidden = array_merge($this->hidden, $this->islamicHidden);
        $this->casts = array_merge($this->casts, $this->islamicCasts);
    }

    /**
     * Get the user's Islamic role.
     */
    public function getIslamicRole(): string
    {
        return $this->getAttribute('islamic_role') ?? 'user';
    }

    /**
     * Set the user's Islamic role.
     */
    public function setIslamicRole(string $role): self
    {
        if (!array_key_exists($role, $this->islamicRoles)) {
            throw new \InvalidArgumentException("Invalid Islamic role: {$role}");
        }

        $this->setAttribute('islamic_role', $role);
        return $this;
    }

    /**
     * Check if user has a specific Islamic permission.
     */
    public function hasIslamicPermission(string $permission): bool
    {
        $role = $this->getIslamicRole();
        $permissions = $this->islamicRoles[$role] ?? [];
        
        return in_array($permission, $permissions);
    }

    /**
     * Get all permissions for the user's Islamic role.
     */
    public function getIslamicPermissions(): array
    {
        $role = $this->getIslamicRole();
        return $this->islamicRoles[$role] ?? [];
    }

    /**
     * Check if user is a scholar.
     */
    public function isScholar(): bool
    {
        return $this->getAttribute('is_scholar') ?? false;
    }

    /**
     * Check if user is a verified scholar.
     */
    public function isVerifiedScholar(): bool
    {
        return $this->getAttribute('is_verified_scholar') ?? false;
    }

    /**
     * Check if user is a companion of the Prophet (Sahabi).
     */
    public function isSahabi(): bool
    {
        return $this->getAttribute('is_sahabi') ?? false;
    }

    /**
     * Get the user's Islamic qualification level.
     */
    public function getQualificationLevel(): string
    {
        return $this->getAttribute('qualification_level') ?? 'none';
    }

    /**
     * Get the user's Madhab (school of thought).
     */
    public function getMadhab(): ?string
    {
        return $this->getAttribute('madhab');
    }

    /**
     * Get the user's Islamic specialization.
     */
    public function getSpecialization(): ?string
    {
        return $this->getAttribute('specialization');
    }

    /**
     * Get the user's Arabic name.
     */
    public function getArabicName(): ?string
    {
        return $this->getAttribute('arabic_name');
    }

    /**
     * Get the user's Kunyah.
     */
    public function getKunyah(): ?string
    {
        return $this->getAttribute('kunyah');
    }

    /**
     * Get the user's Laqab (nickname).
     */
    public function getLaqab(): ?string
    {
        return $this->getAttribute('laqab');
    }

    /**
     * Get the user's Nasab (lineage).
     */
    public function getNasab(): ?string
    {
        return $this->getAttribute('nasab');
    }

    /**
     * Get the user's verification status.
     */
    public function getVerificationStatus(): string
    {
        return $this->getAttribute('verification_status') ?? 'pending';
    }

    /**
     * Check if user is verified.
     */
    public function isVerified(): bool
    {
        return $this->getVerificationStatus() === 'verified';
    }

    /**
     * Get the user's Islamic bio.
     */
    public function getIslamicBio(): ?string
    {
        return $this->getAttribute('islamic_bio');
    }

    /**
     * Get the user's Islamic credentials.
     */
    public function getIslamicCredentials(): array
    {
        $credentials = $this->getAttribute('islamic_credentials');
        if (is_string($credentials)) {
            $decoded = json_decode($credentials, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($credentials) ? $credentials : [];
    }

    /**
     * Add an Islamic credential.
     */
    public function addIslamicCredential(array $credential): self
    {
        $credentials = $this->getIslamicCredentials();
        $credentials[] = $credential;
        $this->setAttribute('islamic_credentials', json_encode($credentials));
        return $this;
    }

    /**
     * Get the user's Islamic works.
     */
    public function getIslamicWorks(): array
    {
        $works = $this->getAttribute('islamic_works');
        if (is_string($works)) {
            $decoded = json_decode($works, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($works) ? $works : [];
    }

    /**
     * Add an Islamic work.
     */
    public function addIslamicWork(array $work): self
    {
        $works = $this->getIslamicWorks();
        $works[] = $work;
        $this->setAttribute('islamic_works', json_encode($works));
        return $this;
    }

    /**
     * Get the user's Islamic contributions.
     */
    public function getIslamicContributions(): array
    {
        $contributions = $this->getAttribute('islamic_contributions');
        if (is_string($contributions)) {
            $decoded = json_decode($contributions, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($contributions) ? $contributions : [];
    }

    /**
     * Add an Islamic contribution.
     */
    public function addIslamicContribution(array $contribution): self
    {
        $contributions = $this->getIslamicContributions();
        $contributions[] = $contribution;
        $this->setAttribute('islamic_contributions', json_encode($contributions));
        return $this;
    }

    /**
     * Get the user's full Islamic name.
     */
    public function getFullIslamicName(): string
    {
        $parts = [];
        
        if ($kunyah = $this->getKunyah()) {
            $parts[] = $kunyah;
        }
        
        if ($arabicName = $this->getArabicName()) {
            $parts[] = $arabicName;
        } elseif ($displayName = $this->getDisplayName()) {
            $parts[] = $displayName;
        }
        
        if ($laqab = $this->getLaqab()) {
            $parts[] = $laqab;
        }
        
        if ($nasab = $this->getNasab()) {
            $parts[] = $nasab;
        }
        
        return implode(' ', $parts) ?: $this->getDisplayName();
    }

    /**
     * Get the user's Islamic profile data.
     */
    public function getIslamicProfile(): array
    {
        return [
            'id' => $this->getAttribute('id'),
            'username' => $this->getAttribute('username'),
            'display_name' => $this->getDisplayName(),
            'arabic_name' => $this->getArabicName(),
            'full_islamic_name' => $this->getFullIslamicName(),
            'islamic_role' => $this->getIslamicRole(),
            'is_scholar' => $this->isScholar(),
            'is_verified_scholar' => $this->isVerifiedScholar(),
            'is_sahabi' => $this->isSahabi(),
            'qualification_level' => $this->getQualificationLevel(),
            'madhab' => $this->getMadhab(),
            'specialization' => $this->getSpecialization(),
            'verification_status' => $this->getVerificationStatus(),
            'is_verified' => $this->isVerified(),
            'islamic_bio' => $this->getIslamicBio(),
            'islamic_credentials' => $this->getIslamicCredentials(),
            'islamic_works' => $this->getIslamicWorks(),
            'islamic_contributions' => $this->getIslamicContributions(),
            'permissions' => $this->getIslamicPermissions(),
        ];
    }

    /**
     * Request scholar verification.
     */
    public function requestScholarVerification(array $verificationData): bool
    {
        try {
            // Get scholar database connection
            $islamicDb = $this->container->get(IslamicDatabaseManager::class);
            $scholarConnection = $islamicDb->getScholarConnection();
            
            // Create verification request
            $requestData = [
                'scholar_id' => $this->getAttribute('scholar_id'),
                'requested_by' => $this->getAttribute('id'),
                'request_type' => 'scholar_verification',
                'request_details' => json_encode($verificationData),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $scholarConnection->insert(
                'INSERT INTO scholar_verification_requests (scholar_id, requested_by, request_type, request_details, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $requestData['scholar_id'],
                    $requestData['requested_by'],
                    $requestData['request_type'],
                    $requestData['request_details'],
                    $requestData['status'],
                    $requestData['created_at'],
                    $requestData['updated_at'],
                ]
            );
            
            // Update user verification status
            $this->setAttribute('verification_status', 'pending');
            $this->save();
            
            return true;
        } catch (\Exception $e) {
            error_log("Scholar verification request failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve scholar verification.
     */
    public function approveScholarVerification(int $approvedBy): bool
    {
        try {
            // Get scholar database connection
            $islamicDb = $this->container->get(IslamicDatabaseManager::class);
            $scholarConnection = $islamicDb->getScholarConnection();
            
            // Update scholar verification status
            $scholarConnection->update(
                'UPDATE scholars SET verification_status = ?, verified_by = ?, verified_at = ? WHERE id = ?',
                ['verified', $approvedBy, now(), $this->getAttribute('scholar_id')]
            );
            
            // Update user verification status
            $this->setAttribute('verification_status', 'verified');
            $this->setAttribute('verified_by', $approvedBy);
            $this->setAttribute('verified_at', now());
            $this->setAttribute('is_verified_scholar', true);
            $this->save();
            
            return true;
        } catch (\Exception $e) {
            error_log("Scholar verification approval failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject scholar verification.
     */
    public function rejectScholarVerification(int $rejectedBy, string $reason): bool
    {
        try {
            // Get scholar database connection
            $islamicDb = $this->container->get(IslamicDatabaseManager::class);
            $scholarConnection = $islamicDb->getScholarConnection();
            
            // Update scholar verification status
            $scholarConnection->update(
                'UPDATE scholars SET verification_status = ?, verified_by = ?, verified_at = ?, verification_notes = ? WHERE id = ?',
                ['rejected', $rejectedBy, now(), $reason, $this->getAttribute('scholar_id')]
            );
            
            // Update user verification status
            $this->setAttribute('verification_status', 'rejected');
            $this->setAttribute('verified_by', $rejectedBy);
            $this->setAttribute('verified_at', now());
            $this->setAttribute('verification_notes', $reason);
            $this->save();
            
            return true;
        } catch (\Exception $e) {
            error_log("Scholar verification rejection failed: " . $e->getMessage());
            return false;
        }
    }
} 