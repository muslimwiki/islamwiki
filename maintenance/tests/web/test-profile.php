<?php

/**
 * Test page to verify profile page functionality
 */

// Load LocalSettings.php
require_once __DIR__ . '/../LocalSettings.php';

// Get active skin
global $wgActiveSkin;
$activeSkinName = $wgActiveSkin ?? 'Bismillah';

// Get skin CSS
$skinPath = __DIR__ . '/../skins/' . $activeSkinName;
$cssPath = $skinPath . '/css/bismillah.css';
$cssContent = file_exists($cssPath) ? file_get_contents($cssPath) : '/* CSS not found */';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Test - IslamWiki</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Safa CSS Framework -->
    <link rel="stylesheet" href="/css/safa.css">
    
    <!-- Bismillah Skin CSS -->
    <style>
        <?php echo $cssContent; ?>
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar-section">
                    <div class="profile-avatar">
                        <img src="/images/default-avatar.png" alt="Profile Picture" class="avatar-image">
                        <div class="avatar-overlay">
                            <button class="avatar-edit-btn" onclick="editAvatar()">
                                <span>📷</span>
                            </button>
                        </div>
                    </div>
                    <div class="profile-info">
                        <h1 class="profile-name">Test User</h1>
                        <p class="profile-username">@testuser</p>
                        <p class="profile-bio">This is a test user profile for demonstration purposes.</p>
                        <div class="profile-meta">
                            <span class="meta-item">
                                <span class="meta-icon">📅</span>
                                Member since 2024-01-15
                            </span>
                            <span class="meta-item">
                                <span class="meta-icon">🕒</span>
                                Last active 2 hours ago
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <a href="/settings" class="btn btn-primary">
                        <span>⚙️</span>
                        Settings
                    </a>
                    <button class="btn btn-outline" onclick="editProfile()">
                        <span>✏️</span>
                        Edit Profile
                    </button>
                </div>
            </div>

            <!-- Profile Statistics -->
            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-content">
                        <div class="stat-number">5</div>
                        <div class="stat-label">Pages Created</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✏️</div>
                    <div class="stat-content">
                        <div class="stat-number">12</div>
                        <div class="stat-label">Recent Edits</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👁️</div>
                    <div class="stat-content">
                        <div class="stat-number">8</div>
                        <div class="stat-label">Watchlist Items</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🎨</div>
                    <div class="stat-content">
                        <div class="stat-number">Bismillah</div>
                        <div class="stat-label">Active Skin</div>
                    </div>
                </div>
            </div>

            <!-- Profile Content Tabs -->
            <div class="profile-tabs">
                <div class="tab-nav">
                    <button class="tab-btn active" data-tab="overview">
                        📊 Overview
                    </button>
                    <button class="tab-btn" data-tab="activity">
                        📝 Recent Activity
                    </button>
                    <button class="tab-btn" data-tab="contributions">
                        🎯 Contributions
                    </button>
                    <button class="tab-btn" data-tab="settings">
                        ⚙️ Settings Summary
                    </button>
                </div>

                <!-- Overview Tab -->
                <div class="tab-content active" id="overview">
                    <div class="profile-section">
                        <h2 class="section-title">👤 Personal Information</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <label class="info-label">Display Name</label>
                                <div class="info-value">Test User</div>
                            </div>
                            
                            <div class="info-item">
                                <label class="info-label">Email Address</label>
                                <div class="info-value">test@example.com</div>
                            </div>
                            
                            <div class="info-item">
                                <label class="info-label">Bio</label>
                                <div class="info-value">This is a test user profile for demonstration purposes.</div>
                            </div>
                            
                            <div class="info-item">
                                <label class="info-label">Active Skin</label>
                                <div class="info-value">Bismillah</div>
                            </div>
                            
                            <div class="info-item">
                                <label class="info-label">Theme</label>
                                <div class="info-value">light</div>
                            </div>
                            
                            <div class="info-item">
                                <label class="info-label">Language</label>
                                <div class="info-value">en</div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-section">
                        <h2 class="section-title">📈 Activity Summary</h2>
                        <div class="activity-summary">
                            <div class="summary-item">
                                <div class="summary-icon">📄</div>
                                <div class="summary-content">
                                    <h3>Pages Created</h3>
                                    <p>5 pages created</p>
                                </div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon">✏️</div>
                                <div class="summary-content">
                                    <h3>Recent Activity</h3>
                                    <p>12 edits in the last 30 days</p>
                                </div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon">👁️</div>
                                <div class="summary-content">
                                    <h3>Watchlist</h3>
                                    <p>8 pages being watched</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Tab -->
                <div class="tab-content" id="activity">
                    <div class="profile-section">
                        <h2 class="section-title">📝 Recent Activity</h2>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon">✏️</div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <a href="/pages/test-page">Test Page</a>
                                    </div>
                                    <div class="activity-meta">
                                        <span class="activity-type">Edited</span>
                                        <span class="activity-time">2 hours ago</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-icon">✏️</div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <a href="/pages/another-page">Another Page</a>
                                    </div>
                                    <div class="activity-meta">
                                        <span class="activity-type">Edited</span>
                                        <span class="activity-time">1 day ago</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-icon">📄</div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <a href="/pages/new-page">New Page</a>
                                    </div>
                                    <div class="activity-meta">
                                        <span class="activity-type">Created</span>
                                        <span class="activity-time">3 days ago</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contributions Tab -->
                <div class="tab-content" id="contributions">
                    <div class="profile-section">
                        <h2 class="section-title">🎯 Your Contributions</h2>
                        <div class="contributions-grid">
                            <div class="contribution-card">
                                <div class="contribution-icon">📄</div>
                                <div class="contribution-content">
                                    <h3>Pages Created</h3>
                                    <div class="contribution-number">5</div>
                                    <p>Pages you've created and published</p>
                                </div>
                            </div>
                            
                            <div class="contribution-card">
                                <div class="contribution-icon">✏️</div>
                                <div class="contribution-content">
                                    <h3>Edits Made</h3>
                                    <div class="contribution-number">12</div>
                                    <p>Recent edits in the last 30 days</p>
                                </div>
                            </div>
                            
                            <div class="contribution-card">
                                <div class="contribution-icon">👁️</div>
                                <div class="contribution-content">
                                    <h3>Pages Watched</h3>
                                    <div class="contribution-number">8</div>
                                    <p>Pages you're following for updates</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Summary Tab -->
                <div class="tab-content" id="settings">
                    <div class="profile-section">
                        <h2 class="section-title">⚙️ Settings Summary</h2>
                        <div class="settings-summary">
                            <div class="settings-group">
                                <h3>🎨 Appearance</h3>
                                <div class="settings-list">
                                    <div class="setting-item">
                                        <span class="setting-label">Active Skin:</span>
                                        <span class="setting-value">Bismillah</span>
                                    </div>
                                    <div class="setting-item">
                                        <span class="setting-label">Theme:</span>
                                        <span class="setting-value">light</span>
                                    </div>
                                    <div class="setting-item">
                                        <span class="setting-label">Language:</span>
                                        <span class="setting-value">en</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-group">
                                <h3>🔔 Notifications</h3>
                                <div class="settings-list">
                                    <div class="setting-item">
                                        <span class="setting-label">Frequency:</span>
                                        <span class="setting-value">daily</span>
                                    </div>
                                    <div class="setting-item">
                                        <span class="setting-label">Timezone:</span>
                                        <span class="setting-value">UTC</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-group">
                                <h3>🔒 Privacy</h3>
                                <div class="settings-list">
                                    <div class="setting-item">
                                        <span class="setting-label">Profile Visibility:</span>
                                        <span class="setting-value">public</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-actions">
                                <a href="/settings" class="btn btn-primary">
                                    <span>⚙️</span>
                                    Manage Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Profile</h3>
                <button class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="form-group">
                        <label class="form-label">Display Name</label>
                        <input type="text" class="form-control" name="display_name" value="Test User">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" value="test@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Bio</label>
                        <textarea class="form-control" name="bio" rows="3">This is a test user profile for demonstration purposes.</textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab navigation
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const targetTab = this.dataset.tab;
                
                // Remove active class from all tabs
                tabBtns.forEach(t => t.classList.remove('active'));
                tabContents.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                document.getElementById(targetTab).classList.add('active');
            });
        });
    });

    // Edit profile functions
    function editProfile() {
        document.getElementById('editProfileModal').style.display = 'block';
    }

    function closeEditModal() {
        document.getElementById('editProfileModal').style.display = 'none';
    }

    function editAvatar() {
        alert('Avatar upload functionality will be implemented soon!');
    }

    // Handle form submission
    document.getElementById('editProfileForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        try {
            const response = await fetch('/profile/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Profile updated successfully!');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                alert(result.message || 'Failed to update profile');
            }
        } catch (error) {
            alert('An error occurred while updating profile');
        }
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('editProfileModal');
        if (event.target === modal) {
            closeEditModal();
        }
    });
    </script>
</body>
</html> 