<?php

namespace Diffyne\Http\Controllers;

use Diffyne\State\ComponentHydrator;
use Diffyne\VirtualDOM\Renderer;
use Diffyne\VirtualDOM\PatchSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DiffyneController extends Controller
{
    protected ComponentHydrator $hydrator;
    protected Renderer $renderer;
    protected PatchSerializer $serializer;

    public function __construct(
        ComponentHydrator $hydrator,
        Renderer $renderer,
        PatchSerializer $serializer
    ) {
        $this->hydrator = $hydrator;
        $this->renderer = $renderer;
        $this->serializer = $serializer;
    }

    /**
     * Handle component updates.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type');
            $componentId = $request->input('componentId');
            $state = $request->input('state', []);
            $fingerprint = $request->input('fingerprint');
            $previousHtml = $request->input('previousHtml');

            // Validate request
            if (!$componentId || !$state) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid request',
                ], 400);
            }

            // Get component class from state or registry
            $componentClass = $this->resolveComponentClass($request);

            if (!$componentClass) {
                return response()->json([
                    'success' => false,
                    'error' => 'Component class not found',
                ], 404);
            }

            // Hydrate component from state
            $component = $this->hydrator->hydrate($componentClass, $state, $componentId);

            // Handle different request types
            switch ($type) {
                case 'call':
                    $method = $request->input('method');
                    $params = $request->input('params', []);
                    
                    if (!$method) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Method not specified',
                        ], 400);
                    }

                    $component->callMethod($method, $params);
                    break;

                case 'update':
                    $property = $request->input('property');
                    $value = $request->input('value');
                    
                    if (!$property) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Property not specified',
                        ], 400);
                    }

                    $component->updateProperty($property, $value);
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'error' => 'Invalid request type',
                    ], 400);
            }

            // Render updates and generate patches
            $response = $this->renderer->renderUpdate($component, $previousHtml);

            return response()->json($this->serializer->toResponse($response));

        } catch (\Exception $e) {
            if (config('diffyne.debug', false)) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ], 500);
            }

            return response()->json([
                'success' => false,
                'error' => 'Server error',
            ], 500);
        }
    }

    /**
     * Resolve component class from request.
     */
    protected function resolveComponentClass(Request $request): ?string
    {
        // Try to get from request
        $componentClass = $request->input('componentClass');

        if ($componentClass && class_exists($componentClass)) {
            return $componentClass;
        }

        // Try to get from component name
        $componentName = $request->input('componentName');
        
        if ($componentName) {
            $defaultNamespace = config('diffyne.component_namespace', 'App\\Diffyne');
            $fullClass = $defaultNamespace . '\\' . $componentName;

            if (class_exists($fullClass)) {
                return $fullClass;
            }
        }

        return null;
    }

    /**
     * Health check endpoint.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'version' => '1.0.0',
            'transport' => config('diffyne.transport', 'ajax'),
        ]);
    }
}
