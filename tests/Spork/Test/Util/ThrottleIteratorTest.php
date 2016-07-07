<?php

/*
 * This file is part of Spork, an OpenSky project.
 *
 * (c) OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EdwardStock\Spork\Test\Util;

use EdwardStock\Spork\Util\ThrottleIterator;

class ThrottleIteratorTest extends \PHPUnit_Framework_TestCase
{
    private $iterator;

    public function testIteration()
    {
        iterator_to_array($this->iterator);
        $this->assertEquals([1, 2, 4], $this->iterator->sleeps);
    }

    protected function setUp()
    {
        $this->iterator = new ThrottleIteratorStub(array(1, 2, 3, 4, 5), 3);
        $this->iterator->loads = array(4, 4, 4, 1, 1);
    }

    protected function tearDown()
    {
        unset($this->iterator);
    }
}

class ThrottleIteratorStub extends ThrottleIterator
{
    public $loads = array();
    public $sleeps = array();

    protected function getLoad()
    {
        return (integer) array_shift($this->loads);
    }

    protected function sleep($period)
    {
        $this->sleeps[] = $period;
    }
}
