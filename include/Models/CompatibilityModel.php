<?php
namespace ScummVM\Models;

use Propel\Runtime\ActiveQuery\Criteria;
use ScummVM\OrmObjects\CompatibilityQuery;
use ScummVM\OrmObjects\Compatibility;
use Propel\Runtime\Collection\ObjectCollection;

/**
 * Model to retrieve Compatibility data for specific game ids and ScummVM
 * versions.
 */
class CompatibilityModel extends BaseModel
{
    const NO_VERSION = 'No version specified.';
    const NO_VERSION_TARGET = 'No version and/or target specified.';
    const NOT_FOUND = 'Did not find any games for the specified version.';

    /**
     * Get the last time the Compatibility file was modified.
     *
     * @return int File modified time.
     */
    public function getLastUpdated()
    {
        return filemtime($this->getLocalizedFilename("compatibility.yaml"));
    }

    /**
     * Retrieves all compatibility data for the request version.
     *
     * @param  string   $version Compatibility data for games up to the latest version.
     * @return ObjectCollection Compatibility objects for the specified version.
     */
    private function getAllData(string $version)
    {
        $version = \explode('.', $version);
        return CompatibilityQuery::create()
            ->withColumn("max(release_date)")
            ->groupById()
            ->useGameQuery()
                ->orderByName()
                ->joinCompany()
                ->useEngineQuery()
                    ->filterByEnabled(true)
                ->endUse()
            ->endUse()
            ->useVersionQuery()
                ->filterByMajor($version[0], Criteria::LESS_EQUAL)
                ->filterByMinor($version[1], Criteria::LESS_EQUAL)
                ->filterByPatch($version[2], Criteria::LESS_EQUAL)
            ->endUse()
            ->find();
    }

    /**
     * Get compatibility data for a specific game and version.
     *
     * @param  string $version The version to look up.
     * @param  string $gameId The Game ID to look up.
     * @return Compatibility Compatibility data.
     */
    public function getGameData(string $version, string $gameId)
    {
        if (!is_string($version) || !is_string($gameId)) {
            throw new \ErrorException(self::NO_VERSION_TARGET);
        }
        if ($version === 'DEV') {
            $version = "99.99.99";
        }
        $version = explode('.', $version);
        $gameData = CompatibilityQuery::create()
                ->joinVersion()
                ->withColumn("max(release_date)")
                ->filterById($gameId)
                ->useVersionQuery()
                    ->filterByMajor($version[0], Criteria::LESS_EQUAL)
                    ->filterByMinor($version[1], Criteria::LESS_EQUAL)
                    ->filterByPatch($version[2], Criteria::LESS_EQUAL)
                ->endUse()
            ->findOne();

        if (!$gameData) {
            throw new \ErrorException(self::NOT_FOUND);
        }

        return $gameData;
    }

    /**
     * Categorize and group by Company all Compatibility data for a
     * specific version. Companies with less than 3 supported games will be
     * categorized under the 'Other' category.
     *
     * @param  string $version The ScummVM version to retrieve data for
     * @return array|mixed Compatibility data grouped by Company.
     */
    public function getAllDataGroups(string $version)
    {
        if (!$version || $version === 'DEV') {
            $version = "99.99.99";
        }

        $compat_data = $this->getFromCache($version);
        if (\is_null($compat_data)) {
            $data = $this->getAllData($version);
            $compat_data = [];
            foreach ($data as $compat) {
                $company = $compat->getGame()->getCompany();

                if (!$company) {
                    $companyName = "Unknown";
                } else {
                    $companyName = $company->getName();
                }
                if (!isset($compat_data[$companyName])) {
                    $compat_data[$companyName] = [];
                }
                $compat_data[$companyName][] = $compat;
            }
            $compat_data['Other'] = [];
            foreach ($compat_data as $key => $company) {
                if (count($compat_data[$key]) < 3) {
                    $compat_data['Other'] = \array_merge($compat_data['Other'], $company);
                    unset($compat_data[$key]);
                }
            }
            $this->saveToCache($compat_data, $version);
        }

        return $compat_data;
    }
}
