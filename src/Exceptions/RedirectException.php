<?php

namespace Diffyne\Exceptions;

use Exception;

class RedirectException extends Exception
{
    protected array $redirectData;

    public function __construct(array $redirectData)
    {
        parent::__construct('Diffyne Redirect');
        $this->redirectData = $redirectData;
    }

    public function getRedirectData(): array
    {
        return $this->redirectData;
    }
}
