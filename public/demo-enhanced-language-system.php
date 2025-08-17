<?php
/**
 * Demo: Enhanced Path-Based Language System
 * 
 * This script demonstrates the enhanced language-aware routing system.
 * Users can now access content directly in different languages via simple paths!
 */

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>🌍 Enhanced Path-Based Language System Demo</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }";
echo "h2 { color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 10px; }";
echo "h3 { color: #2980b9; }";
echo ".language-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }";
echo ".language-card { background: #ecf0f1; padding: 20px; border-radius: 8px; border-left: 4px solid #3498db; }";
echo ".language-card h4 { margin-top: 0; color: #2c3e50; }";
echo ".demo-links { margin-top: 15px; }";
echo ".demo-links a { display: inline-block; margin: 5px; padding: 8px 16px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; }";
echo ".demo-links a:hover { background: #2980b9; }";
echo ".feature-list { background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; }";
echo ".feature-list ul { margin: 10px 0; }";
echo ".feature-list li { margin: 5px 0; }";
echo ".status { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #c3e6cb; }";
echo ".warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #ffeaa7; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🌍 Enhanced Path-Based Language System Demo</h1>";

echo "<div class='status'>";
echo "<strong>✅ Status: FULLY FUNCTIONAL</strong><br>";
echo "The enhanced path-based language system is now working with direct content rendering!";
echo "</div>";

echo "<h2>🚀 What's New</h2>";
echo "<div class='feature-list'>";
echo "<p><strong>Enhanced Features:</strong></p>";
echo "<ul>";
echo "<li><strong>Direct Content Rendering:</strong> No more redirects - content is rendered directly in the target language</li>";
echo "<li><strong>Smart Translation Integration:</strong> Automatically uses translation services when available</li>";
echo "<li><strong>Language-Aware Templates:</strong> RTL support and proper text direction for all languages</li>";
echo "<li><strong>Comprehensive Content Coverage:</strong> All major content types now have language-specific rendering</li>";
echo "<li><strong>Fallback System:</strong> Gracefully falls back to redirects if translation services are unavailable</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🌐 Available Languages</h2>";
echo "<div class='language-grid'>";

// English
echo "<div class='language-card'>";
echo "<h4>🇺🇸 English (Default)</h4>";
echo "<p><strong>URL:</strong> <code>http://localhost:8000/</code></p>";
echo "<p><strong>Direction:</strong> LTR</p>";
echo "<div class='demo-links'>";
echo "<a href='/' target='_blank'>Home</a>";
echo "<a href='/quran' target='_blank'>Quran</a>";
echo "<a href='/hadith' target='_blank'>Hadith</a>";
echo "</div>";
echo "</div>";

// Arabic
echo "<div class='language-card'>";
echo "<h4>🇸🇦 Arabic (العربية)</h4>";
echo "<p><strong>URL:</strong> <code>http://localhost:8000/ar/</code></p>";
echo "<p><strong>Direction:</strong> RTL</p>";
echo "<div class='demo-links'>";
echo "<a href='/ar/' target='_blank'>Home</a>";
echo "<a href='/ar/quran' target='_blank'>Quran</a>";
echo "<a href='/ar/hadith' target='_blank'>Hadith</a>";
echo "</div>";
echo "</div>";

// Urdu
echo "<div class='language-card'>";
echo "<h4>🇵🇰 Urdu (اردو)</h4>";
echo "<p><strong>URL:</strong> <code>http://localhost:8000/ur/</code></p>";
echo "<p><strong>Direction:</strong> RTL</p>";
echo "<div class='demo-links'>";
echo "<a href='/ur/' target='_blank'>Home</a>";
echo "<a href='/ur/quran' target='_blank'>Quran</a>";
echo "<a href='/ur/hadith' target='_blank'>Hadith</a>";
echo "</div>";
echo "</div>";

// Turkish
echo "<div class='language-card'>";
echo "<h4>🇹🇷 Turkish (Türkçe)</h4>";
echo "<p><strong>URL:</strong> <code>http://localhost:8000/tr/</code></p>";
echo "<p><strong>Direction:</strong> LTR</p>";
echo "<div class='demo-links'>";
echo "<a href='/tr/' target='_blank'>Home</a>";
echo "<a href='/tr/quran' target='_blank'>Quran</a>";
echo "<a href='/tr/hadith' target='_blank'>Hadith</a>";
echo "</div>";
echo "</div>";

// Indonesian
echo "<div class='language-card'>";
echo "<h4>🇮🇩 Indonesian (Bahasa Indonesia)</h4>";
echo "<p><strong>URL:</strong> <code>http://localhost:8000/id/</code></p>";
echo "<p><strong>Direction:</strong> LTR</p>";
echo "<div class='demo-links'>";
echo "<a href='/id/' target='_blank'>Home</a>";
echo "<a href='/id/quran' target='_blank'>Quran</a>";
echo "<a href='/id/hadith' target='_blank'>Hadith</a>";
echo "</div>";
echo "</div>";

