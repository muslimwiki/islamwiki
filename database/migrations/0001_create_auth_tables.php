<?php

declare(strict_types=1);

/**
 * Create Authentication and User Management Tables
 * 
 * Migration for user authentication, roles, and skin preferences.
 * Uses Mizan database system with Islamic naming conventions.
 * 
 * @package IslamWiki\Database\Migrations
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

/**
 * Create Authentication Tables Migration
 * 
 * This migration creates all necessary tables for the Aman authentication system,
 * including users, roles, permissions, and skin preferences.
 */
class CreateAuthTables
{
    private $database;
    private $schema;

    public function __construct($database)
    {
        $this->database = $database;
        $this->schema = $database->getSchema();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Users table
        $this->schema->create('mizan_users', function ($table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->text('bio')->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['username', 'email']);
            $table->index('is_active');
        });

        // Roles table
        $this->schema->create('mizan_roles', function ($table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('display_name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            
            $table->index('name');
            $table->index('is_active');
        });

        // Permissions table
        $this->schema->create('mizan_permissions', function ($table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('display_name', 100);
            $table->text('description')->nullable();
            $table->string('category', 50)->default('general');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['name', 'category']);
            $table->index('is_active');
        });

        // Role permissions pivot table
        $this->schema->create('mizan_role_permissions', function ($table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamps();
            
            $table->foreign('role_id')->references('id')->on('mizan_roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('mizan_permissions')->onDelete('cascade');
            $table->unique(['role_id', 'permission_id']);
        });

        // User roles pivot table
        $this->schema->create('mizan_user_roles', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('mizan_users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('mizan_roles')->onDelete('cascade');
            $table->unique(['user_id', 'role_id']);
        });

        // User preferences table
        $this->schema->create('mizan_user_preferences', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('preference_key', 100);
            $table->text('preference_value')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('mizan_users')->onDelete('cascade');
            $table->unique(['user_id', 'preference_key']);
            $table->index('preference_key');
        });

        // Skin preferences table
        $this->schema->create('mizan_skin_preferences', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('skin_name', 100);
            $table->json('customization_settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('mizan_users')->onDelete('cascade');
            $table->unique(['user_id', 'skin_name']);
            $table->index('skin_name');
        });

