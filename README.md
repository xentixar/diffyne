# Diffyne

A blazing-fast, server-driven UI framework for PHP powered by a lightweight **Virtual DOM + Diff Engine** (Delta Rendering Engine).  
Diffyne lets you build dynamic interfaces with the simplicity of Blade/PHP components — but with the rendering efficiency of modern SPA frameworks.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-%5E12.0-red)](https://laravel.com)

## 🚀 Why Diffyne?

- **🔥 Ultra-efficient:** 70-95% smaller payloads than traditional approaches
- **🧠 Server-driven:** Write UI logic in PHP, not JavaScript
- **⚡ Lightweight:** ~23 KB JS, sub-100ms response times
- **✅ Laravel Native:** Full validation, authentication, and ORM integration

## 📦 Quick Start

```bash
# Install via Composer
composer require diffyne/diffyne

# Publish assets
php artisan vendor:publish --tag=diffyne-assets

# Add to your layout before </body>
@diffyneScripts

# Create your first component
php artisan make:diffyne Counter
```

**[📖 Full Installation Guide →](https://github.com/diffyne/docs/blob/main/getting-started/installation.md)**

## 🧩 Example Component

**Component Class** (`app/Diffyne/Counter.php`):
```php
use Diffyne\Attributes\Invokable;
use Diffyne\Component;

class Counter extends Component
{
    public int $count = 0;

    #[Invokable]
    public function increment()
    {
        $this->count++;
    }
}
```

**Component View** (`resources/views/diffyne/counter.blade.php`):
```blade
<div>
    <h1>Count: {{ $count }}</h1>
    <button diff:click="increment">Increment</button>
</div>
```

**Usage:**
```blade
@diffyne('Counter')
```

When the button is clicked, Diffyne sends only the minimal patch (~50 bytes) instead of the full HTML (~400 bytes)!

**[🎯 More Examples →](https://github.com/diffyne/docs/tree/main/examples)**

## ⚙️ Core Features

| Directive | Description |
|-----------|-------------|
| `diff:click` | Call server method on click |
| `diff:model` | Two-way data binding |
| `diff:submit` | Handle form submission |
| `diff:poll` | Auto-refresh at intervals |
| `diff:loading` | Show loading states |
| `diff:error` | Display validation errors |

```blade
{{-- Live search with debouncing --}}
<input diff:model.live.debounce.300="search">

{{-- Form with validation --}}
<form diff:submit="submit">
    <input diff:model="email">
    <span diff:error="email"></span>
    <button type="submit" diff:loading.class.opacity-50>Submit</button>
</form>
```

**[📚 Complete Directives Guide →](https://github.com/diffyne/docs/blob/main/features/directives.md)**

## 📖 Documentation

📚 **Full documentation is available in the [Diffyne Docs Repository](https://github.com/diffyne/docs)**

### Getting Started
- [Installation](https://github.com/diffyne/docs/blob/main/getting-started/installation.md)
- [Quick Start](https://github.com/diffyne/docs/blob/main/getting-started/quickstart.md)
- [Your First Component](https://github.com/diffyne/docs/blob/main/getting-started/first-component.md)

### Features
- [Directives Overview](https://github.com/diffyne/docs/blob/main/features/directives.md)
- [Click Events](https://github.com/diffyne/docs/blob/main/features/click-events.md)
- [Data Binding](https://github.com/diffyne/docs/blob/main/features/data-binding.md)
- [Forms](https://github.com/diffyne/docs/blob/main/features/forms.md)
- [Validation](https://github.com/diffyne/docs/blob/main/features/validation.md)
- [Loading States](https://github.com/diffyne/docs/blob/main/features/loading-states.md)
- [Polling](https://github.com/diffyne/docs/blob/main/features/polling.md)
- [Error Handling](https://github.com/diffyne/docs/blob/main/features/error-handling.md)

### Examples
- [Counter Component](https://github.com/diffyne/docs/blob/main/examples/counter.md)
- [Todo List](https://github.com/diffyne/docs/blob/main/examples/todo-list.md)
- [Contact Form](https://github.com/diffyne/docs/blob/main/examples/contact-form.md)
- [Live Search](https://github.com/diffyne/docs/blob/main/examples/search.md)

### Advanced
- [Virtual DOM Engine](https://github.com/diffyne/docs/blob/main/advanced/virtual-dom.md)
- [Lifecycle Hooks](https://github.com/diffyne/docs/blob/main/advanced/lifecycle-hooks.md)
- [Component State](https://github.com/diffyne/docs/blob/main/advanced/component-state.md)
- [Performance](https://github.com/diffyne/docs/blob/main/advanced/performance.md)
- [Testing](https://github.com/diffyne/docs/blob/main/advanced/testing.md)

## ⚡ Performance

- **70-95% smaller payloads** than traditional HTML-over-the-wire approaches
- **Sub-100ms response times** for most operations
- **~12 KB minified JS**
- Only changed DOM nodes are updated
- Automatic input value syncing
- Built-in validation with automatic error display

## 🛣 Roadmap

**v1.0 (Current)**
- ✅ Virtual DOM diff engine
- ✅ Core directives & data binding
- ✅ Form validation
- ✅ Lifecycle hooks
- ✅ Loading states & polling
- ✅ Component events (dispatch, dispatchTo, dispatchSelf)
- ✅ Query string binding
- ✅ WebSocket support (via Sockeon)
- ✅ Security features (state signing, locked properties)
- ✅ Attributes (Locked, QueryString, On, Invokable, Computed, Lazy)
- ✅ Redirects (SPA navigation)
- ✅ Browser events

**Coming Soon**
- File uploads
- Flash messages
- Nested components

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](.github/CONTRIBUTING.md) for details.

- [Contributing Guide](.github/CONTRIBUTING.md)
- [Code of Conduct](.github/CODE_OF_CONDUCT.md)
- [Security Policy](.github/SECURITY.md)
- [Support](.github/SUPPORT.md)

## 📝 License

MIT License © 2025 Diffyne Team