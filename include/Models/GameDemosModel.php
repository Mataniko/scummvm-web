<?php
namespace ScummVM\Models;

use Propel\Runtime\Collection\ObjectCollection;
use ScummVM\OrmObjects\Demo;
use ScummVM\OrmObjects\DemoQuery;

/**
 * The GameDemosModel class will generate GameDemo objects.
 */
class GameDemosModel extends BaseModel
{
    /**
     * Get all the groups and their respective demos.
     *
     * @return mixed Demos grouped by company.
     */
    public function getAllGroupsAndDemos()
    {
        $groupedData = $this->getFromCache();
        if (is_null($groupedData)) {
            $demos = DemoQuery::create()
                ->useGameQuery()
                ->orderByName()
                ->endUse()
            ->find();
            $groupedData =  $this->createGroups($demos);
            $this->saveToCache($groupedData);
        }
        return $groupedData;
    }

    /**
     * Groups Demo entries by Company. If a Company has less than 15 demos,
     * the demos will be grouped under "Miscellaneous Demos".
     *
     * @param  Demo[] $demos
     * @return mixed Demos grouped by Company.
     */
    private function createGroups($demos)
    {
        $groups = [];
        foreach ($demos as $demo) {
            $company = $demo->getGame()->getCompany();

            if ($company === null) {
                $companyName = "Unknown";
            } else {
                $companyName = $company->getName();
            }
            $companyId = $company->getId();
            if (!isset($groups[$companyId])) {
                $groups[$companyId] = [
                    'name' => "$companyName Demos",
                    'href' => $companyId,
                    'demos' => []
                ];
            }

            $groups[$companyId]['demos'][] = $demo;
        }
        \sort($groups);

        $groups['other'] = [
            'name' => "Miscellaneous Demos",
            'href' => 'other',
            'demos' => []
        ];
        foreach ($groups as $key => $group) {
            if (count($groups[$key]['demos']) <= 15) {
                $groups['other']['demos'] = \array_merge($groups['other']['demos'], $groups[$key]['demos']);
                unset($groups[$key]);
            }
        }
        \sort($groups['other']['demos'], SORT_STRING);
        return $groups;
    }
}
