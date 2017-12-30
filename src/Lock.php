<?php

namespace Bolt\Extension\JKazimir\EditLocking;

use Ratchet\ConnectionInterface;

class Lock
{
    /**
     * @var ConnectionInterface
     */
    private $conn;

    /**
     * @var \DateTime
     */
    private $time;

    /**
     * @param ConnectionInterface $conn
     */
    public function __construct(ConnectionInterface $conn)
    {
        $this->conn = $conn;
        $this->time = new \DateTime();
    }

    /**
     * Get the web socket connection
     *
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
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
