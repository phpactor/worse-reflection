<?php

namespace Phpactor\WorseReflection\Core\Cache;

use Closure;
use Exception;
use Phpactor\WorseReflection\Core\Cache;

class TtlCache implements Cache
{
    /**
     * @var array<string, mixed>
     */
    private $cache = [];

    /**
     * @var array<string, Exception>
     */
    private $throws = [];

    /**
     * @var array<string, int>
     */
    private $expires = [];

    /**
     * @var int
     */
    private $lifetime;

    private $ticker = 0;

    /**
     * @var float $lifetime Lifetime in seconds
     */
    public function __construct(float $lifetime = 5.0)
    {
        $this->lifetime = $lifetime;
    }

    public function getOrSet(string $key, Closure $setter)
    {
        $now = microtime(true);

        if (isset($this->cache[$key]) && $this->expires[$key] > $now) {
            if (isset($this->throws[$key])) {
                throw $this->throws[$key];
            }

            return $this->cache[$key];
        }

        $this->purgeExpired($now);
        try {
            $this->cache[$key] = $setter();
        } catch (Exception $e) {
            $this->cache[$key] = true;
            $this->throws[$key] = $e;
        }
        $this->expires[$key] = microtime(true) + $this->lifetime;

        return $this->cache[$key];
    }

    public function purge(): void
    {
        $this->cache = [];
        $this->expires = [];
    }

    private function purgeExpired(string $now): void
    {
        foreach ($this->expires as $key => $expires) {
            if ($expires > $now) {
                continue;
            }

            unset($this->expires[$key]);
            unset($this->cache[$key]);
        }
    }
}
