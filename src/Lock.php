<?php

namespace Bolt\Extension\JKazimir\EditLocking;

use Hoa\Event\Source;
use Hoa\Websocket\Connection;

class Lock
{
    /**
     * @var Source
     */
    private $conn;

    /**
     * @var \DateTime
     */
    private $time;

    /**
     * @param Source|Connection $conn
     */
    public function __construct(Source $conn)
    {
        $this->conn = $conn;
        $this->time = new \DateTime();
    }

    /**
     * Get the web socket connection
     *
     * @return Source
     */
    public function getConnection(): Source
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
