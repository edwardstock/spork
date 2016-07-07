<?php

/*
 * This file is part of Spork, an OpenSky project.
 *
 * (c) OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EdwardStock\Spork;

use EdwardStock\Spork\Deferred\Deferred;
use EdwardStock\Spork\Deferred\DeferredInterface;
use EdwardStock\Spork\Exception\ForkException;
use EdwardStock\Spork\Exception\ProcessControlException;
use EdwardStock\Spork\Util\ExitMessage;

class Fork implements DeferredInterface
{
    private $defer;
    private $pid;
    private $shm;
    private $debug;
    private $name;
    private $status;

    /**
     * @var ExitMessage
     */
    private $message;

    public function __construct($pid, SharedMemory $shm, $debug = false)
    {
        $this->defer = new Deferred();
        $this->pid   = $pid;
        $this->shm   = $shm;
        $this->debug = $debug;
        $this->name  = '<anonymous>';
    }

    public function always($always)
    {
        $this->defer->always($always);

        return $this;
    }

    public function done($done)
    {
        $this->defer->done($done);

        return $this;
    }

    public function fail($fail)
    {
        $this->defer->fail($fail);

        return $this;
    }

    public function getError()
    {
        if ($this->message) {
            return $this->message->getError();
        }
    }

    public function getExitStatus()
    {
        if (null !== $this->status) {
            return pcntl_wexitstatus($this->status);
        }
    }

    public function getOutput()
    {
        if ($this->message) {
            return $this->message->getOutput();
        }
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function getResult()
    {
        if ($this->message) {
            return $this->message->getResult();
        }
    }

    public function getState()
    {
        return $this->defer->getState();
    }

    public function getStopSignal()
    {
        if (null !== $this->status) {
            return pcntl_wstopsig($this->status);
        }
    }

    public function getTermSignal()
    {
        if (null !== $this->status) {
            return pcntl_wtermsig($this->status);
        }
    }

    public function isExited()
    {
        return null !== $this->status && pcntl_wifexited($this->status);
    }

    public function isSignaled()
    {
        return null !== $this->status && pcntl_wifsignaled($this->status);
    }

    public function isStopped()
    {
        return null !== $this->status && pcntl_wifstopped($this->status);
    }

    public function isSuccessful()
    {
        return 0 === $this->getExitStatus();
    }

    public function kill($signal = SIGINT)
    {
        if (false === $this->shm->signal($signal)) {
            throw new ProcessControlException('Unable to send signal');
        }

        return $this;
    }

    public function notify()
    {
        $args = func_get_args();
        array_unshift($args, $this);

        call_user_func_array([$this->defer, 'notify'], $args);

        return $this;
    }

	/**
     * Processes a status value retrieved while waiting for this fork to exit.
     * @param string $status
     */
    public function processWaitStatus($status)
    {
        if ($this->isExited()) {
            throw new \LogicException('Cannot set status on an exited fork');
        }

        $this->status = $status;

        if ($this->isExited()) {
            $this->receive();

            $this->isSuccessful() ? $this->resolve() : $this->reject();

            if ($this->debug && (!$this->isSuccessful() || $this->getError())) {
                throw new ForkException($this->name, $this->pid, $this->getError());
            }
        }
    }

    public function progress($progress)
    {
        $this->defer->progress($progress);

        return $this;
    }

    public function receive()
    {
        $messages = [];

        foreach ($this->shm->receive() as $message) {
            if ($message instanceof ExitMessage) {
                $this->message = $message;
            } else {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    public function reject()
    {
        $args = func_get_args();
        array_unshift($args, $this);

        call_user_func_array([$this->defer, 'reject'], $args);

        return $this;
    }

    public function resolve()
    {
        $args = func_get_args();
        array_unshift($args, $this);

        call_user_func_array(array($this->defer, 'resolve'), $args);

        return $this;
    }

	/**
	 * Assign a name to the current fork (useful for debugging).
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function then($done, $fail = null)
    {
        $this->defer->then($done, $fail);

        return $this;
    }

    public function wait($hang = true)
    {
        if ($this->isExited()) {
            return $this;
        }

        if (-1 === $pid = pcntl_waitpid($this->pid, $status, ($hang ? 0 : WNOHANG) | WUNTRACED)) {
            throw new ProcessControlException('Error while waiting for process ' . $this->pid);
        }

        if ($this->pid === $pid) {
            $this->processWaitStatus($status);
        }

        return $this;
    }
}