        // Sessions table (Wisal system)
        $this->schema->create('mizan_sessions', function ($table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity');
            
            $table->foreign('user_id')->references('id')->on('mizan_users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('last_activity');
        });

        // Password reset tokens table
        $this->schema->create('mizan_password_reset_tokens', function ($table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Failed login attempts table
        $this->schema->create('mizan_failed_login_attempts', function ($table) {
            $table->id();
            $table->string('username', 50);
            $table->string('ip_address', 45);
            $table->timestamp('attempted_at');
            
            $table->index(['username', 'ip_address']);
            $table->index('attempted_at');
        });

        // Insert default roles
        $this->insertDefaultRoles();
        
        // Insert default permissions
        $this->insertDefaultPermissions();
        
        // Insert default role permissions
        $this->insertDefaultRolePermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('mizan_failed_login_attempts');
        $this->schema->dropIfExists('mizan_password_reset_tokens');
        $this->schema->dropIfExists('mizan_sessions');
        $this->schema->dropIfExists('mizan_skin_preferences');
        $this->schema->dropIfExists('mizan_user_preferences');
        $this->schema->dropIfExists('mizan_user_roles');
        $this->schema->dropIfExists('mizan_role_permissions');
        $this->schema->dropIfExists('mizan_permissions');
        $this->schema->dropIfExists('mizan_roles');
        $this->schema->dropIfExists('mizan_users');
    }

    /**
     * Insert default roles
     */
    private function insertDefaultRoles(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access and control',
                'is_system' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Moderator',
                'description' => 'Content moderation and user management',
                'is_system' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'contributor',
                'display_name' => 'Contributor',
                'description' => 'Content creation and editing',
                'is_system' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Standard user access',
                'is_system' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($roles as $role) {
            $this->database->table('mizan_roles')->insert($role);
        }
    }

    /**
     * Insert default permissions
     */
    private function insertDefaultPermissions(): void
    {
        $permissions = [
            // Admin permissions
            ['name' => 'admin.access', 'display_name' => 'Access Admin Panel', 'category' => 'admin'],
            ['name' => 'admin.users', 'display_name' => 'Manage Users', 'category' => 'admin'],
            ['name' => 'admin.roles', 'display_name' => 'Manage Roles', 'category' => 'admin'],
            ['name' => 'admin.system', 'display_name' => 'System Settings', 'category' => 'admin'],
            ['name' => 'admin.extensions', 'display_name' => 'Manage Extensions', 'category' => 'admin'],
            
            // User management permissions
            ['name' => 'user.create', 'display_name' => 'Create Users', 'category' => 'user_management'],
            ['name' => 'user.edit', 'display_name' => 'Edit Users', 'category' => 'user_management'],
            ['name' => 'user.delete', 'display_name' => 'Delete Users', 'category' => 'user_management'],
            ['name' => 'user.view', 'display_name' => 'View Users', 'category' => 'user_management'],
            
            // Content permissions
            ['name' => 'content.create', 'display_name' => 'Create Content', 'category' => 'content'],
            ['name' => 'content.edit', 'display_name' => 'Edit Content', 'category' => 'content'],
            ['name' => 'content.delete', 'display_name' => 'Delete Content', 'category' => 'content'],
            ['name' => 'content.moderate', 'display_name' => 'Moderate Content', 'category' => 'content'],
            
            // Skin permissions
            ['name' => 'skin.select', 'display_name' => 'Select Personal Skin', 'category' => 'skin'],
            ['name' => 'skin.preview', 'display_name' => 'Preview Skins', 'category' => 'skin'],
            ['name' => 'skin.manage', 'display_name' => 'Manage System Skins', 'category' => 'skin'],
            ['name' => 'skin.customize', 'display_name' => 'Customize Skins', 'category' => 'skin'],
        ];

        foreach ($permissions as $permission) {
            $permission['created_at'] = date('Y-m-d H:i:s');
            $permission['updated_at'] = date('Y-m-d H:i:s');
            $this->database->table('mizan_permissions')->insert($permission);
        }
    }

    /**
     * Insert default role permissions
     */
    private function insertDefaultRolePermissions(): void
    {
        // Get role and permission IDs
        $adminRoleId = $this->database->table('mizan_roles')->where('name', 'admin')->first()->id;
        $moderatorRoleId = $this->database->table('mizan_roles')->where('name', 'moderator')->first()->id;
        $contributorRoleId = $this->database->table('mizan_roles')->where('name', 'contributor')->first()->id;
        $userRoleId = $this->database->table('mizan_roles')->where('name', 'user')->first()->id;

        // Admin gets all permissions
        $allPermissions = $this->database->table('mizan_permissions')->get();
        foreach ($allPermissions as $permission) {
            $this->database->table('mizan_role_permissions')->insert([
                'role_id' => $adminRoleId,
                'permission_id' => $permission->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Moderator permissions
        $moderatorPermissions = ['user.view', 'content.moderate', 'skin.preview'];
        foreach ($moderatorPermissions as $permName) {
            $permission = $this->database->table('mizan_permissions')->where('name', $permName)->first();
            if ($permission) {
                $this->database->table('mizan_role_permissions')->insert([
                    'role_id' => $moderatorRoleId,
                    'permission_id' => $permission->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Contributor permissions
        $contributorPermissions = ['content.create', 'content.edit', 'skin.select', 'skin.preview'];
        foreach ($contributorPermissions as $permName) {
            $permission = $this->database->table('mizan_permissions')->where('name', $permName)->first();
            if ($permission) {
                $this->database->table('mizan_role_permissions')->insert([
                    'role_id' => $contributorRoleId,
                    'permission_id' => $permission->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // User permissions
        $userPermissions = ['skin.select', 'skin.preview'];
        foreach ($userPermissions as $permName) {
            $permission = $this->database->table('mizan_permissions')->where('name', $permName)->first();
            if ($permission) {
                $this->database->table('mizan_role_permissions')->insert([
                    'role_id' => $userRoleId,
                    'permission_id' => $permission->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
} 