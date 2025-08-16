import os
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv(os.path.join(os.path.dirname(__file__), '../../.env'))

# Sunnah.com API configuration
SUNNAH_API_BASE_URL = "https://api.sunnah.com/v1"
SUNNAH_API_KEY = os.getenv('SUNNAH_COM_API_KEY')

# Database configuration
DB_CONFIG = {
    'host': os.getenv('DB_HOST', 'localhost'),
    'user': os.getenv('DB_USERNAME', 'root'),
    'password': os.getenv('DB_PASSWORD', ''),
    'database': os.getenv('HADITH_DB_DATABASE', 'islamwiki_hadith'),
    'port': int(os.getenv('DB_PORT', 3306)),
    'charset': 'utf8mb4',
    'use_unicode': True
}

# Import settings
BATCH_SIZE = 100  # Number of hadiths to process in each batch
REQUEST_DELAY = 1  # Delay between API requests in seconds
