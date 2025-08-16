# Hadith Import Script (PHP Version)

This script imports hadith data from sunnah.com into the Islam Wiki database using PHP, which is already available in your environment.

## Prerequisites

1. PHP 7.4 or higher with cURL and PDO MySQL extensions
2. MySQL/MariaDB database with the hadith tables already created
3. API key from sunnah.com

## Setup

1. **Get an API Key**:
   - Visit [sunnah.com/developers](https://sunnah.com/developers)
   - Sign up or log in to get your API key

2. **Configure Database**:
   - Make sure your database tables are created (run migrations if needed)
   - The script will use the same database connection as your Laravel application

## Usage

Run the script from the command line with your API key:

```bash
cd /var/www/html/local.islam.wiki
php scripts/hadith_import/import_hadiths.php --api-key=YOUR_API_KEY_HERE
```

## What It Does

1. Fetches all hadith collections from sunnah.com
2. For each collection, it:
   - Creates or updates the collection record
   - Fetches all books in the collection
   - For each book, it fetches all hadiths
   - Imports any new hadiths that don't already exist

## Notes

- The script is designed to be idempotent - it won't import duplicates
- It includes rate limiting to be respectful to the sunnah.com API
- Progress is shown in the console as it runs

## Troubleshooting

- **Database Connection Issues**:
  - Make sure your `.env` file has the correct database credentials
  - The script uses the `HADITH_DB_DATABASE` environment variable (defaults to 'islamwiki_hadith')

- **API Issues**:
  - Verify your API key is correct
  - Check that you have remaining API quota on sunnah.com

- **Memory Issues**:
  - The script processes data in chunks to avoid memory issues
  - If you encounter memory problems, you may need to increase PHP's memory limit
