<?php

namespace Bolt\Extension\JKazimir\EditLocking;

use Hoa\Event\Bucket;
use Hoa\Event\Source;
use Hoa\Websocket\Connection;
use Hoa\Websocket\Node;
use Hoa\Websocket\Server;

class LockingServerApp
{
    /**
     * @var \SplObjectStorage
     */
    private $clients;

    /**
     * @var LockPool
     */
    private $locks;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->locks = new LockPool;
    }

    /**
     * Register event handlers to the websocket server
     *
     * @param Server $ws
     */
    public function registerToServer(Server $ws)
    {
        $ws->on('open', [$this, 'onOpen']);
        $ws->on('close', [$this, 'onClose']);
        $ws->on('message', [$this, 'onMessage']);
        $ws->on('error', [$this, 'onError']);
    }

    /**
     * Handle websocket open event
     *
     * @param Bucket $bucket
     */
    public function onOpen(Bucket $bucket)
    {
        /** @var Node $node */
        $node = $bucket->getSource()->getConnection()->getCurrentNode();

        $this->clients->attach($node);

    }

    /**
     * Handle websocket close event
     *
     * @param Bucket $bucket
     */
    public function onClose(Bucket $bucket)
    {
        /** @var Node $node */
        $node = $bucket->getSource()->getConnection()->getCurrentNode();

        $this->locks->removeAllByConnection($node);
        $this->clients->detach($node);
    }

    /**
     * Handle websocket error event
     *
     * @param Bucket $bucket
     */
    public function onError(Bucket $bucket)
    {
        $data = $bucket->getData();

        echo $data['exception']->getMessage();
    }

    /**
     * Handle websocket message event
     *
     * @param Bucket $bucket
     */
    public function onMessage(Bucket $bucket)
    {
        $data = json_decode($bucket->getData()['message'], true);

        if (!$data) {
            return;
        }

        if ($data['type'] ?? '' === 'requestEditLock') {
            $this->onRequestEditLock($bucket->getSource(), $data);
        }
    }

    /**
     * Handle request edit lock message type
     *
     * @param Source|Connection $conn
     * @param array             $data
     */
    private function onRequestEditLock(Source $conn, array $data)
    {
        if (!isset($data['contenttype']) || !isset($data['id'])) {
            return;
        }

        $key = $data['contenttype'] . '/' . $data['id'];

        if ($this->locks->has($key)) {
            $lock = $this->locks->get($key);

            $conn->send(json_encode([
                'type' => 'editLockDenied',
                'time' => $lock->getTime()->format('Y-m-d H:i:s'),
            ]));

            return;
        }

        $this->locks->set($key, new Lock($conn->getConnection()->getCurrentNode()));

        $conn->send(json_encode([
            'type' => 'editLockGranted',
        ]));
    }
}
