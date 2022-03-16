<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shy\Exception;

use Throwable;
use ParseError;
use TypeError;
use ErrorException;

/**
 * Fatal Throwable Error.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class FatalThrowableError extends FatalErrorException
{
    private $originalClassName;

    /**
     * fatalThrowableError constructor.
     *
     * @param Throwable $e
     * @throws \ReflectionException
     */
    public function __construct(Throwable $e)
    {
        $this->originalClassName = get_class($e);

        if ($e instanceof ParseError) {
            $severity = E_PARSE;
        } elseif ($e instanceof TypeError) {
            $severity = E_RECOVERABLE_ERROR;
        } else {
            $severity = E_ERROR;
        }

        ErrorException::__construct(
            $e->getMessage(),
            $e->getCode(),
            $severity,
            $e->getFile(),
            $e->getLine(),
            $e->getPrevious()
        );

        $this->setTrace($e->getTrace());
    }

    public function getOriginalClassName(): string
    {
        return $this->originalClassName;
    }
}
