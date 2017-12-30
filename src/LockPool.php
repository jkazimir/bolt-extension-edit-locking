<?php

namespace Bolt\Extension\JKazimir\EditLocking;

use Ratchet\ConnectionInterface;

class LockPool
{
    /**
     * @var Lock[]
     */
    private $pool = [];

    /**
     * Set a lock
     *
     * @param string $key
     * @param Lock   $lock
     */
    public function set($key, Lock $lock)
    {
        $this->pool[$key] = $lock;
    }

    /**
     * Check if a key is locked
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->pool[$key]);
    }

    /**
     * Find a lock by key
     *
     * @param string $key
     *
     * @return Lock
     */
    public function get($key)
    {
        return $this->pool[$key];
    }

    /**
     * Remove a lock by key
     *
     * @param string $key
     */
    public function remove($key)
    {
        unset($this->pool[$key]);
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function removeAllByConnection(ConnectionInterface $conn)
    {
        foreach ($this->pool as $key => $lock) {
            if ($lock->getConnection() === $conn) {
                $this->remove($key);
            }
        }
    }
}
