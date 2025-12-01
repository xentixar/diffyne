# Installation

## Requirements

- PHP 8.1 or higher
- Laravel 10.0, 11.0, or 12.0
- Composer

## Step-by-Step Installation

### 1. Install via Composer

```bash
composer require diffyne/diffyne
```

### 2. Publish Configuration

Publish the configuration file to customize Diffyne settings:

```bash
php artisan vendor:publish --tag=diffyne-config
```

This creates `config/diffyne.php` where you can configure:
- Component namespace
- View path
- Debug mode
- Cache settings

### 3. Publish Assets

Publish the JavaScript runtime to your public directory:

```bash
php artisan vendor:publish --tag=diffyne-assets
```

This copies `diffyne.js` to `public/vendor/diffyne/diffyne.js`.

### 4. Add Scripts to Layout

In your main layout file (e.g., `resources/views/layouts/app.blade.php`), add the Diffyne scripts directive before the closing `</body>` tag:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>My App</title>
    <!-- Your other head content -->
</head>
<body>
    <!-- Your content -->
    
    @diffyneScripts
</body>
</html>
```

The `@diffyneScripts` directive includes:
- The Diffyne JavaScript runtime
- CSRF token configuration
- Component initialization

## Verification

To verify your installation is working:

1. Create a test component:
```bash
php artisan make:diffyne TestComponent
```

2. Add it to a view:
```blade
@diffyne('TestComponent')
```

3. Visit the page in your browser and check the browser console for any errors.

## Asset Building (Development)

If you're developing Diffyne itself or need to rebuild assets:

```bash
cd packages/diffyne
npm install
npm run build
```

This compiles and minifies the JavaScript runtime.

## Configuration

### Basic Configuration

The `config/diffyne.php` file contains these options:

```php
return [
    // Namespace for your components
    'namespace' => 'App\\Diffyne',
    
    // View path for component templates
    'view_path' => 'diffyne',
    
    // Enable debug mode for detailed error messages
    'debug' => env('DIFFYNE_DEBUG', false),
    
    // Cache component metadata
    'cache' => env('DIFFYNE_CACHE', true),
];
```

### Environment Variables

Add these to your `.env` file:

```env
DIFFYNE_DEBUG=false
DIFFYNE_CACHE=true
```

## Next Steps

- [Quick Start Guide](quickstart.md) - Build your first component
- [Your First Component](first-component.md) - Detailed component creation tutorial
