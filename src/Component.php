<?php

namespace Diffyne;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use TypeError;

abstract class Component
{
    /**
     * The component's unique identifier.
     */
    public string $id;

    /**
     * The component's data fingerprint for change detection.
     */
    protected string $fingerprint = '';

    /**
     * Component properties that should not be exposed to the client.
     */
    protected array $hidden = [];

    /**
     * Component properties that should be tracked for changes.
     */
    protected array $tracked = [];

    /**
     * Previous state snapshot for diff calculation.
     */
    private array $previousState = [];

    /**
     * Validation error bag.
     */
    protected MessageBag $errorBag;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->id = $this->generateId();
        $this->errorBag = new MessageBag();
        $this->initializeProperties();
    }

    /**
     * Generate a unique component ID.
     */
    protected function generateId(): string
    {
        return 'diffyne-' . Str::random(16);
    }

    /**
     * Initialize component properties.
     */
    protected function initializeProperties(): void
    {
        // Track all public properties by default
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            if (!$property->isStatic() && $property->getName() !== 'id') {
                $this->tracked[] = $property->getName();
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    abstract public function render(): View|string;

    /**
     * Get the component's view name.
     */
    protected function view(): string
    {
        $class = str_replace('\\', '/', static::class);
        $name = Str::kebab(class_basename($class));
        
        return "diffyne.{$name}";
    }

    /**
     * Lifecycle hook: Called before the component is rendered for the first time.
     */
    public function mount(): void
    {
        //
    }

    /**
     * Lifecycle hook: Called after the component is hydrated on the client.
     */
    public function hydrate(): void
    {
        //
    }

    /**
     * Lifecycle hook: Called before a property is updated.
     */
    public function updating(string $property, mixed $value): void
    {
        //
    }

    /**
     * Lifecycle hook: Called after a property is updated.
     */
    public function updated(string $property): void
    {
        //
    }

    /**
     * Lifecycle hook: Called before dehydrating the component state.
     */
    public function dehydrate(): void
    {
        //
    }

    /**
     * Update a component property.
     */
    public function updateProperty(string $property, mixed $value): void
    {
        if (!property_exists($this, $property)) {
            throw new \InvalidArgumentException("Property [{$property}] does not exist on component.");
        }

        if (in_array($property, $this->hidden)) {
            throw new \InvalidArgumentException("Property [{$property}] is protected and cannot be updated.");
        }

        $this->updating($property, $value);
        
        $this->$property = $value;
        
        $this->updated($property);
    }

    /**
     * Call a component method.
     */
    public function callMethod(string $method, array $params = []): mixed
    {
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException("Method [{$method}] does not exist on component.");
        }

        if (str_starts_with($method, '__') || in_array($method, $this->getProtectedMethods())) {
            throw new \BadMethodCallException("Method [{$method}] cannot be called.");
        }

        return $this->$method(...$params);
    }

    /**
     * Get methods that cannot be called from the client.
     */
    protected function getProtectedMethods(): array
    {
        return [
            'mount',
            'hydrate',
            'updating',
            'updated',
            'dehydrate',
            'render',
            'toArray',
            'getState',
            'restoreState',
            'getChanges',
            'snapshot',
            'updateProperty',
            'callMethod',
            'validate',
            'validateOnly',
            'resetValidation',
            'resetErrorBag',
            'addError',
            'getErrorBag',
            'setErrorBag',
            'rules',
            'messages',
            'validationAttributes',
        ];
    }

    /**
     * Validate component properties.
     *
     * @param array|null $rules
     * @param array $messages
     * @param array $attributes
     * @return array
     * @throws ValidationException
     */
    protected function validate(?array $rules = null, array $messages = [], array $attributes = []): array
    {
        $rules = $rules ?? $this->rules();
        $messages = array_merge($this->messages(), $messages);
        $attributes = array_merge($this->validationAttributes(), $attributes);

        $validator = Validator::make(
            $this->getState(),
            $rules,
            $messages,
            $attributes
        );

        try {
            $validated = $validator->validate();
            $this->resetValidation();
            return $validated;
        } catch (ValidationException $e) {
            $this->errorBag = $e->validator->errors();
            throw $e;
        }
    }

    /**
     * Validate a single property.
     *
     * @param string $field
     * @return void
     */
    protected function validateOnly(string $field): void
    {
        $rules = $this->rules();
        
        if (!isset($rules[$field])) {
            return;
        }

        $validator = Validator::make(
            $this->getState(),
            [$field => $rules[$field]],
            $this->messages(),
            $this->validationAttributes()
        );

        if ($validator->fails()) {
            $this->errorBag->merge($validator->errors()->messages());
        } else {
            $this->errorBag->forget($field);
        }
    }

    /**
     * Reset validation for specific fields or all fields.
     *
     * @param string|array|null $field
     * @return void
     */
    protected function resetValidation($field = null): void
    {
        if ($field === null) {
            $this->errorBag = new MessageBag();
            return;
        }

        $fields = is_array($field) ? $field : [$field];
        
        foreach ($fields as $f) {
            $this->errorBag->forget($f);
        }
    }

    /**
     * Alias for resetValidation().
     *
     * @param string|array|null $field
     * @return void
     */
    protected function resetErrorBag($field = null): void
    {
        $this->resetValidation($field);
    }

    /**
     * Add a validation error.
     *
     * @param string $field
     * @param string $message
     * @return void
     */
    protected function addError(string $field, string $message): void
    {
        $this->errorBag->add($field, $message);
    }

    /**
     * Get the validation rules.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Get custom validation attributes.
     *
     * @return array
     */
    protected function validationAttributes(): array
    {
        return [];
    }

    /**
     * Get the error bag.
     *
     * @return MessageBag
     */
    public function getErrorBag(): MessageBag
    {
        return $this->errorBag;
    }

    /**
     * Set the error bag (for hydration).
     *
     * @param array $errors
     * @return void
     */
    public function setErrorBag(array $errors): void
    {
        $this->errorBag = new MessageBag($errors);
    }

    /**
     * Get the component's current state.
     */
    public function getState(): array
    {
        $state = [];

        foreach ($this->tracked as $property) {
            if (!in_array($property, $this->hidden) && property_exists($this, $property)) {
                $state[$property] = $this->$property;
            }
        }

        return $state;
    }

    /**
     * Restore component state from an array.
     */
    public function restoreState(array $state): void
    {
        foreach ($state as $property => $value) {
            if (property_exists($this, $property) && !in_array($property, $this->hidden)) {
                try {
                    $this->$property = $value;
                } catch (TypeError $e) {
                    $reflection = new ReflectionProperty($this, $property);
                    $type = $reflection->getType();
                    
                    if ($type && !$type->allowsNull() && $value === null) {
                        $typeName = $type instanceof ReflectionNamedType ? $type->getName() : 'mixed';
                        $this->$property = match($typeName) {
                            'string' => '',
                            'int' => 0,
                            'float' => 0.0,
                            'bool' => false,
                            'array' => [],
                            default => $value,
                        };
                    } else {
                        throw $e;
                    }
                }
            }
        }
    }

    /**
     * Take a snapshot of current state for diff comparison.
     */
    public function snapshot(): void
    {
        $this->previousState = $this->getState();
        $this->fingerprint = $this->calculateFingerprint();
    }

    /**
     * Get changes since last snapshot.
     */
    public function getChanges(): array
    {
        $current = $this->getState();
        $changes = [];

        foreach ($current as $key => $value) {
            if (!isset($this->previousState[$key]) || $this->previousState[$key] !== $value) {
                $changes[$key] = [
                    'old' => $this->previousState[$key] ?? null,
                    'new' => $value,
                ];
            }
        }

        return $changes;
    }

    /**
     * Calculate a fingerprint of the current state.
     */
    public function calculateFingerprint(): string
    {
        return md5(json_encode($this->getState()));
    }

    /**
     * Get the current fingerprint.
     */
    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    /**
     * Check if the component state has changed.
     */
    public function hasChanged(): bool
    {
        return $this->fingerprint !== $this->calculateFingerprint();
    }

    /**
     * Convert the component to an array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => static::class,
            'state' => $this->getState(),
            'fingerprint' => $this->fingerprint,
        ];
    }

    /**
     * Dynamically set a property.
     */
    public function __set(string $name, mixed $value): void
    {
        if (property_exists($this, $name)) {
            $this->updateProperty($name, $value);
        }
    }

    /**
     * Dynamically get a property.
     */
    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new \InvalidArgumentException("Property [{$name}] does not exist on component.");
    }
}
