# Hadith Extension for Islam Wiki

This extension provides comprehensive hadith browsing and search functionality for Islam Wiki, allowing users to explore collections of hadith with ease.

## Features

- Browse hadith collections and books
- Search across hadith texts with filters
- View detailed hadith information including:
  - Arabic text with diacritics
  - Translations in multiple languages
  - Chain of narrators (Isnad)
  - Grading and authenticity information
  - Related hadiths
- Narrator profiles with biographical information
- Responsive design for all device sizes

## Installation

1. Ensure the extension is properly registered in your `config/app.php` file
2. Run database migrations:
   ```bash
   php migrate.php
   ```
3. Clear the application cache:
   ```bash
   php cache:clear
   ```

## Configuration

Configuration options can be set in `config/hadith.php`:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Hadith Collection
    |--------------------------------------------------------------------------
    |
    | This value determines the default hadith collection to show when
    | users visit the hadith index page.
    */
    'default_collection' => 'bukhari',
    
    /*
    |--------------------------------------------------------------------------
    | Items Per Page
    |--------------------------------------------------------------------------
    |
    | This value determines the number of hadith to show per page in search
    | results and collection views.
    */
    'items_per_page' => 20,
];
```

## Usage

### Routes

- `GET /hadiths` - List all available hadith collections
- `GET /hadiths/collection/{collection}` - View a specific collection
- `GET /hadiths/collection/{collection}/book/{book}` - View a specific book within a collection
- `GET /hadiths/collection/{collection}/hadith/{hadith}` - View a specific hadith
- `GET /hadiths/narrator/{narrator}` - View narrator information
- `GET /hadiths/search` - Search hadith database

### API Endpoints

- `GET /api/hadith/collections` - List all available collections
- `GET /api/hadith/collection/{collection}` - Get collection details
- `GET /api/hadith/hadiths/{id}` - Get hadith by ID
- `GET /api/hadith/search` - Search hadith database
- `GET /api/hadith/random` - Get a random hadith

## Database Schema

The extension uses the following database tables:

- `hadith_collections` - Stores hadith collections (e.g., Sahih Bukhari, Sahih Muslim)
- `hadith_books` - Books within collections
- `hadith_narrations` - Individual hadith narrations
- `hadith_narrators` - Chain of narrators
- `hadith_comments` - Scholarly commentary on hadith

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
