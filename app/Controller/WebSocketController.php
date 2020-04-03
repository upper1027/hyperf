<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{

    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        $redis = di()->get(\Redis::class);

        $groupId = $redis->get('fd_group:'.$frame->fd);

        $redis->sAdd('group_order:'.$groupId, $frame->data);
        $groupMemberFds = $redis->sMembers('group _fd:'.$groupId);
        $groupOrders = $redis->sMembers('group_order:'.$groupId);
        foreach ($groupMemberFds as $groupMemberFd) {
            $groupMemberFd = (int) $groupMemberFd;
            $server->push($groupMemberFd, json_encode($groupOrders));
        }

    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        $redis = di()->get(\Redis::class);
        $id = $redis->get('fd_id:'.$fd);
        $group = $redis->get('fd_group:'.$fd);
        $redis->del('fd_id:'.$fd);
        $redis->del('fd_group:'.$fd);
        $redis->sRem('group_fd:'.$group, $fd);
    }

    public function onOpen(WebSocketServer $server, Request $request): void
    {
        $data = $request->get;
        $redis = di()->get(\Redis::class);
        $redis->set('fd_id:'.$request->fd, $data['id']);
        $redis->set('fd_group:'.$request->fd, $data['group']);
        $redis->sAdd('group_fd:'.$data['group'], $request->fd);

        $groupOrders = $redis->sMembers('group_order:'.$data['group']);

        $server->push($request->fd, json_encode($groupOrders));
    }
}
