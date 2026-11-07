# GhostLink deployment and themes

## Database setup

1. Create a MySQL 8+ database and a dedicated database user using your host's control panel. Grant that user access only to the GhostLink database.
2. Import `database/schema.sql` into the new database using the hosting control panel or `mysql -u USER -p DATABASE < database/schema.sql`.
3. Set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` in the PHP hosting environment. `.env.example` documents the variables; PHP does not load it automatically. Never commit production credentials.
4. Ensure PHP has PDO MySQL and MySQLi enabled and that `uploads/` is writable by PHP. Keep the uploads directory from executing PHP scripts.

The repository cannot provision a live database or know your host credentials. This schema is for a fresh install; back up existing data and plan a migration before applying it to an existing installation.

## Shared themes and preferences

`assets/themes/ghostlink.css` is the shared theme layer. It defines the CSS token contract and appearance preferences for the Glass, Aurora, Midnight, and Rose palettes. Add new palettes by extending its `body[data-theme="..."]` token declarations and the theme selector in `assets/theme-settings.js`.

The settings script saves appearance, density, font size, and animation preferences in browser local storage. Browser-level preferences work without database setup; account-synchronized preferences can be layered onto the `user_settings` table later.
