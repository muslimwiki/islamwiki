<?php
/**
 * Demo: Path-Based Language Switching
 * 
 * This script demonstrates how easy it is to use the new path-based
 * language system. No DNS configuration required!
 */

echo "<h1>🌍 Path-Based Language Switching Demo</h1>\n";
echo "<p>This demonstrates the new user-friendly language system.</p>\n\n";

echo "<h2>✅ How It Works</h2>\n";
echo "<p>Instead of requiring subdomains like <code>ar.local.islam.wiki</code>,</p>\n";
echo "<p>users can now simply use paths like <code>local.islam.wiki/ar</code></p>\n\n";

echo "<h2>🌐 Available Languages</h2>\n";
echo "<ul>\n";
echo "<li><strong>English (Default):</strong> <code>http://localhost:8000/</code></li>\n";
echo "<li><strong>Arabic:</strong> <code>http://localhost:8000/ar/</code> 🇸🇦</li>\n";
echo "<li><strong>Urdu:</strong> <code>http://localhost:8000/ur/</code> 🇵🇰</li>\n";
echo "<li><strong>Turkish:</strong> <code>http://localhost:8000/tr/</code> 🇹🇷</li>\n";
echo "<li><strong>Indonesian:</strong> <code>http://localhost:8000/id/</code> 🇮🇩</li>\n";
echo "<li><strong>Malay:</strong> <code>http://localhost:8000/ms/</code> 🇲🇾</li>\n";
echo "<li><strong>Persian:</strong> <code>http://localhost:8000/fa/</code> 🇮🇷</li>\n";
echo "<li><strong>Hebrew:</strong> <code>http://localhost:8000/he/</code> 🇮🇱</li>\n";
echo "</ul>\n\n";

echo "<h2>🚀 Benefits for Users</h2>\n";
echo "<ul>\n";
echo "<li>✅ <strong>No DNS Configuration</strong> - Works out of the box</li>\n";
echo "<li>✅ <strong>Standard URLs</strong> - Follows web conventions</li>\n";
echo "<li>✅ <strong>Better SEO</strong> - Single domain authority</li>\n";
echo "<li>✅ <strong>Easier Installation</strong> - Just install and go</li>\n";
echo "<li>✅ <strong>No SSL Issues</strong> - Single certificate works for all</li>\n";
echo "</ul>\n\n";

echo "<h2>🔧 Installation</h2>\n";
echo "<ol>\n";
echo "<li>Install the extension</li>\n";
echo "<li>That's it! No additional configuration needed</li>\n";
echo "</ol>\n\n";

echo "<h2>🧪 Test the System</h2>\n";
echo "<p>Try these URLs in your browser:</p>\n";
echo "<ul>\n";
echo "<li><a href='http://localhost:8000/ar/'>Arabic Home</a></li>\n";
echo "<li><a href='http://localhost:8000/ur/'>Urdu Home</a></li>\n";
echo "<li><a href='http://localhost:8000/tr/'>Turkish Home</a></li>\n";
echo "<li><a href='http://localhost:8000/language/available'>View All Languages</a></li>\n";
echo "</ul>\n\n";

echo "<h2>📊 API Endpoints</h2>\n";
echo "<ul>\n";
echo "<li><code>GET /language/current</code> - Current language info</li>\n";
echo "<li><code>GET /language/available</code> - All supported languages</li>\n";
echo "<li><code>GET /{language}/</code> - Language-specific home page</li>\n";
echo "<li><code>POST /language/translate</code> - Translate text</li>\n";
echo "</ul>\n\n";

echo "<h2>🎯 Perfect for Extensions</h2>\n";
echo "<p>This approach makes it incredibly easy for users to install and use</p>\n";
echo "<p>the language system on their own sites. No technical knowledge required!</p>\n\n";

echo "<hr>\n";
echo "<p><em>Demo created for IslamWiki v0.0.60 - Path-Based Language Switching</em></p>\n";
?> 