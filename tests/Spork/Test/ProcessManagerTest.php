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

use EdwardStock\Spork\Fork;
use EdwardStock\Spork\ProcessManager;

class ProcessManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Process Manager object
     *
     * @var ProcessManager
     */
    private $manager;

    /**
     * Data provider for `testLargeBatchProcessing()`
     *
     * @return array
     */
    public function batchProvider()
    {
        return [
            [10],
            [1000],
            [6941],
            [6942],
            [6000],
            [10000],
            [20000],
        ];
    }

    public function testBatchProcessing()
    {
        $expected = range(100, 109);

        $fork = $this->manager->process($expected, function ($item) {
            return $item;
        });

        $this->manager->wait();

        $this->assertEquals($expected, $fork->getResult());
    }

    /**
     * Test batch processing with return values containing a newline character
     */
    public function testBatchProcessingWithNewlineReturnValues()
    {
        $range = range(100, 109);
        $expected = [
            0 => "SomeString\n100",
            1 => "SomeString\n101",
            2 => "SomeString\n102",
            3 => "SomeString\n103",
            4 => "SomeString\n104",
            5 => "SomeString\n105",
            6 => "SomeString\n106",
            7 => "SomeString\n107",
            8 => "SomeString\n108",
            9 => "SomeString\n109",
        ];

        $this->manager->setDebug(true);
        $fork = $this->manager->process($range, function ($item) {
            return "SomeString\n$item";
        });

        $this->manager->wait();

        $this->assertEquals($expected, $fork->getResult());
    }

    public function testDoneCallbacks()
    {
        $success = null;

        $fork = $this->manager->fork(function() {
            echo 'output';
            return 'result';
        })->done(function() use(& $success) {
            $success = true;
        })->fail(function() use(& $success) {
            $success = false;
        });

        $this->manager->wait();

        $this->assertTrue($success);
        $this->assertEquals('output', $fork->getOutput());
        $this->assertEquals('result', $fork->getResult());
    }

    public function testFailCallbacks()
    {
        $success = null;

        $fork = $this->manager->fork(function() {
            throw new \Exception('child error');
        })->done(function() use(& $success) {
            $success = true;
        })->fail(function() use(& $success) {
            $success = false;
        });

        $this->manager->wait();

        $this->assertFalse($success);
        $this->assertNotEmpty($fork->getError());
    }

    /**
     * Test large batch sizes
     *
     * @dataProvider batchProvider
     */
    public function testLargeBatchProcessing($rangeEnd)
    {
        $expected = array_fill(0, $rangeEnd, null);

        /** @var Fork $fork */
        $fork = $this->manager->process($expected, function($item) {
            return $item;
        });

        $this->manager->wait();

        $this->assertEquals($expected, $fork->getResult());
    }

    public function testObjectReturn()
    {
        $fork = $this->manager->fork(function() {
            return new Unserializable();
        });

        $this->manager->wait();

        $this->assertNull($fork->getResult());
        $this->assertFalse($fork->isSuccessful());
    }

    protected function setUp()
    {
        $this->manager = new ProcessManager();
    }

    protected function tearDown()
    {
        unset($this->manager);
    }
}

class Unserializable
{
    public function __sleep()
    {
        throw new \Exception('Hey, don\'t serialize me!');
    }
}
