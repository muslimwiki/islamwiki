# Hadith Import Script

This script imports hadith data from sunnah.com into the Islam Wiki database.

## Prerequisites

1. Python 3.8+
2. MySQL/MariaDB database
3. API key from sunnah.com

## Setup

1. Install the required Python packages:
   ```bash
   pip install -r requirements.txt
   ```

2. Create a `.env` file in the project root with your database and API credentials:
   ```env
   # Database
   DB_HOST=localhost
   DB_DATABASE=islamwiki
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_db_password
   DB_PORT=3306
   
   # Sunnah.com API
   SUNNAH_COM_API_KEY=your_api_key_here
   ```

## Usage

1. Make the script executable:
   ```bash
   chmod +x import_hadiths.py
   ```

2. Run the import script:
   ```bash
   cd /var/www/html/local.islam.wiki/scripts/hadith_import
   python3 import_hadiths.py
   ```

## Features

- Imports hadith collections from sunnah.com
- Checks for existing records to avoid duplicates
- Handles errors and provides feedback
- Configurable batch sizes and request delays

## Notes

- The script requires an API key from sunnah.com
- It's recommended to run this in a development environment first
- The script can be stopped and restarted; it will skip already imported data
