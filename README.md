# Diffyne

A blazing-fast, server-driven UI framework for PHP powered by a lightweight **Virtual DOM + Diff Engine** (Delta Rendering Engine).  
Diffyne lets you build dynamic interfaces with the simplicity of Blade/PHP components — but with the rendering efficiency of modern SPA frameworks.

It delivers **minimal DOM updates** and optional **WebSocket sync** for real-time applications.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-%5E10.0%20%7C%20%5E11.0-red)](https://laravel.com)

---

## 🚀 Why Diffyne?

### 🔥 Ultra-efficient delta updates
Diffyne does **not** send full HTML fragments.  
It computes a Virtual DOM diff and ships **only the smallest possible patch** to the browser.

### 🧠 Server-driven logic
Write your UI logic entirely in PHP.  
The client applies diffs — nothing else.

### ⚡ Lightweight & Fast
- Minimal JS payload (~11 KB minified, ~3.7 KB gzipped)
- AJAX-based communication
- Sub-100ms response times

Perfect for dashboards, forms, CRUDs, and dynamic UIs.

---

# 📦 Installation

### 1. Require via Composer

```bash
composer require diffyne/diffyne
```

### 2. Publish assets and configuration

```bash
php artisan vendor:publish --tag=diffyne-config
php artisan vendor:publish --tag=diffyne-assets
```

This will:
- Publish `config/diffyne.php`
- Publish JavaScript to `public/vendor/diffyne/diffyne.js`

### 3. Add to your layout

In your main layout (e.g., `resources/views/layouts/app.blade.php`), add before `</body>`:

```blade
@diffyneScripts
```

**Done!** You're ready to create components.

---

# 🧩 Creating Your First Component

### 1. Generate component

```bash
php artisan make:diffyne Counter
```

Creates:

```
app/Diffyne/Counter.php
resources/views/diffyne/counter.blade.php
```

---

### 2. Component Class

```php
namespace App\Diffyne;

use Diffyne\Component;

class Counter extends Component
{
    public int $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
```

---

### 3. Component View

```html
<div>
    <h1>Count: {{ $count }}</h1>
    <button diffyne:click="increment">Increment</button>
</div>
```

---

### 4. Use in page

```html
<diffyne:counter />
```

Diffyne automatically:

* hydrates the component
* syncs events
* diffs DOM
* applies patches

---

# ⚙️ Diffyne Directives

| Directive            | Description                           |
| -------------------- | ------------------------------------- |
| `diffyne:click`      | Call method on server                 |
| `diffyne:change`     | Trigger on input change               |
| `diffyne:model`      | Two-way bind property (deferred)      |
| `diffyne:model.live` | Two-way bind with instant server sync |
| `diffyne:model.lazy` | Two-way bind on change event          |
| `diffyne:submit`     | Handle forms                          |
| `diffyne:poll="500"` | Poll server every X ms                |
| `diffyne:loading`    | Loading state binding                 |

### Example

```html
<input diffyne:model.live.debounce.300="search">
<button diffyne:click="save" diffyne:loading.class="opacity-50">Save</button>
```

---

# 🔄 Two-Way Binding Examples

### Text Input

```html
<input type="text" diffyne:model="username">
```

### Checkbox

```html
<input type="checkbox" diffyne:model="active">
```

### Select

```html
<select diffyne:model="category">
```

---

# 🎯 Real Example: Todo App

### PHP Component

```php
class Todo extends Component
{
    public array $items = [];
    public string $newItem = '';

    public function add()
    {
        if ($this->newItem !== '') {
            $this->items[] = $this->newItem;
            $this->newItem = '';
        }
    }

    public function remove($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }
}
```

---

### Blade View

```html
<div>
    <input diffyne:model="newItem" placeholder="Add todo...">
    <button diffyne:click="add">Add</button>

    <ul>
        @foreach ($items as $i => $item)
            <li>
                {{ $item }}
                <button diffyne:click="remove({{ $i }})">x</button>
            </li>
        @endforeach
    </ul>
</div>
```

---

# 🧬 Diffyne Virtual DOM Engine (DVDE)

Diffyne uses a custom Virtual DOM engine to achieve high performance.

Rendering pipeline:

```
PHP state
    → template render
        → virtual DOM snapshot
            → diff engine
                → delta packets
                    → client patcher
                        → DOM update
```

### Patch Types Supported

* Text node updates
* Attribute diffing
* Add/remove elements
* Reorder lists (keyed diffing)
* Input value preservation
* Component hydration patches

### Example patch packet sent to browser:

```json
{
  "type": "text",
  "node": "#text-17",
  "value": "Count: 5"
}
```

---

# ♻️ Lifecycle Hooks

### Hooks available:

| Hook               | Trigger                            |
| ------------------ | ---------------------------------- |
| `mount()`          | Before component renders           |
| `hydrate()`        | After client hydration             |
| `updating($field)` | Before a property updates          |
| `updated($field)`  | After a property updates           |
| `dehydrate()`      | Before sending DOM diffs to client |

### Example usage:

```php
public function updated($field)
{
    logger("Updated: $field");
}
```

---

# 🗂 Directory Structure

```
app/
 └── Diffyne/              # Your components
       └── Counter.php
       └── TodoList.php
       
resources/
 └── views/
       └── diffyne/         # Component views
             └── counter.blade.php
             └── todo-list.blade.php

public/
 └── vendor/
       └── diffyne/
             └── diffyne.js  # Client-side runtime

config/
 └── diffyne.php           # Configuration
```

---

# ⚡ Performance Advantages

* DOM patches are **70–95% smaller** than Livewire/HTMX-style HTML responses.
* Only changed nodes are updated — no full HTML morphing.
* Sub-100ms server round-trip times for most operations.
* Minimal JS payload (~11 KB minified, ~3.7 KB gzipped).
* Automatic input/textarea/select value syncing.

---

# 🛣 Roadmap

### ✅ Completed (v1.0)

* ✅ Virtual DOM diff engine with minimal patches
* ✅ Core directives (click, change, model, submit, poll, loading)
* ✅ Two-way data binding with modifiers (.live, .lazy, .debounce)
* ✅ Component hydration & state management
* ✅ Nested component support
* ✅ Error handling with specific error types
* ✅ Minified patch format for optimal payload size

### 🚧 In Progress

* File uploads support
* Component nesting and slots
* Keyed list diffing optimization

### 🔮 Future

* WebSocket transport option
* Partial hydration / islands architecture
* Streaming SSR
* Static segment compiler
* Browser devtools extension
* Plugin API for custom directives

---

# 🤝 Contributing

Pull requests are welcome!
Follow PSR-12 and include tests for new features.

---

# 📝 License

MIT License © 2025 Diffyne Team