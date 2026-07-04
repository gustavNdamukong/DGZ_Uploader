![issues](https://img.shields.io/github/issues/gustavNdamukong/DGZ_Uploader)
![forks](https://img.shields.io/github/forks/gustavNdamukong/DGZ_Uploader)
![stars](https://img.shields.io/github/stars/gustavNdamukong/DGZ_Uploader)
![license](https://img.shields.io/github/license/gustavNdamukong/DGZ_Uploader)

# DGZ_Uploader

### Upload and resize images in Laravel however you please.

DGZ_Uploader is a Laravel package that handles file uploads with a clean, straightforward API. It covers the full cycle: moving files to a configured destination, detecting duplicates and renaming them automatically, generating scaled-down thumbnails, and reporting exactly what happened through a messages array you can inspect at any time.

The package is built around three classes that layer on top of each other:

- **`DGZ_Upload`** — the base class. Handles moving files, validating size and MIME type, and renaming duplicates. Use this directly when you want uploads without automatic thumbnail generation (videos, audio, PDFs, etc.).
- **`DGZ_Thumbnail`** — standalone thumbnail generator. Takes an existing image path and creates a proportionally scaled copy. You rarely instantiate this yourself; `DGZ_Uploader` does it for you.
- **`DGZ_Uploader`** — extends `DGZ_Upload`. On top of everything the base class does, it generates a thumbnail the moment an image is uploaded, in the same operation. This is the class you will use most of the time.

---

## Requirements

- PHP >= 7.4 (PHP 8.x recommended)
- Laravel 8 or later
- **GD extension enabled** (`extension=gd` in `php.ini`), with support for the image formats you upload (PNG, JPEG, GIF, WebP) — required for thumbnail generation. Without GD you'll get a fatal `Call to undefined function ...imagecreatefrompng()` at upload time. On shared / cPanel hosting, enable it under **Select PHP Version → Extensions → `gd`**.

---

## Installation

Install via Composer from your Laravel project root:

```bash
composer require dgz_uploader/dgz_uploader
```

### Publish the config file

```bash
php artisan vendor:publish
```

This copies the package config into your application at `config/dgz_uploader.php`. Open that file to set your upload destinations and maximum file size. You must do this before using the package, because the uploader reads destination paths from that config file at runtime.

---

## Configuration

The published config file lives at `config/dgz_uploader.php` and contains two things:

### 1. Maximum file size

```php
'maxFileUploadSize' => '10MB',
```

This is the ceiling for all uploads through this package. You can write it as a human-readable string (`'5MB'`, `'500KB'`, `'1.5GB'`) or as a raw integer in bytes. Both formats are accepted:

```php
'maxFileUploadSize' => '5MB',      // human-readable — recommended
'maxFileUploadSize' => 5242880,    // raw bytes — also works
```

### 2. Upload destination paths

You define one or more named destinations. Each key is a label you pass to `DGZ_Uploader()`, and each value is the path — relative to Laravel's `public/` folder — where files will be stored. The trailing slash is required.

```php
'default'      => 'images/store_imgs/',
'gallery'      => 'images/gallery/',
'userProfiles' => 'images/profiles/',
'products'     => 'images/products/',
```

You can add as many entries as your application needs. At runtime you tell the uploader which destination to use by passing its key name to the constructor.

> **Note:** Make sure each directory exists and is writable before uploading to it. The package will throw an exception if the path is not a valid, writable directory.

---

## Basic usage

### Step 1 — Set up your HTML form

Your form must use `enctype="multipart/form-data"` and `method="POST"`:

```html
<form action="/upload" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="photo">
    <button type="submit">Upload</button>
</form>
```

For multiple file uploads, add `multiple` to the input and use an array name:

```html
<input type="file" name="photos[]" multiple>
```

### Step 2 — Call the uploader in your controller

```php
use DGZ_Uploader\DGZ_Uploader;

$upload = new DGZ_Uploader('default');
$upload->move('original');
```

That is the minimum. The uploader reads `$_FILES` automatically — you do not pass the file to it manually.

---

## The constructor: choosing a destination

```php
new DGZ_Uploader(string $path, string $uniqueSubFolder = '')
```

**`$path`** is the key of the destination you defined in `config/dgz_uploader.php`:

```php
$upload = new DGZ_Uploader('gallery');       // uploads to images/gallery/
$upload = new DGZ_Uploader('userProfiles');  // uploads to images/profiles/
$upload = new DGZ_Uploader('default');       // uploads to images/store_imgs/
```

**`$uniqueSubFolder`** is an optional sub-directory appended to the destination. This is useful when individual records need their own folder — for example, a gallery album, a user's profile, or a product in an e-commerce catalogue:

```php
// Upload images for album ID 42 into images/gallery/42/
$upload = new DGZ_Uploader('gallery', '42');

// Upload images for product SKU 'shirt-blue' into images/products/shirt-blue/
$upload = new DGZ_Uploader('products', 'shirt-blue');
```

The sub-folder must already exist and be writable. The package will not create it for you.

---

## The move() method: uploading

```php
$upload->move(string $modify = 'original', bool $overwrite = false)
```

`move()` is what actually performs the upload. It takes two optional arguments.

### $modify — what to do with the file

| Value | Behaviour |
|---|---|
| `'original'` | Upload the file as-is. MIME type and size are validated against your config. |
| `'resize'` | Upload the file and generate a thumbnail automatically. MIME type and size are validated. |
| `'original-allow'` | Upload the file with no MIME type or size validation. Use this in admin-only areas for files like videos and audio that fall outside the default allowed types. |

`'original'` is the default, so calling `$upload->move()` with no arguments is equivalent to `$upload->move('original')`.

### $overwrite — what to do with duplicate filenames

| Value | Behaviour |
|---|---|
| `false` (default) | If a file with the same name already exists at the destination, the new file is automatically renamed by appending `_1`, `_2`, etc. Both files are kept. |
| `true` | If a file with the same name exists, it is overwritten. |

### Examples

```php
// Upload as-is, rename duplicates
$upload->move();
$upload->move('original');
$upload->move('original', false);

// Upload as-is, overwrite duplicates
$upload->move('original', true);

// Upload and generate a thumbnail
$upload->move('resize');

// Upload a video or audio file (no type/size checking)
$upload->move('original-allow');
```

---

## Retrieving uploaded filenames

After `move()` completes, call `getFilenames()` to retrieve the names of successfully uploaded files. It returns an array, regardless of how many files were uploaded.

```php
$upload = new DGZ_Uploader('gallery');
$upload->move('original');

$filenames = $upload->getFilenames();
```

For a **single file upload**, grab the first element:

```php
$filename = $upload->getFilenames()[0];
```

For **multiple file uploads**, loop through the array:

```php
foreach ($upload->getFilenames() as $filename) {
    echo $filename;
}
```

The returned names are the final names on disk — if the file was renamed to avoid a collision, `getFilenames()` gives you the renamed version, not the original submitted name.

---

## Handling errors

If an upload fails silently, `getMessages()` tells you why. It returns an array of strings — one message per file processed, covering upload errors, size violations, disallowed MIME types, and successful uploads.

The recommended pattern is to check for a filename first, and only fall back to messages if nothing was uploaded:

```php
$upload = new DGZ_Uploader('default');
$upload->move('original');

if (!empty($upload->getFilenames())) {
    $filename = $upload->getFilenames()[0];
    // store $filename in the database, redirect with success, etc.
} else {
    return redirect()->back()->withErrors($upload->getMessages());
}
```

---

## Controlling the maximum upload size

By default the package uses whatever you set in `config/dgz_uploader.php`. If you need to override it at runtime for a specific upload, call `setMaxSize()` before `move()`:

```php
$upload = new DGZ_Uploader('gallery');
$upload->setMaxSize('2MB');   // human-readable
$upload->setMaxSize(2097152); // raw bytes — equivalent, also accepted
$upload->move('original');
```

To read the current limit back as a formatted string:

```php
echo $upload->getMaxSize(); // e.g. "2,048.0kB"
```

---

## Thumbnail control (when using move('resize'))

When you call `move('resize')`, `DGZ_Uploader` automatically creates a scaled-down thumbnail immediately after uploading the original. Several methods let you control exactly how that thumbnail is generated. Call them before `move()`.

### setThumbMaxSize(int $size)

Sets the maximum pixel dimension (width or height, whichever is larger) of the thumbnail. The image is scaled proportionally so it fits within a square of this size without cropping. Default is **500px**.

```php
$upload = new DGZ_Uploader('gallery');
$upload->setThumbMaxSize(300); // thumbnail will be at most 300×300
$upload->move('resize');
```

### setThumbSuffix(string $suffix)

Sets the string appended to the thumbnail filename to distinguish it from the original. Default is `'_thb'`.

```php
$upload->setThumbSuffix('_small'); // e.g. photo_small.jpg
$upload->setThumbSuffix('thb');    // leading underscore added automatically → photo_thb.jpg
$upload->setThumbSuffix('');       // thumbnail has the same name as the original
```

### setThumbDestination(string $path)

By default thumbnails are saved to the same folder as the original. Call this to redirect thumbnails to a separate directory:

```php
$upload = new DGZ_Uploader('gallery');
$upload->setThumbDestination(public_path('images/gallery/thumbs/'));
$upload->move('resize');
```

The path must be an absolute path to a directory that already exists and is writable.

---

## Quality control for JPEG and WebP thumbnails

`DGZ_Thumbnail` exposes a `setQuality()` method if you need to set thumbnail compression directly. However, when using `DGZ_Uploader`, quality is managed through the `DGZ_Thumbnail` instance created internally. The default quality is **82**, which gives excellent visual results at roughly 60% the file size of quality 100.

If you need a different quality level, instantiate `DGZ_Thumbnail` directly:

```php
use DGZ_Uploader\DGZ_Thumbnail;

$thumb = new DGZ_Thumbnail('/absolute/path/to/original.jpg');
$thumb->setDestination('/absolute/path/to/thumbs/');
$thumb->setQuality(90); // 1-100; has no effect on PNG or GIF output
$thumb->create();
```

PNG output always uses compression level 6 (the web standard). GIF thumbnails have no quality setting.

---

## WebP support

DGZ_Uploader v1.1.0 adds full WebP support end-to-end:

- **Uploading WebP files**: `image/webp` is now an accepted MIME type by default. No extra configuration needed.
- **Thumbnails from WebP originals**: the thumbnail generator detects WebP images, reads them with `imagecreatefromwebp()`, preserves the alpha channel, and saves the thumbnail as `.webp`.
- **Alpha transparency**: PNG and WebP thumbnails both have their alpha channel preserved correctly during resampling.

No code changes are required to take advantage of WebP — if a user uploads a `.webp` file, it flows through the same `move()` call as a JPEG or PNG.

---

## Static helper methods

Two static helpers make it easier to derive filenames in your application code, particularly when you need to store the thumbnail path in a database after upload.

### DGZ_Upload::extension(string $filename): string

Returns the file extension without the dot:

```php
DGZ_Upload::extension('sunset.jpg');   // 'jpg'
DGZ_Upload::extension('photo.PNG');    // 'PNG'
DGZ_Upload::extension('clip.webp');    // 'webp'
```

You can also call it on an instance:

```php
$upload->extension($filenames[0]);
```

### DGZ_Upload::thumbName(string $filename, string $suffix = '_thb'): string

Derives the thumbnail filename from an original filename. Pass a custom suffix if you changed it with `setThumbSuffix()`:

```php
DGZ_Upload::thumbName('sunset.jpg');          // 'sunset_thb.jpg'
DGZ_Upload::thumbName('hero.png', '_small');  // 'hero_small.png'
DGZ_Upload::thumbName('photo.webp');          // 'photo_thb.webp'
```

A typical post-upload pattern when storing to a database:

```php
$upload = new DGZ_Uploader('gallery');
$upload->move('resize');

$original  = $upload->getFilenames()[0];
$thumbnail = DGZ_Upload::thumbName($original); // 'photo_thb.jpg'

// Now store both $original and $thumbnail in your DB record
```

---

## Uploading non-image files

`DGZ_Upload` (the base class, not `DGZ_Uploader`) is the right tool for videos, audio, PDFs, and other files where you do not need thumbnail generation:

```php
use DGZ_Uploader\DGZ_Upload;

// In the constructor, pass an absolute path directly instead of a config key
$upload = new DGZ_Upload(public_path('videos/'));
$upload->move('original-allow'); // bypasses MIME type and size checks
```

Alternatively, use `DGZ_Uploader` with `'original-allow'` if you want to keep the config-key-based destination:

```php
$upload = new DGZ_Uploader('videoUploadDir');
$upload->move('original-allow');
```

---

## Permitted MIME types

The following types are accepted by default when using `'original'` or `'resize'`:

```
image/gif
image/jpeg
image/pjpeg
image/jpg
image/png
image/webp
```

### Adding custom types

Use `addPermittedTypes()` to allow additional types beyond the defaults. The method validates that any type you add belongs to a pre-approved safe list, so you cannot accidentally allow arbitrary binary uploads:

```php
$upload = new DGZ_Uploader('documents');
$upload->addPermittedTypes('application/pdf');
$upload->addPermittedTypes(['text/plain', 'text/rtf']);
$upload->move('original');
```

The full list of types you can add via this method is: `image/tiff`, `image/webp`, `application/pdf`, `text/plain`, `text/rtf`. For anything else, use `move('original-allow')` in a protected admin context.

---

## Full working example

```php
use DGZ_Uploader\DGZ_Uploader;
use DGZ_Uploader\DGZ_Upload;

public function store(Request $request)
{
    // Upload to the 'gallery' destination, into a subfolder named after the album ID
    $albumId = $request->input('album_id');
    $upload  = new DGZ_Uploader('gallery', $albumId);

    // Thumbnails should be at most 300px on the longest side
    $upload->setThumbMaxSize(300);

    // Upload original + generate _thb thumbnail in one step
    $upload->move('resize');

    if (empty($upload->getFilenames())) {
        return redirect()->back()->withErrors($upload->getMessages());
    }

    $original  = $upload->getFilenames()[0];
    $thumbnail = DGZ_Upload::thumbName($original); // e.g. 'sunset_thb.jpg'

    // Store paths relative to the upload destination, not the full server path
    GalleryImage::create([
        'album_id'  => $albumId,
        'filename'  => $original,
        'thumbnail' => $thumbnail,
    ]);

    return redirect()->route('gallery.index')->with('success', 'Image uploaded.');
}
```

---

## What's new in v1.1.0

| Area | Change |
|---|---|
| `setMaxSize()` | Now accepts human-readable strings (`'5MB'`, `'500KB'`, `'1.5GB'`). Raw integers (bytes) still work — fully backwards compatible. |
| WebP | Full end-to-end WebP support: upload, thumbnail generation, and alpha channel preservation. |
| `setThumbSuffix()` | Restored — was silently broken in v1.0.0 (always produced an empty suffix). |
| `setThumbMaxSize()` | New method. Control the pixel dimension cap for thumbnails. |
| Thumbnail quality | Default changed from 100 → 82 for JPEG and WebP thumbnails (visually excellent; much smaller files). Use `setQuality()` on `DGZ_Thumbnail` directly if you need a different value. |
| PNG compression | Thumbnail PNG compression changed from 0 (no compression) → 6 (web standard). |
| Alpha channel | PNG and WebP thumbnails now correctly preserve transparency during resampling. |
| `extension()` | New static helper. Returns the extension of a filename without the dot. |
| `thumbName()` | New static helper. Derives a thumbnail filename from an original filename. |
| `test()` | Debugging helper on `DGZ_Thumbnail` is now accessible (was commented out in v1.0.0). |

---

## License

MIT

---

## Credits

An open-source package by [Nolimit Media](https://nolimitmedia.ca).
