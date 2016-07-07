<?php

/*
 * This file is part of Spork, an OpenSky project.
 *
 * (c) OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EdwardStock\Spork\Deferred;

interface DeferredInterface extends PromiseInterface
{
	/**
	 * Notifies the promise of progress.
	 * @return DeferredInterface The current promise
	 * @internal param mixed $args Any arguments will be passed along to the callbacks
	 *
	 */
    function notify();

	/**
	 * Marks the current promise as successful.
	 *
	 * Calls "always" callbacks first, followed by "done" callbacks.
	 * @return DeferredInterface The current promise
	 * @internal param mixed $args Any arguments will be passed along to the callbacks
	 *
	 */
    function resolve();

	/**
	 * Marks the current promise as failed.
	 *
	 * Calls "always" callbacks first, followed by "fail" callbacks.
	 * @return DeferredInterface The current promise
	 * @internal param mixed $args Any arguments will be passed along to the callbacks
	 *
	 */
    function reject();
}
