<?php

/*
 * This file is part of Spork, an OpenSky project.
 *
 * (c) OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EdwardStock\Spork\Batch\Strategy;

use EdwardStock\Spork\Exception\UnexpectedTypeException;

class CallbackStrategy extends AbstractStrategy
{
    private $callback;

    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new UnexpectedTypeException($callback, 'callable');
        }

        $this->callback = $callback;
    }

    public function createBatches($data)
    {
        return call_user_func($this->callback, $data);
    }
}
