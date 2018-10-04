<?php

namespace Expose;

include_once 'MockMongoCollection.php';

class QueueTest extends \PHPUnit\Framework\TestCase
{
    private $queue = null;

    /**
     * Get a mock of the Queue object that returns the given results
     * 
     * @param mixed $return Return data
     * @return Mocked object
     */
    public function getQueueMock($return)
    {
        $collection = new \Expose\MockMongoCollection($return);

        $mock = $this->getMock('\\Expose\\Queue\\Mongo', array('getCollection'));
        $mock->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($collection));
        
        return $mock;
    }

    /**
     * Test the setting of the adapter on object construction
     * 
     * @covers \Expose\Queue::__construct
     * @covers \Expose\Queue::getAdapter
     */
    public function testSetAdapterOnConstruct()
    {
        $adapter = new \stdClass();
        $adapter->foo = 'test';
        $queue = new \Expose\Queue\Mongo($adapter);

        $this->assertEquals(
            $queue->getAdapter(),
            $adapter
        );
    }

    /**
     * Test the getter/setter for the adapter of the queue
     * 
     * @covers \Expose\Queue::getAdapter
     * @covers \Expose\Queue::setAdapter
     */
    public function testGetSetAdapter()
    {
        $adapter = new \stdClass();
        $adapter->foo = 'test';
        
        $queue = new \Expose\Queue\Mongo();
        $queue->setAdapter($adapter);

        $this->assertEquals(
            $queue->getAdapter(),
            $adapter
        );
    }

    /**
     * Get the current set of pending records
     * 
     * @covers \Expose\Queue::getPending
     */
    public function testGetPendingRecords()
    {
        $result = array(
            array(
                '_id' => '12345',
                'data' => array(
                    'POST' => array('test' => 'foo')
                ),
                'remote_ip' => '127.0.0.1',
                'datetime' => time(),
                'processed' => false
            )
        );

        $queue = $this->getQueueMock($result);
        $results = $queue->getPending();
        
        // be sure they're all "pending"
        $pass = true;
        foreach ($results as $result) {
            if ($result['processed'] !== false) {
                $pass = false;
            }
        }

        $this->assertTrue($pass, 'Non-pending records found');
    }

    /**
     * Source: Stackoverflow answer
     * https://stackoverflow.com/a/46147201/5128251
     *
     * Returns a mock object for the specified class.
     *
     * This method is a temporary solution to provide backward compatibility for tests that are still using the old
     * (4.8) getMock() method.
     * We should update the code and remove this method but for now this is good enough.
     *
     *
     * @param string     $originalClassName       Name of the class to mock.
     * @param array|null $methods                 When provided, only methods whose names are in the array
     *                                            are replaced with a configurable test double. The behavior
     *                                            of the other methods is not changed.
     *                                            Providing null means that no methods will be replaced.
     * @param array      $arguments               Parameters to pass to the original class' constructor.
     * @param string     $mockClassName           Class name for the generated test double class.
     * @param bool       $callOriginalConstructor Can be used to disable the call to the original class' constructor.
     * @param bool       $callOriginalClone       Can be used to disable the call to the original class' clone constructor.
     * @param bool       $callAutoload            Can be used to disable __autoload() during the generation of the test double class.
     * @param bool       $cloneArguments
     * @param bool       $callOriginalMethods
     * @param object     $proxyTarget
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     *
     * @throws \Exception
     */
    public function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false, $callOriginalMethods = false, $proxyTarget = null)
    {
        $builder = $this->getMockBuilder($originalClassName);

        if (is_array($methods)) {
            $builder->setMethods($methods);
        }

        if (is_array($arguments)) {
            $builder->setConstructorArgs($arguments);
        }

        $callOriginalConstructor ? $builder->enableOriginalConstructor() : $builder->disableOriginalConstructor();
        $callOriginalClone ? $builder->enableOriginalClone() : $builder->disableOriginalClone();
        $callAutoload ? $builder->enableAutoload() : $builder->disableAutoload();
        $cloneArguments ? $builder->enableOriginalClone() : $builder->disableOriginalClone();
        $callOriginalMethods ? $builder->enableProxyingToOriginalMethods() : $builder->disableProxyingToOriginalMethods();

        if ($mockClassName) {
            $builder->setMockClassName($mockClassName);
        }

        if ($proxyTarget) {
            $builder->setProxyTarget($proxyTarget);
        }

        $mockObject = $builder->getMock();

        return $mockObject;
    }
}