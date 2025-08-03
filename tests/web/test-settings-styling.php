<?php
/**
 * Test page to verify settings styling with Bismillah skin
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
    <title>Settings Styling Test - IslamWiki</title>
    
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
    <div class="settings-container">
        <div class="container">
            <!-- Settings Header -->
            <div class="settings-header">
                <h1 class="settings-title">⚙️ Settings Test</h1>
                <p class="settings-subtitle">Testing Bismillah skin styling for settings page</p>
            </div>

            <!-- Settings Navigation -->
            <div class="settings-nav">
                <button class="nav-tab active" data-tab="appearance">
                    🎨 Appearance
                </button>
                <button class="nav-tab" data-tab="account">
                    👤 Account
                </button>
                <button class="nav-tab" data-tab="privacy">
                    🔒 Privacy
                </button>
                <button class="nav-tab" data-tab="notifications">
                    🔔 Notifications
                </button>
            </div>

            <!-- Settings Content -->
            <div class="settings-content">
                <!-- Appearance Tab -->
                <div class="settings-tab active" id="appearance">
                    <div class="settings-section">
                        <h2 class="section-title">🎨 Skin Selection</h2>
                        <p class="section-description">Choose the visual theme for your IslamWiki experience.</p>
                        
                        <div class="skin-grid">
                            <div class="skin-card active" data-skin="Bismillah">
                                <div class="skin-info">
                                    <h3 class="skin-name">Bismillah</h3>
                                    <p class="skin-description">The default skin for IslamWiki with modern Islamic design and beautiful gradients.</p>
                                    <div class="skin-meta">
                                        <span class="skin-version">v0.0.28</span>
                                        <span class="skin-author">by IslamWiki Team</span>
                                    </div>
                                    
                                    <div class="skin-actions">
                                        <button class="skin-select-btn" disabled>
                                            ✓ Active
                                        </button>
                                        
                                        <button class="skin-info-btn" data-skin="Bismillah">
                                            Info
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h2 class="section-title">🎯 Theme Options</h2>
                        <p class="section-description">Customize your experience with additional theme settings.</p>
                        
                        <div class="theme-options">
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="enable_animations" checked>
                                    <span class="option-text">Enable Animations</span>
                                </label>
                                <p class="option-description">Smooth transitions and hover effects</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="enable_gradients" checked>
                                    <span class="option-text">Enable Gradients</span>
                                </label>
                                <p class="option-description">Beautiful gradient backgrounds</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="enable_dark_theme">
                                    <span class="option-text">Dark Theme</span>
                                </label>
                                <p class="option-description">Switch to dark mode (if supported)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Tab -->
                <div class="settings-tab" id="account">
                    <div class="settings-section">
                        <h2 class="section-title">👤 Account Information</h2>
                        <p class="section-description">Manage your account details and preferences.</p>
                        
                        <div class="account-settings">
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="user123" readonly>
                                <small class="form-text">Username cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="user@example.com">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Display Name</label>
                                <input type="text" class="form-control" value="User">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Bio</label>
                                <textarea class="form-control" rows="3" placeholder="Tell us about yourself..."></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Profile Picture</label>
                                <div class="profile-picture-upload">
                                    <div class="current-avatar">
                                        <img src="/images/default-avatar.png" alt="Profile Picture" class="avatar-preview">
                                    </div>
                                    <button class="btn btn-outline">Upload New Picture</button>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button class="btn btn-primary">Save Changes</button>
                                <button class="btn btn-secondary">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h2 class="section-title">🔐 Security Settings</h2>
                        <p class="section-description">Manage your account security and authentication.</p>
                        
                        <div class="security-settings">
                            <div class="form-group">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" placeholder="Enter current password">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" placeholder="Enter new password">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" placeholder="Confirm new password">
                            </div>
                            
                            <div class="form-actions">
                                <button class="btn btn-primary">Change Password</button>
                            </div>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h2 class="section-title">🗑️ Account Actions</h2>
                        <p class="section-description">Dangerous actions that affect your account.</p>
                        
                        <div class="account-actions">
                            <div class="action-item">
                                <h4>Delete Account</h4>
                                <p>Permanently delete your account and all associated data. This action cannot be undone.</p>
                                <button class="btn btn-danger">Delete Account</button>
                            </div>
                            
                            <div class="action-item">
                                <h4>Export Data</h4>
                                <p>Download a copy of your personal data and contributions.</p>
                                <button class="btn btn-outline">Export My Data</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Privacy Tab -->
                <div class="settings-tab" id="privacy">
                    <div class="settings-section">
                        <h2 class="section-title">🔒 Privacy Settings</h2>
                        <p class="section-description">Control your privacy and data preferences.</p>
                        
                        <div class="privacy-settings">
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="profile_public" checked>
                                    <span class="option-text">Public Profile</span>
                                </label>
                                <p class="option-description">Allow other users to view your profile and contributions</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="show_email" checked>
                                    <span class="option-text">Show Email Address</span>
                                </label>
                                <p class="option-description">Display your email address on your public profile</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="show_activity">
                                    <span class="option-text">Show Activity History</span>
                                </label>
                                <p class="option-description">Display your recent activity and contributions</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="allow_messages">
                                    <span class="option-text">Allow Private Messages</span>
                                </label>
                                <p class="option-description">Allow other users to send you private messages</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="track_analytics">
                                    <span class="option-text">Analytics Tracking</span>
                                </label>
                                <p class="option-description">Help improve IslamWiki by allowing anonymous usage analytics</p>
                            </div>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h2 class="section-title">📊 Data & Cookies</h2>
                        <p class="section-description">Manage your data and cookie preferences.</p>
                        
                        <div class="data-settings">
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="essential_cookies" checked disabled>
                                    <span class="option-text">Essential Cookies</span>
                                </label>
                                <p class="option-description">Required for basic site functionality (always enabled)</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="preference_cookies" checked>
                                    <span class="option-text">Preference Cookies</span>
                                </label>
                                <p class="option-description">Remember your settings and preferences</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="analytics_cookies">
                                    <span class="option-text">Analytics Cookies</span>
                                </label>
                                <p class="option-description">Help us understand how you use the site</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="marketing_cookies">
                                    <span class="option-text">Marketing Cookies</span>
                                </label>
                                <p class="option-description">Used for personalized content and advertisements</p>
                            </div>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h2 class="section-title">🗑️ Data Management</h2>
                        <p class="section-description">Control your personal data and privacy.</p>
                        
                        <div class="data-management">
                            <div class="action-item">
                                <h4>Download My Data</h4>
                                <p>Get a copy of all your personal data stored on IslamWiki.</p>
                                <button class="btn btn-outline">Request Data Download</button>
                            </div>
                            
                            <div class="action-item">
                                <h4>Delete My Data</h4>
                                <p>Permanently delete all your personal data from IslamWiki.</p>
                                <button class="btn btn-danger">Delete All Data</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div class="settings-tab" id="notifications">
                    <div class="settings-section">
                        <h2 class="section-title">🔔 Notification Preferences</h2>
                        <p class="section-description">Manage your notification settings.</p>
                        
                        <div class="notification-settings">
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="email_notifications" checked>
                                    <span class="option-text">Email Notifications</span>
                                </label>
                                <p class="option-description">Receive notifications via email</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="browser_notifications" checked>
                                    <span class="option-text">Browser Notifications</span>
                                </label>
                                <p class="option-description">Show notifications in your browser</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="mobile_notifications">
                                    <span class="option-text">Mobile Push Notifications</span>
                                </label>
                                <p class="option-description">Receive push notifications on mobile devices</p>
                            </div>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h2 class="section-title">📧 Email Notifications</h2>
                        <p class="section-description">Choose which email notifications you want to receive.</p>
                        
                        <div class="email-notifications">
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="welcome_emails" checked>
                                    <span class="option-text">Welcome & Onboarding</span>
                                </label>
                                <p class="option-description">Get started with helpful tips and guides</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="page_updates" checked>
                                    <span class="option-text">Page Updates</span>
                                </label>
                                <p class="option-description">When pages you follow are updated</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="comment_replies" checked>
                                    <span class="option-text">Comment Replies</span>
                                </label>
                                <p class="option-description">When someone replies to your comments</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="mention_notifications" checked>
                                    <span class="option-text">Mentions</span>
                                </label>
                                <p class="option-description">When someone mentions you in a comment or page</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="security_alerts" checked>
                                    <span class="option-text">Security Alerts</span>
                                </label>
                                <p class="option-description">Important security-related notifications</p>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="checkbox" class="option-checkbox" id="newsletter">
                                    <span class="option-text">Newsletter</span>
                                </label>
                                <p class="option-description">Monthly updates about new features and content</p>
                            </div>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h2 class="section-title">⏰ Notification Schedule</h2>
                        <p class="section-description">Control when and how often you receive notifications.</p>
                        
                        <div class="notification-schedule">
                            <div class="form-group">
                                <label class="form-label">Notification Frequency</label>
                                <select class="form-control">
                                    <option value="immediate">Immediate</option>
                                    <option value="hourly">Hourly Digest</option>
                                    <option value="daily" selected>Daily Digest</option>
                                    <option value="weekly">Weekly Digest</option>
                                    <option value="never">Never</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Quiet Hours</label>
                                <div class="quiet-hours">
                                    <input type="time" class="form-control" value="22:00">
                                    <span>to</span>
                                    <input type="time" class="form-control" value="08:00">
                                </div>
                                <small class="form-text">Notifications will be delayed during these hours</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Time Zone</label>
                                <select class="form-control">
                                    <option value="UTC">UTC</option>
                                    <option value="EST">Eastern Time</option>
                                    <option value="PST">Pacific Time</option>
                                    <option value="GMT">GMT</option>
                                    <option value="CET">Central European Time</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab navigation
        const navTabs = document.querySelectorAll('.nav-tab');
        const settingsTabs = document.querySelectorAll('.settings-tab');
        
        navTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.dataset.tab;
                
                // Remove active class from all tabs
                navTabs.forEach(t => t.classList.remove('active'));
                settingsTabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                document.getElementById(targetTab).classList.add('active');
            });
        });
    });
    </script>
</body>
</html> 