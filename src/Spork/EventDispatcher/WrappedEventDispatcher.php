<?php

/*
 * This file is part of Spork, an OpenSky project.
 *
 * (c) OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(ticks=1);

namespace EdwardStock\Spork\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as BaseEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WrappedEventDispatcher implements EventDispatcherInterface
{
    private $delegate;

    public function __construct(BaseEventDispatcherInterface $delegate)
    {
        $this->delegate = $delegate;
    }

	public function addListener($eventName, $listener, $priority = 0)
    {
	    $this->delegate->addListener($eventName, $listener, $priority);
    }

    public function addSignalListener($signal, $callable, $priority = 0)
    {
        $this->delegate->addListener('spork.signal.'.$signal, $callable, $priority);
        pcntl_signal($signal, array($this, 'dispatchSignal'));
    }

	public function addSubscriber(EventSubscriberInterface $subscriber)
    {
	    $this->delegate->addSubscriber($subscriber);
    }

    public function dispatch($eventName, Event $event = null)
    {
        return $this->delegate->dispatch($eventName, $event);
    }

	public function dispatchSignal($signal)
    {
	    $this->delegate->dispatch('spork.signal.' . $signal);
    }

	/**
	 * Gets the listener priority for a specific event.
	 *
	 * Returns null if the event or the listener does not exist.
	 *
	 * @param string $eventName The name of the event
	 * @param callable $listener The listener
	 *
	 * @return int|null The event listener priority
	 */
	public function getListenerPriority($eventName, $listener)
	{
		return null;
	}

	public function getListeners($eventName = null)
    {
	    return $this->delegate->getListeners($eventName);
    }

	public function hasListeners($eventName = null)
    {
	    return $this->delegate->hasListeners($eventName);
    }

	public function removeListener($eventName, $listener)
    {
	    $this->delegate->removeListener($eventName, $listener);
    }

	public function removeSignalListener($signal, $callable)
    {
	    $this->delegate->removeListener('spork.signal.' . $signal, $callable);
    }

	public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
	    $this->delegate->removeSubscriber($subscriber);
    }
}
