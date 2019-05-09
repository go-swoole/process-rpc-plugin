<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/9
 * Time: 10:25
 */

namespace GoSwoole\Plugins\ProcessRPC;


use GoSwoole\BaseServer\Server\Message\Message;

class ProcessRPCCallMessage extends Message
{
    /**
     * ProcessRPCCallMessage constructor.
     * @param string $className
     * @param string $name
     * @param array $arguments
     */
    public function __construct(string $className, string $name, array $arguments)
    {
        parent::__construct(RpcMessageProcessor::type, new ProcessRPCCallData($className, $name, $arguments));
    }

    public function getProcessRPCCallData(): ProcessRPCCallData
    {
        return $this->getData();
    }
}