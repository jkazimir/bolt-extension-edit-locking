<?php

namespace Bolt\Extension\JKazimir\EditLocking;

use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class LockingServer implements MessageComponentInterface
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
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->locks->removeAllByConnection($conn);
        $this->clients->detach($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        print_r($e);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $conn, MessageInterface $msg)
    {
        $data = json_decode($msg->getPayload(), true);

        if (!$data) {
            return;
        }

        if ($data['type'] ?? '' === 'requestEditLock') {
            $this->onRequestEditLock($conn, $data);
        }
    }

    /**
     * Handle request edit lock message type
     *
     * @param ConnectionInterface $conn
     * @param array               $data
     */
    private function onRequestEditLock(ConnectionInterface $conn, array $data)
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

        $this->locks->set($key, new Lock($conn));

        $conn->send(json_encode([
            'type' => 'editLockGranted',
        ]));
    }
}
