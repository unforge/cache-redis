<?php
/**
 * This file is part of the Cache library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ronam Unstirred (unforge.coder@gmail.com)
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Unforge\Toolkit\Cache;

use Unforge\Toolkit\Arr;
use Unforge\Toolkit\Logger; // todo
use Unforge\Abstraction\Cache\AbstractCache;

/**
 * Class Redis
 *
 * @package Unforge\Toolkit\Cache
 */
class Redis extends AbstractCache
{
    /**
     * @var \Redis
     */
    private $client;

    /**
     * @param array $config
     *
     * @throws \Exception
     */
    public function connect(array $config)
    {
        $host           = Arr::getString($config, 'host');
        $port           = Arr::getInt($config, 'port', 6379);
        $timeout        = Arr::getFloat($config, 'timeout', 0.0);
        $retry_interval = Arr::getInt($config, 'retry_interval', 0);

        if (!$host) {
            throw new \Exception("Host is required");
        }

        try {
            $this->client = new \Redis();
            $this->client->connect($host, $port, $timeout, null, $retry_interval);
            $this->client->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @param string $prefix
     *
     * @return bool
     */
    public function set(string $key, string $value, string $prefix = 'cache'): bool
    {
        $key = $this->prepareKeyToString($prefix, $key);

        try {
            return $this->client->set($key, $value);
        } catch (\Exception $e) {
            // todo Logger
            return false;
        }
    }

    /**
     * @param string $key
     * @param string $prefix
     *
     * @return string
     */
    public function get(string $key, string $prefix = 'cache'): string
    {
        $key = $this->prepareKeyToString($prefix, $key);

        try {
            return $this->client->get($key);
        } catch (\Exception $e) {
            // todo Logger
            return '';
        }
    }

    /**
     * @param string $key
     * @param string $prefix
     *
     * @return bool
     */
    public function del(string $key, string $prefix = 'cache'): bool
    {
        $key = $this->prepareKeyToString($prefix, $key);

        try {
            return $this->client->del($key);
        } catch (\Exception $e) {
            // todo Logger
            return false;
        }
    }

    /**
     * @param string $prefix
     *
     * @return bool
     */
    public function flush(string $prefix = 'cache'): bool
    {
        $key = $prefix . ":*";

        try {
            return $this->client->del($key);
        } catch (\Exception $e) {
            // todo Logger
            return false;
        }
    }

    /**
     * @param string $prefix
     * @param string $key
     *
     * @return string
     */
    protected function prepareKeyToString(string $prefix, string $key): string
    {
        return $prefix . ":" . str_replace("//", ":", $key) . ":" . md5($key);
    }
}
