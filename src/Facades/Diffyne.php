<?php

namespace Diffyne\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void component(string $alias, string $class)
 * @method static string mount(string $component, array $params = [])
 * @method static \Diffyne\VirtualDOM\Renderer getRenderer()
 * @method static \Diffyne\State\ComponentHydrator getHydrator()
 * @method static array getComponents()
 *
 * @see \Diffyne\DiffyneManager
 */
class Diffyne extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'diffyne';
    }
}
