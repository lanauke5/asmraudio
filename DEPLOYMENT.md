# Deployment — asmraudio.online

## Server location

Upload the project to `/www/wwwroot/asmraudio.online/`. Keep the existing production `config.php`; never replace it with `config.example.php`.

## Existing database upgrade

Back up the database, then import `database/migrations-prompt.sql` once. A new installation should import `database/install.sql` instead and must not also import the migration file.

## Nginx URL rewrite rules

```nginx
rewrite ^/sitemap\.xml$ /sitemap.php last;
rewrite ^/audio/?$ /audio.php last;
rewrite ^/asmr-creators/?$ /creator.php last;
rewrite ^/asmr-equipment/?$ /products.php last;
rewrite ^/(asmr-guides|asmr-triggers|sleep-sounds|focus-relaxation)/?$ /category.php?slug=$1 last;
rewrite ^/go/([a-z0-9-]+)/?$ /go.php?slug=$1 last;
rewrite ^/audio/([a-z0-9-]+)/?$ /audio-detail.php?slug=$1 last;
rewrite ^/creators/([a-z0-9-]+)/?$ /creator-detail.php?slug=$1 last;
rewrite ^/products/([a-z0-9-]+)/?$ /product.php?slug=$1 last;
rewrite ^/([a-z0-9-]+)/([a-z0-9-]+)/?$ /article.php?slug=$2 last;
rewrite ^/category/([a-z0-9-]+)/?$ /category.php?slug=$1 permanent;
rewrite ^/([a-z0-9-]+)/?$ /article.php?slug=$1 last;
```

Place the specific rules above the generic article rules, save, test the Nginx configuration, then reload Nginx.

## Permissions

- Directories: `755`
- PHP, CSS, JavaScript, SQL, and text files: `644`
- `config.php`: `600` when supported, otherwise `640`
- `uploads/image`, `uploads/audio`, and `storage`: writable by the PHP-FPM user; normally `775`

## Scheduled publishing

Scheduled content is promoted on normal traffic. For reliable timing, add:

```cron
*/5 * * * * curl -fsS https://asmraudio.online/ >/dev/null
```

## Post-deployment checks

Test the homepage, category and article clean URLs, audio playback and timer, creator profile, search pagination, contact and subscription forms, affiliate redirects, advertisements, admin login/logout, CRUD forms, uploads, sitemap, RSS, robots.txt, and a nonexistent URL.
