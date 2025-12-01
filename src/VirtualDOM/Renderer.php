<?php

namespace Diffyne\VirtualDOM;

use Diffyne\Component;
use Illuminate\Support\Facades\View;

/**
 * Renderer for Diffyne components that generates Virtual DOM and patches.
 */
class Renderer
{
    protected HTMLParser $parser;
    protected DiffEngine $diffEngine;

    /**
     * Component Virtual DOM snapshots.
     */
    protected array $snapshots = [];

    public function __construct()
    {
        $this->parser = new HTMLParser();
        $this->diffEngine = new DiffEngine();
    }

    /**
     * Render a component and return initial HTML with metadata.
     */
    public function renderInitial(Component $component): array
    {
        $html = $this->renderComponentView($component);
        $vdom = $this->parser->parse($html);
        
        // Store snapshot for future diffs
        $this->snapshots[$component->id] = $vdom;

        return [
            'id' => $component->id,
            'html' => $html,
            'state' => $component->getState(),
            'fingerprint' => $component->calculateFingerprint(),
        ];
    }

    /**
     * Re-render a component and generate patches.
     */
    public function renderUpdate(Component $component, ?string $previousHtml = null): array
    {
        $html = $this->renderComponentView($component);
        $newVdom = $this->parser->parse($html);

        // Parse the previous HTML if provided
        $oldVdom = null;
        if ($previousHtml) {
            $oldVdom = $this->parser->parse($previousHtml);
        }

        // Generate patches
        $patches = $this->diffEngine->diff($oldVdom, $newVdom);
        $patches = $this->diffEngine->optimizePatches($patches);

        return [
            'id' => $component->id,
            'patches' => $patches,
            'html' => $html,
            'state' => $component->getState(),
            'fingerprint' => $component->calculateFingerprint(),
        ];
    }

    /**
     * Render the component's view to HTML.
     */
    protected function renderComponentView(Component $component): string
    {
        $view = $component->render();

        if (is_string($view)) {
            return $view;
        }

        if ($view instanceof \Illuminate\View\View) {
            return $view->with($component->getState())->render();
        }

        throw new \InvalidArgumentException('Component render() must return a string or View instance.');
    }

    /**
     * Get stored snapshot for a component.
     */
    public function getSnapshot(string $componentId): ?VNode
    {
        return $this->snapshots[$componentId] ?? null;
    }

    /**
     * Clear snapshot for a component.
     */
    public function clearSnapshot(string $componentId): void
    {
        unset($this->snapshots[$componentId]);
    }

    /**
     * Clear all snapshots.
     */
    public function clearAllSnapshots(): void
    {
        $this->snapshots = [];
    }
}
