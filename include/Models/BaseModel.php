<?php
namespace ScummVM\Models;

use Phpfastcache\Helper\Psr16Adapter;
use Phpfastcache\Drivers\Predis\Config as PredisConfig;
use Phpfastcache\Drivers\Redis\Config as RedisConfig;
use Phpfastcache\Exceptions\PhpfastcacheDriverException;

/**
 * The base model provides abstractions to use the cache and retrieve localized
 * data file names.
 *
 */
abstract class BaseModel
{
    const FILE_NOT_FOUND = 'The filename %s could not be found';

    protected static $cache;

    /**
     * Sets up the cache driver used to save and store data.
     * If a file called `.no-cache` exists in the root folder the existing
     * cache will be purged.
     */
    public function __construct()
    {
        if (is_null(self::$cache)) {
            try {
                $driver = extension_loaded('redis') ? 'redis' : 'predis';
                $database = $_SERVER['HTTP_HOST'] === 'www.scummvm.org' ? 8 : 7;
                $config = extension_loaded('redis')
                    ? new RedisConfig(['database' => $database])
                    : new PredisConfig(['database' => $database]);
                self::$cache = new Psr16Adapter($driver, $config);
            } catch (PhpfastcacheDriverException $ex) {
                // Fallback to files based cache
                self::$cache = new Psr16Adapter('files');
            }

            if (\file_exists(DIR_BASE . '/.clear-cache')) {
                self::$cache->clear();
                unlink(DIR_BASE . '/.clear-cache');
            }
        }
    }

    /**
     * Data file for current language, or the default language data file if
     * the localized file doesn't exist. Throws if no file is found.
     *
     * @param  string   Name of the Data file to look for.
     * @return string   Full path to the localized data file. It is guaranteed
     *                  to exist.
     */
    protected function getLocalizedFilename(string $filename)
    {
        global $lang;
        if (!$lang) {
            $lang = DEFAULT_LOCALE;
        }
        $localizedFilename = DIR_DATA . "/$lang/$filename";
        $defaultFilename = DIR_DATA . "/" . DEFAULT_LOCALE . "/$filename";
        if (\is_file($localizedFilename) && \is_readable($localizedFilename)) {
            return $localizedFilename;
        } elseif (\is_file($defaultFilename) && \is_readable($defaultFilename)) {
            return $defaultFilename;
        } else {
            throw new \ErrorException(\sprintf(self::FILE_NOT_FOUND, $filename));
        }
    }

    /**
     * Saves data to the cache.
     *
     * @param  mixed    $data
     * @param  string   $key    Supplemental key to use when generating the
     *                          default cache key.
     * @return void
     */
    protected function saveToCache($data, string $key = '')
    {
        if ($key) {
            $key = "_$key";
        }
        global $lang;
        $cacheKey = str_replace("\\", "_", \get_called_class() . $key . "_$lang");
        self::$cache->set($cacheKey, $data, 3600);
    }

    /**
     * Retrieves data from the cache.
     *
     * @param  string     Supplemental key to the default cache key when
     *                    retrieving data from the cache.
     * @return mixed|null Data from the cache. Null if not key is not found in
     *                    the cache or a file named `.no-cache`
     *                    exists in the root folder.
     */
    protected function getFromCache(string $key = '')
    {
        if (\file_exists(DIR_BASE . '/.no-cache')) {
            return null;
        }

        if ($key) {
            $key = "_$key";
        }
        global $lang;
        $cacheKey = str_replace("\\", "_", \get_called_class() . $key . "_$lang");
        $cachedData = self::$cache->get($cacheKey);
        return $cachedData;
    }
}