// Malay
echo "<div class='language-card'>";
echo "<h4>🇲🇾 Malay (Bahasa Melayu)</h4>";
echo "<p><strong>URL:</strong> <code>http://localhost:8000/ms/</code></p>";
echo "<p><strong>Direction:</strong> LTR</p>";
echo "<div class='demo-links'>";
echo "<a href='/ms/' target='_blank'>Home</a>";
echo "<a href='/ms/quran' target='_blank'>Quran</a>";
echo "<a href='/ms/hadith' target='_blank'>Hadith</a>";
echo "</div>";
echo "</div>";

// Persian
echo "<div class='language-card'>";
echo "<h4>🇮🇷 Persian (فارسی)</h4>";
echo "<p><strong>URL:</strong> <code>http://localhost:8000/fa/</code></p>";
echo "<p><strong>Direction:</strong> RTL</p>";
echo "<div class='demo-links'>";
echo "<a href='/fa/' target='_blank'>Home</a>";
echo "<a href='/fa/quran' target='_blank'>Quran</a>";
echo "<a href='/fa/hadith' target='_blank'>Hadith</a>";
echo "</div>";
echo "</div>";

// Hebrew
echo "<div class='language-card'>";
echo "<h4>🇮🇱 Hebrew (עברית)</h4>";
echo "<p><strong>URL:</strong> <code>http://localhost:8000/he/</code></p>";
echo "<p><strong>Direction:</strong> RTL</p>";
echo "<div class='demo-links'>";
echo "<a href='/he/' target='_blank'>Home</a>";
echo "<a href='/he/quran' target='_blank'>Quran</a>";
echo "<a href='/he/hadith' target='_blank'>Hadith</a>";
echo "</div>";
echo "</div>";

echo "</div>";

echo "<h2>🔧 Content Types Supported</h2>";
echo "<div class='feature-list'>";
echo "<p><strong>All major content types now support direct language rendering:</strong></p>";
echo "<ul>";
echo "<li><strong>Quran:</strong> <code>/{language}/quran</code> - Browse, search, and read Quran content</li>";
echo "<li><strong>Hadith:</strong> <code>/{language}/hadith</code> - Access hadith collections and teachings</li>";
echo "<li><strong>Wiki:</strong> <code>/{language}/wiki</code> - Islamic articles and knowledge base</li>";
echo "<li><strong>Sciences:</strong> <code>/{language}/sciences</strong> - Islamic jurisprudence, beliefs, and more</li>";
echo "<li><strong>Community:</strong> <code>/{language}/community</code> - Connect with other Muslims</li>";
echo "<li><strong>Documentation:</strong> <code>/{language}/docs</code> - User guides and API references</li>";
echo "<li><strong>Bayan System:</strong> <code>/{language}/bayan</code> - Islamic concept relationships</li>";
echo "</ul>";
echo "</div>";

echo "<h2>💡 How It Works</h2>";
echo "<div class='feature-list'>";
echo "<p><strong>Technical Implementation:</strong></p>";
echo "<ol>";
echo "<li><strong>Path Detection:</strong> System detects language from URL path (e.g., /ar/quran)</li>";
echo "<li><strong>Translation Service:</strong> Automatically integrates with available translation services</li>";
echo "<li><strong>Content Generation:</strong> Creates language-specific HTML with proper RTL/LTR support</li>";
echo "<li><strong>Fallback:</strong> If translation fails, gracefully falls back to redirect system</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🎯 Benefits</h2>";
echo "<div class='feature-list'>";
echo "<ul>";
echo "<li><strong>✅ No DNS Configuration:</strong> Works out of the box with standard hosting</li>";
echo "<li><strong>✅ Better SEO:</strong> Language-specific URLs improve search engine optimization</li>";
echo "<strong>✅ User-Friendly:</strong> Simple, intuitive URLs for all languages</strong></li>";
echo "<li><strong>✅ RTL Support:</strong> Full right-to-left layout support for Arabic, Urdu, Persian, and Hebrew</li>";
echo "<li><strong>✅ Performance:</strong> Direct content rendering reduces redirects and improves speed</li>";
echo "<li><strong>✅ Accessibility:</strong> Proper language attributes and text direction for screen readers</li>";
echo "</ul>";
echo "</div>";

echo "<div class='warning'>";
echo "<strong>⚠️ Note:</strong> Translation services require proper API configuration. ";
echo "If translation services are unavailable, the system will fall back to the redirect-based approach.";
echo "</div>";

echo "<h2>🚀 Try It Now!</h2>";
echo "<p>Click on any of the language links above to experience the enhanced multilingual system in action!</p>";

echo "<div class='demo-links' style='text-align: center; margin-top: 30px;'>";
echo "<a href='/' style='background: #27ae60; font-size: 18px; padding: 15px 30px;'>🏠 Go to English Home</a>";
echo "<a href='/ar/' style='background: #e74c3c; font-size: 18px; padding: 15px 30px;'>🇸🇦 Arabic Home</a>";
echo "<a href='/ur/' style='background: #f39c12; font-size: 18px; padding: 15px 30px;'>🇵🇰 Urdu Home</a>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>"; 