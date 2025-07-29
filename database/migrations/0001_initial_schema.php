<?
declare(strict_types=1);
php\nuse IslamWiki\Core\Database\Migrations\Migration;
use IslamWiki\Core\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Users table
        $this->schema()->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('display_name', 100)->nullable();
            $table->text('bio')->nullable();
            $table->string('website')->nullable();
            $table->string('location', 100)->nullable();
            $table->string('timezone', 50)->default('UTC');
            $table->string('language', 10)->default('en');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_admin')->default(false);
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamps();
        });

        // Pages table
        $this->schema()->create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('content_format', 20)->default('markdown');
            $table->string('namespace', 50)->default('');
            $table->foreignId('parent_id')->nullable()->constrained('pages')->onDelete('set null');
            $table->boolean('is_locked')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamps();
            
            $table->index(['namespace', 'slug']);
        });

        // Page revisions
        $this->schema()->create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('content');
            $table->string('content_format', 20)->default('markdown');
            $table->text('comment')->nullable();
            $table->boolean('is_minor_edit')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['page_id', 'created_at']);
        });

        // User watchlist
        $this->schema()->create('user_watchlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'page_id']);
        });

        // Categories
        $this->schema()->create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Page categories
        $this->schema()->create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['page_id', 'category_id']);
        });

        // Media files
        $this->schema()->create('media_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->integer('size');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('filename');
        });
    }

    public function down(): void
    {
        $this->schema()->dropIfExists('page_categories');
        $this->schema()->dropIfExists('user_watchlist');
        $this->schema()->dropIfExists('page_revisions');
        $this->schema()->dropIfExists('pages');
        $this->schema()->dropIfExists('categories');
        $this->schema()->dropIfExists('media_files');
        $this->schema()->dropIfExists('users');
    }
};
