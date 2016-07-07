<?php

/*
 * This file is part of Spork, an OpenSky project.
 *
 * (c) OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EdwardStock\Spork\Test;

use EdwardStock\Spork\ProcessManager;

class SignalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessManager
     */
    private $manager;

    public function testSignalParent()
    {
        $signaled = false;
        $this->manager->addListener(SIGUSR1, function() use(& $signaled) {
            $signaled = true;
        });

        $this->manager->fork(function($sharedMem) {
            $sharedMem->signal(SIGUSR1);
        });

        $this->manager->wait();

        $this->assertTrue($signaled);
    }

    protected function setUp()
    {
        $this->manager = new ProcessManager();
    }

    protected function tearDown()
    {
        $this->manager = null;
    }
}
