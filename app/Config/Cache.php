<?php

namespace Config;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Cache\Handlers\DummyHandler;
use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\Cache\Handlers\MemcachedHandler;
use CodeIgniter\Cache\Handlers\PredisHandler;
use CodeIgniter\Cache\Handlers\RedisHandler;
use CodeIgniter\Cache\Handlers\WincacheHandler;
use CodeIgniter\Config\BaseConfig;

class Cache extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Primary Handler
     * --------------------------------------------------------------------------
     *
     * The name of the preferred handler that should be used. If for some reason
     * it is not available, the $backupHandler will be used in its place.
     */
    public string $handler = (MY_CACHE_HANDLER == 'disable' ? 'file' : MY_CACHE_HANDLER);

    /**
     * --------------------------------------------------------------------------
     * Backup Handler
     * --------------------------------------------------------------------------
     *
     * The name of the handler that will be used in case the first one is
     * unreachable. Often, 'file' is used here since the filesystem is
     * always available, though that's not always practical for the app.
     */
    public string $backupHandler = 'dummy';

    /**
     * --------------------------------------------------------------------------
     * Key Prefix
     * --------------------------------------------------------------------------
     *
     * This string is added to all cache item names to help avoid collisions
     * if you run multiple applications with the same cache engine.
     */
    public string $prefix = CACHE_HOST_PREFIX;

    /**
     * --------------------------------------------------------------------------
     * Default TTL
     * --------------------------------------------------------------------------
     *
     * The default number of seconds to save items when none is specified.
     *
     * WARNING: This is not used by framework handlers where 60 seconds is
     * hard-coded, but may be useful to projects and modules. This will replace
     * the hard-coded value in a future release.
     */
    public int $ttl = 60;

    /**
     * --------------------------------------------------------------------------
     * Reserved Characters
     * --------------------------------------------------------------------------
     *
     * A string of reserved characters that will not be allowed in keys or tags.
     * Strings that violate this restriction will cause handlers to throw.
     * Default: {}()/\@:
     *
     * NOTE: The default set is required for PSR-6 compliance.
     */
    public string $reservedCharacters = '{}()/\@:';

    /**
     * --------------------------------------------------------------------------
     * File settings
     * --------------------------------------------------------------------------
     *
     * Your file storage preferences can be specified below, if you are using
     * the File driver.
     *
     * @var array{storePath?: string, mode?: int}
     */
    public array $file = [
        'storePath' => WRITE_CACHE_PATH,
        'mode'      => 0777,
    ];

    /**
     * -------------------------------------------------------------------------
     * Memcached settings
     * -------------------------------------------------------------------------
     *
     * Your Memcached servers can be specified below, if you are using
     * the Memcached drivers.
     *
     * @see https://codeigniter.com/user_guide/libraries/caching.html#memcached
     *
     * @var array{host?: string, port?: int, weight?: int, raw?: bool}
     */
    public array $memcached = [
        'host'   => WGR_MEMCACHED_HOSTNAME,
        'port'   => WGR_MEMCACHED_PORT,
        'weight' => 1,
        'raw'    => false,
    ];

    /**
     * -------------------------------------------------------------------------
     * Redis settings
     * -------------------------------------------------------------------------
     *
     * Your Redis server can be specified below, if you are using
     * the Redis or Predis drivers.
     *
     * @var array{host?: string, password?: string|null, port?: int, timeout?: int, database?: int}
     */
    public array $redis = [
        'host'     => WGR_REDIS_HOSTNAME,
        'password' => null,
        'port'     => WGR_REDIS_PORT,
        'timeout'  => 0,
        'database' => 0,
    ];

    /**
     * --------------------------------------------------------------------------
     * Available Cache Handlers
     * --------------------------------------------------------------------------
     *
     * This is an array of cache engine alias' and class names. Only engines
     * that are listed here are allowed to be used.
     *
     * @var array<string, class-string<CacheInterface>>
     */
    public array $validHandlers = [
        'dummy'     => DummyHandler::class,
        'file'      => FileHandler::class,
        'memcached' => MemcachedHandler::class,
        'predis'    => PredisHandler::class,
        'redis'     => RedisHandler::class,
        'wincache'  => WincacheHandler::class,
    ];

    /**
     * --------------------------------------------------------------------------
     * Web Page Caching: Cache Include Query String
     * --------------------------------------------------------------------------
     *
     * Whether to take the URL query string into consideration when generating
     * output cache files. Valid options are:
     *
     *    false = Disabled
     *    true  = Enabled, take all query parameters into account.
     *            Please be aware that this may result in numerous cache
     *            files generated for the same page over and over again.
     *    ['q'] = Enabled, but only take into account the specified list
     *            of query parameters.
     *
     * @var bool|list<string>
     */
    public $cacheQueryString = false;
}
