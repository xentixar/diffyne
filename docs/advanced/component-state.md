# Component State

Understanding how Diffyne manages component state.

## State Properties

All public properties are part of component state:

```php
class Counter extends Component
{
    public int $count = 0;        // Part of state
    public string $name = '';     // Part of state
    protected int $secret = 42;   // NOT part of state
    private bool $flag = true;    // NOT part of state
}
```

**Rule:** Only `public` properties are reactive and synced with the client.

## State Lifecycle

### 1. Initial State

When component first renders:

```php
public function mount()
{
    $this->count = 0;
    $this->items = [];
}
```

State sent to browser:

```json
{
    "count": 0,
    "items": []
}
```

### 2. State Updates

When user interacts:

```
Browser → Server: {method: 'increment', state: {count: 0}}
Server updates state: $this->count = 1
Server → Browser: {patches: [...], state: {count: 1}}
```

### 3. State Hydration

On each request, state is restored:

```php
// Browser sends state
{
    "count": 5,
    "items": ["a", "b", "c"]
}

// Server hydrates component
$component->count = 5;
$component->items = ["a", "b", "c"];
```

## Type Safety

Use type hints for safety:

```php
public string $name = '';
public int $age = 0;
public bool $active = false;
public array $items = [];
public ?User $user = null;
```

Diffyne handles type-safe restoration, converting null values appropriately:
- `string` → `''`
- `int` → `0`
- `bool` → `false`
- `array` → `[]`

## Computed Properties

Use methods for computed values:

```php
class TodoList extends Component
{
    public array $todos = [];
    
    public function getCompletedCount()
    {
        return count(array_filter($this->todos, fn($t) => $t['completed']));
    }
    
    public function getRemainingCount()
    {
        return count($this->todos) - $this->getCompletedCount();
    }
}
```

Use in views:

```blade
<p>{{ $this->getCompletedCount() }} completed</p>
<p>{{ $this->getRemainingCount() }} remaining</p>
```

## Resetting State

### Reset All Properties

```php
public function clearForm()
{
    $this->reset();
}
```

### Reset Specific Properties

```php
public function clearName()
{
    $this->reset('name');
}

public function clearForm()
{
    $this->reset('name', 'email', 'message');
}
```

## State Persistence

State exists only during request cycle. To persist across page loads:

### Session Storage

```php
public function dehydrate()
{
    session()->put('component.data', $this->data);
}

public function hydrate()
{
    $this->data = session()->get('component.data', []);
}
```

### Database Storage

```php
public function mount()
{
    $saved = UserPreference::where('user_id', auth()->id())
        ->where('key', 'filter_settings')
        ->first();
    
    if ($saved) {
        $this->filters = json_decode($saved->value, true);
    }
}

public function saveFilters()
{
    UserPreference::updateOrCreate(
        ['user_id' => auth()->id(), 'key' => 'filter_settings'],
        ['value' => json_encode($this->filters)]
    );
}
```

## Complex State

### Nested Arrays

```php
public array $user = [
    'name' => '',
    'email' => '',
    'address' => [
        'street' => '',
        'city' => '',
        'zip' => '',
    ],
];

public function updateAddress($field, $value)
{
    $this->user['address'][$field] = $value;
}
```

### Collections

Convert to arrays for state:

```php
public array $users = [];

public function mount()
{
    $this->users = User::all()->toArray();
}
```

## Best Practices

### 1. Use Type Hints

```php
// Good
public string $name = '';
public int $count = 0;

// Avoid
public $name;
public $count;
```

### 2. Initialize Arrays

```php
// Good
public array $items = [];

// Avoid
public $items; // undefined
```

### 3. Don't Store Models Directly

```php
// Avoid - models can't be serialized
public User $user;

// Good - store ID and reload
public int $userId;
public function hydrate()
{
    $this->user = User::find($this->userId);
}
```

### 4. Use Computed Properties

```php
// Good - computed
public function getTotalPrice()
{
    return array_sum(array_column($this->items, 'price'));
}

// Avoid - storing computed value
public float $totalPrice;
```

## Next Steps

- [Lifecycle Hooks](lifecycle-hooks.md) - Component lifecycle
- [Virtual DOM](virtual-dom.md) - How state changes update DOM
- [Performance](performance.md) - State optimization
