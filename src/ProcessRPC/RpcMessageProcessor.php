<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/9
 * Time: 10:19
 */

namespace ESD\Plugins\ProcessRPC;

use ESD\Core\Message\Message;
use ESD\Core\Message\MessageProcessor;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;

class RpcMessageProcessor extends MessageProcessor
{
    use GetLogger;
    const type = "@processRPC";

    public function __construct()
    {
        parent::__construct(self::type);
    }

    /**
     * 处理消息
     * @param Message $message
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function handler(Message $message): bool
    {
        if ($message instanceof ProcessRPCCallMessage) {
            $rpcCallData = $message->getProcessRPCCallData();
            $handle = Server::$instance->getContainer()->get($rpcCallData->getClassName());
            $result = null;
            $errorClass = null;
            $errorCode = null;
            $errorMessage = null;
            try {
                $result = call_user_func_array([$handle, $rpcCallData->getName()], $rpcCallData->getArguments());
            } catch (\Throwable $e) {
                $errorClass = get_class($e);
                $errorCode = $e->getCode();
                $errorMessage = $e->getMessage();
                $this->error($e);
            }
            if(!$rpcCallData->isOneway()) {
                Server::$instance->getProcessManager()->getCurrentProcess()->sendMessage(
                    new ProcessRPCResultMessage($rpcCallData->getToken(), $result, $errorClass, $errorCode, $errorMessage),
                    Server::$instance->getProcessManager()->getProcessFromId($message->getFromProcessId())
                );
            }
            return true;
        } else if ($message instanceof ProcessRPCResultMessage) {
            $rpcResultData = $message->getProcessRPCResultData();
            RpcManager::callChannel($rpcResultData->getToken(), $rpcResultData);
            return true;
        }
        return false;
    }
}