<?php

namespace Bolt\Extension\JKazimir\EditLocking;

use Hoa\Websocket\Node;

class Lock
{
    /**
     * @var Node
     */
    private $conn;

    /**
     * @var \DateTime
     */
    private $time;

    /**
     * @param Node $conn
     */
    public function __construct(Node $conn)
    {
        $this->conn = $conn;
        $this->time = new \DateTime();
    }

    /**
     * Get the web socket connection
     *
     * @return Node
     */
    public function getConnection(): Node
    {
        return $this->conn;
    }

    /**
     * Get the lock time
     *
     * @return \DateTime
     */
    public function getTime(): \DateTime
    {
        return $this->time;
    }
}
