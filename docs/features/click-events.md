# Click Events

Handle user interactions with the `diffyne:click` directive.

## Basic Usage

Call a component method when an element is clicked:

```blade
<button diffyne:click="save">Save</button>
```

In your component:

```php
public function save()
{
    // Handle save logic
    $this->message = 'Saved successfully!';
}
```

## Passing Parameters

### Simple Parameters

```blade
<button diffyne:click="delete({{ $id }})">Delete</button>
<button diffyne:click="setStatus('active')">Activate</button>
<button diffyne:click="calculate(10, 20)">Calculate</button>
```

Component methods:

```php
public function delete($id)
{
    $this->items = array_filter($this->items, fn($item) => $item['id'] !== $id);
}

public function setStatus($status)
{
    $this->status = $status;
}

public function calculate($a, $b)
{
    $this->result = $a + $b;
}
```

### Passing Property Values

```blade
<button diffyne:click="setCategory('{{ $category }}')">
    {{ $category }}
</button>
```

### Multiple Parameters

```blade
@foreach($items as $index => $item)
    <button diffyne:click="updateItem({{ $index }}, '{{ $item['status'] }}')">
        Update
    </button>
@endforeach
```

## Event Handling

The `diffyne:click` directive automatically handles the click event and sends a request to the server. No event modifiers like `.prevent` or `.stop` are supported - handle event behavior in your component methods if needed.
    Click me
</a>
```

## Loading States

Show visual feedback during requests:

```blade
<button 
    diffyne:click="save"
    diffyne:loading.class="opacity-50 cursor-not-allowed">
    Save
</button>

<button diffyne:click="delete">
    <span diffyne:loading.remove>Delete</span>
    <span diffyne:loading>Deleting...</span>
</button>
```

## Conditional Clicks

Use Blade conditionals to control behavior:

```blade
@if($canEdit)
    <button diffyne:click="edit">Edit</button>
@endif

<button 
    diffyne:click="save"
    @if(!$isValid) disabled @endif>
    Save
</button>
```

## Examples

### Delete with Confirmation

```blade
<button 
    diffyne:click="delete"
    onclick="return confirm('Are you sure?')">
    Delete
</button>
```

Component:

```php
public function delete()
{
    // Will only run if user confirms
    DB::table('items')->where('id', $this->id)->delete();
    $this->deleted = true;
}
```

### Toggle State

```blade
<button diffyne:click="toggle">
    {{ $isActive ? 'Disable' : 'Enable' }}
</button>
```

Component:

```php
public bool $isActive = false;

public function toggle()
{
    $this->isActive = !$this->isActive;
}
```

### List Actions

```blade
<ul>
    @foreach($items as $index => $item)
        <li>
            {{ $item['name'] }}
            <button diffyne:click="edit({{ $index }})">Edit</button>
            <button diffyne:click="remove({{ $index }})">Remove</button>
        </li>
    @endforeach
</ul>
```

Component:

```php
public array $items = [];
public int $editingIndex = -1;

public function edit($index)
{
    $this->editingIndex = $index;
}

public function remove($index)
{
    unset($this->items[$index]);
    $this->items = array_values($this->items);
}
```

### Increment/Decrement

```blade
<div>
    <button diffyne:click="decrement">-</button>
    <span class="mx-4 text-2xl">{{ $count }}</span>
    <button diffyne:click="increment">+</button>
</div>
```

Component:

```php
public int $count = 0;

public function increment()
{
    $this->count++;
}

public function decrement()
{
    if ($this->count > 0) {
        $this->count--;
    }
}
```

### Pagination

```blade
<div>
    <button 
        diffyne:click="previousPage"
        @if($page === 1) disabled @endif>
        Previous
    </button>
    
    <span>Page {{ $page }}</span>
    
    <button 
        diffyne:click="nextPage"
        @if($page === $totalPages) disabled @endif>
        Next
    </button>
</div>
```

Component:

```php
public int $page = 1;
public int $totalPages = 10;

public function nextPage()
{
    if ($this->page < $this->totalPages) {
        $this->page++;
    }
}

public function previousPage()
{
    if ($this->page > 1) {
        $this->page--;
    }
}
```

## Best Practices

### 1. Use Descriptive Method Names

```blade
{{-- Good --}}
<button diffyne:click="saveUserProfile">Save</button>
<button diffyne:click="deletePost">Delete</button>

{{-- Avoid --}}
<button diffyne:click="action1">Save</button>
<button diffyne:click="doIt">Delete</button>
```

### 2. Validate on Server

Never trust client-side validation alone:

```php
public function delete($id)
{
    // Validate user has permission
    if (!auth()->user()->can('delete', $this->item)) {
        $this->addError('general', 'Unauthorized');
        return;
    }
    
    // Proceed with deletion
    $this->item->delete();
}
```

### 3. Provide Feedback

Always give user feedback:

```blade
<button diffyne:click="save">
    Save
    <span diffyne:loading>Saving...</span>
</button>

@if($saved)
    <div class="success-message">Saved successfully!</div>
@endif
```

### 4. Handle Errors Gracefully

```php
public function save()
{
    try {
        // Save logic
        $this->message = 'Saved successfully!';
    } catch (\Exception $e) {
        $this->addError('general', 'Failed to save. Please try again.');
    }
}
```

## Performance Tips

1. **Avoid unnecessary clicks**: Don't call methods that don't change state
2. **Use debouncing**: For rapid clicks, add delay on frontend
3. **Optimize methods**: Keep click handlers fast and efficient
4. **Loading states**: Always show loading feedback for slow operations

## Next Steps

- [Data Binding](data-binding.md) - Two-way data sync
- [Forms](forms.md) - Form handling
- [Loading States](loading-states.md) - Better UX during requests
- [Examples](../examples/) - More real-world examples
