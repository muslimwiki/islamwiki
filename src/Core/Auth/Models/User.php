<?php

declare(strict_types=1);

namespace IslamWiki\Core\Auth\Models;

/**
 * User Model
 * 
 * Represents a user in the system with authentication and profile information.
 * 
 * @package IslamWiki\Core\Auth\Models
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class User
{
    public int $id;
    public string $username;
    public string $email;
    public string $first_name;
    public string $last_name;
    public ?string $bio;
    public string $password_hash;
    public bool $is_active;
    public ?string $avatar_url;
    public string $created_at;
    public string $updated_at;
    public ?string $last_login;
    public ?string $deleted_at;

    public function __construct(array $data)
    {
        $this->id = (int) $data['id'];
        $this->username = $data['username'];
        $this->email = $data['email'];
        $this->first_name = $data['first_name'];
        $this->last_name = $data['last_name'];
        $this->bio = $data['bio'] ?? null;
        $this->password_hash = $data['password_hash'];
        $this->is_active = (bool) $data['is_active'];
        $this->avatar_url = $data['avatar_url'] ?? null;
        $this->created_at = $data['created_at'];
        $this->updated_at = $data['updated_at'];
        $this->last_login = $data['last_login'] ?? null;
        $this->deleted_at = $data['deleted_at'] ?? null;
    }

    /**
     * Get user's full name
     */
    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get user's display name (username if no full name)
     */
    public function getDisplayName(): string
    {
        $fullName = $this->getFullName();
        return !empty($fullName) ? $fullName : $this->username;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if user is deleted
     */
    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }

    /**
     * Get user's avatar URL or default
     */
    public function getAvatarUrl(): string
    {
        return $this->avatar_url ?: '/assets/images/default-avatar.png';
    }

    /**
     * Get user's creation date as formatted string
     */
    public function getCreatedDate(): string
    {
        return date('F j, Y', strtotime($this->created_at));
    }

    /**
     * Get user's last login as formatted string
     */
    public function getLastLoginDate(): string
    {
        if (!$this->last_login) {
            return 'Never';
        }
        return date('F j, Y g:i A', strtotime($this->last_login));
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $roleName): bool
    {
        // This would be implemented with role checking logic
        return false; // Placeholder
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is moderator
     */
    public function isModerator(): bool
    {
        return $this->hasRole('moderator');
    }

    /**
     * Get user's profile completion percentage
     */
    public function getProfileCompletion(): int
    {
        $fields = [
            'first_name' => !empty($this->first_name),
            'last_name' => !empty($this->last_name),
            'email' => !empty($this->email),
            'bio' => !empty($this->bio),
            'avatar_url' => !empty($this->avatar_url)
        ];

        $completed = array_sum($fields);
        $total = count($fields);

        return (int) round(($completed / $total) * 100);
    }

    /**
     * Convert user to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'bio' => $this->bio,
            'is_active' => $this->is_active,
            'avatar_url' => $this->avatar_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'last_login' => $this->last_login,
            'full_name' => $this->getFullName(),
            'display_name' => $this->getDisplayName(),
            'avatar_url_display' => $this->getAvatarUrl(),
            'created_date' => $this->getCreatedDate(),
            'last_login_date' => $this->getLastLoginDate(),
            'profile_completion' => $this->getProfileCompletion()
        ];
    }
} 