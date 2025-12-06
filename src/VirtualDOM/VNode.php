<?php

namespace Diffyne\VirtualDOM;

/**
 * Virtual Node representation of a DOM element.
 */
class VNode
{
    /**
     * Node type constants.
     */
    public const TYPE_ELEMENT = 'element';

    public const TYPE_TEXT = 'text';

    public const TYPE_COMMENT = 'comment';

    /**
     * The node type.
     */
    public string $type;

    /**
     * The tag name (for element nodes).
     */
    public ?string $tag = null;

    /**
     * The text content (for text nodes).
     */
    public ?string $text = null;

    /**
     * Node attributes.
     *
     * @var array<string, mixed>
     */
    public array $attributes = [];

    /**
     * Child nodes.
     *
     * @var array<int, VNode>
     */
    public array $children = [];

    /**
     * Unique node identifier for tracking.
     */
    public ?string $key = null;

    /**
     * Node path in the tree (for efficient updates).
     *
     * @var array<int, int>
     */
    public array $path = [];

    /**
     * Create a new VNode instance.
     *
     * @param array<string, mixed> $attributes
     * @param array<int, VNode> $children
     */
    public function __construct(
        string $type,
        ?string $tag = null,
        ?string $text = null,
        array $attributes = [],
        array $children = []
    ) {
        $this->type = $type;
        $this->tag = $tag;
        $this->text = $text;
        $this->attributes = $attributes;
        $this->children = $children;

        // Extract key from attributes if present
        if (isset($attributes['key'])) {
            $this->key = $attributes['key'];
        }
    }

    /**
     * Create an element node.
     *
     * @param array<string, mixed> $attributes
     * @param array<int, VNode> $children
     */
    public static function element(string $tag, array $attributes = [], array $children = []): self
    {
        return new self(self::TYPE_ELEMENT, $tag, null, $attributes, $children);
    }

    /**
     * Create a text node.
     */
    public static function text(string $text): self
    {
        return new self(self::TYPE_TEXT, null, $text);
    }

    /**
     * Create a comment node.
     */
    public static function comment(string $text): self
    {
        return new self(self::TYPE_COMMENT, null, $text);
    }

    /**
     * Check if this is an element node.
     */
    public function isElement(): bool
    {
        return $this->type === self::TYPE_ELEMENT;
    }

    /**
     * Check if this is a text node.
     */
    public function isText(): bool
    {
        return $this->type === self::TYPE_TEXT;
    }

    /**
     * Check if this is a comment node.
     */
    public function isComment(): bool
    {
        return $this->type === self::TYPE_COMMENT;
    }

    /**
     * Set the node's path in the tree.
     *
     * @param array<int, int> $path
     */
    public function setPath(array $path): void
    {
        $this->path = $path;

        // Recursively set paths for children
        foreach ($this->children as $index => $child) {
            $child->setPath([...$path, $index]);
        }
    }

    /**
     * Get a child node by index.
     */
    public function getChild(int $index): ?VNode
    {
        return $this->children[$index] ?? null;
    }

    /**
     * Add a child node.
     */
    public function appendChild(VNode $child): void
    {
        $this->children[] = $child;
        $child->setPath([...$this->path, count($this->children) - 1]);
    }

    /**
     * Get an attribute value.
     */
    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Set an attribute value.
     */
    public function setAttribute(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Remove an attribute.
     */
    public function removeAttribute(string $name): void
    {
        unset($this->attributes[$name]);
    }

    /**
     * Convert the node to an array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type,
            'path' => $this->path,
        ];

        if ($this->isElement()) {
            $data['tag'] = $this->tag;
            $data['attributes'] = $this->attributes;
            $data['children'] = array_map(fn ($child) => $child->toArray(), $this->children);
        } elseif ($this->isText()) {
            $data['text'] = $this->text;
        } elseif ($this->isComment()) {
            $data['text'] = $this->text;
        }

        if ($this->key !== null) {
            $data['key'] = $this->key;
        }

        return $data;
    }

    /**
     * Convert node to minimal client representation (compact keys, no paths).
     *
     * @return array<string, mixed>
     */
    public function toMinimal(): array
    {
        if ($this->isElement()) {
            $data = ['t' => $this->tag];

            if (! empty($this->attributes)) {
                $data['a'] = $this->attributes;
            }

            if (! empty($this->children)) {
                $data['c'] = array_map(fn ($child) => $child->toMinimal(), $this->children);
            }

            if ($this->key !== null) {
                $data['k'] = $this->key;
            }

            return $data;
        } elseif ($this->isText()) {
            return ['x' => $this->text];
        } elseif ($this->isComment()) {
            return ['m' => $this->text];
        }

        return [];
    }

    /**
     * Get a string representation of the node path.
     */
    public function getPathString(): string
    {
        return implode('.', $this->path);
    }

    /**
     * Clone the node deeply.
     */
    public function clone(): self
    {
        $cloned = new self(
            $this->type,
            $this->tag,
            $this->text,
            $this->attributes,
            []
        );

        $cloned->key = $this->key;
        $cloned->path = $this->path;

        foreach ($this->children as $child) {
            $cloned->children[] = $child->clone();
        }

        return $cloned;
    }
}
