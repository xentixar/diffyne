<?php

namespace Diffyne\Exceptions;

use Exception;

class RedirectException extends Exception
{
    /**
     * @var array<string, mixed>
     */
    protected array $redirectData;

    /**
     * @param array<string, mixed> $redirectData
     */
    public function __construct(array $redirectData)
    {
        parent::__construct('Diffyne Redirect');
        $this->redirectData = $redirectData;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRedirectData(): array
    {
        return $this->redirectData;
    }
}
