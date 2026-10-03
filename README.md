<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Restaurant menu management

### Administration

- Restaurant, owner, and subscription management includes validation and Arabic/English interface support.
- Restaurant notes are private to administrators: use them for agreements and reminders, not public menu text.
- Deletion requires confirmation. Owners with other restaurants and administrator accounts must be retained. Unused owner accounts can be cleaned up separately.
- Account and restaurant deletion is permanent; back up the database and public uploads before deployment or bulk cleanup.

### Bilingual menus

Owners can enter Arabic and optional English restaurant, category, product, option-group, and option text. Existing Arabic content is preserved. Missing English translations fall back to Arabic rather than displaying empty labels. The customer language selector changes menu content, direction, search, option labels, and order text.

The additive migrations restore English fields removed by the historic Arabic-only migration; they cannot recover previously deleted translations.

### Backgrounds and welcome videos

Backgrounds and logos are processed using PHP GD and the existing Intervention Image dependency. JPEG, PNG, GIF, and WebP are supported; images are resized without enlargement. Invalid images and storage failures are surfaced instead of silently storing an unprocessed upload.

Welcome videos accept MP4, MOV, or WebM, with a **10 MB upload limit** and **15-second duration limit**. FFprobe checks actual video metadata, then FFmpeg produces a silent H.264 MP4, up to 1280 × 720 at 24 fps, with fast-start playback. Output larger than **2 MB** is rejected. No external video processing service is used.

Install FFmpeg with the `libx264` encoder and FFprobe on the application server, and configure `FFMPEG_BINARY` and `FFPROBE_BINARY` in the environment (executable names on PATH, or absolute executable paths). Without these processors, video uploads are explicitly unavailable; existing menus remain usable. PHP's `upload_max_filesize` must be at least `10M`, and `post_max_size` and reverse-proxy request limits should be higher to allow form overhead. Allow sufficient request execution time for local encoding.

### Deployment and validation

1. Back up the database and public storage.
2. Apply the new migrations through your normal Laravel deployment procedure.
3. Ensure the public storage link is configured, PHP GD is enabled, and the video executables are available.
4. Build frontend assets with `npm run build`.
5. Run regression coverage with `php vendor\bin\pest`. The real encoding test runs when both video executables are configured; otherwise it reports an explicit skip.

No live database migrations or existing-account deletions are performed merely by updating the code.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
