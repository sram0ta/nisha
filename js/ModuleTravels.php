<?php

namespace TMCms\Modules\Travels;

use TMCms\DB\SQL;
use TMCms\Modules\IModule;
use TMCms\Traits\singletonInstanceTrait;
use TMCms\Modules\ModuleManager;

ModuleManager::requireModule('countries');


class ModuleTravels implements IModule {
    use singletonInstanceTrait;

    public static $tables = [
        'travels' => 'm_travels'
    ];

    public static function getAirlines($q)
    {
        $airlines = new TravelsAirlineRepository();

        if ($q) {
            $q = SQL::sql_prepare($q, true);
            $airlines->addWhereFieldAsString('`name` LIKE "%'. $q .'%"');
        }

        $result = array();
        foreach ($airlines->getPairs('name', 'code') as $k => $v) {
            $result[] = array('code' => $k, 'label' => $v);
        }

        return $result;
    }

    public static function getCountriesWithAeroports()
    {
        $aeroports = new TravelsAeroportRepository();
        $aeroports->addSimpleSelectFields(['country_code']);
        $aeroports->addGroupBy('country_code');

        $countries = new TravelsCountryRepository();
        $countries->addSimpleSelectFields(['name_' . LNG . '` AS `country']);

        $aeroports->mergeWithCollection($countries, 'country_code', 'code');

        $countries = array();
        foreach ($aeroports->getAsArrayOfObjectData(true) as $k => $v) {
            if (!empty($v['country_code']) && !empty($v['country'])) {
                $countries[$v['country']] = $v['country_code'];
            }
        }

        ksort($countries);

        return $countries;
    }

    public static function getAeroports($q)
    {
        $aeroports = new TravelsAeroportRepository();
        $aeroports->addSimpleSelectFields(['airport_iata`, `city_iata`, `airport_name_'.LNG.'` AS `airport_name`, `city_'.LNG.'` AS `city']);

        if ($q) {
            $q = SQL::sql_prepare($q, true);
            $aeroports->addWhereFieldAsString('
                (`airport_iata` LIKE "%'. $q .'%") OR
                (`city_iata` LIKE "%'. $q .'%") OR
                (`country_code` LIKE "%'. $q .'%") OR
                (`info` LIKE "%'. $q .'%") OR
                (`airport_name_en` LIKE "%'. $q .'%") OR
                (`airport_name_lv` LIKE "%'. $q .'%") OR
                (`airport_name_ru` LIKE "%'. $q .'%") OR
                (`city_lv` LIKE "%'. $q .'%") OR
                (`city_en` LIKE "%'. $q .'%") OR
                (`city_ru` LIKE "%'. $q .'%")
            ');
        }

        $data = $aeroports->getAsArrayOfObjectData(true);
        $result = array();

        foreach ($data as $k => $v) {
            if (!empty($v['city_iata']) && !empty($v['city'])) {
                if (empty($result[$v['city_iata'] .'_'. $v['city']])) {
                    $result[$v['city_iata'] .'_'. $v['city']][] = array(
                        'iata' => $v['city_iata'],
                        'city' => $v['city']
                    );
                }

                $row = $v;
                $row['iata'] = $row['airport_iata'];
                unset($row['airport_iata']);

                $result[$v['city_iata'] .'_'. $v['city']][] = $row;
            }
        }

        $final_result = array();
        foreach ($result as $k => $v) {
            if (count($v)) {
                foreach ($v as $kk => $item) {
                    if (count($v) < 3) {
                        if (count($item) > 2) { //first fake item have only 2 fields, not 4!!!
                            $final_result[] = $item;
                        }
                    } else {
                        $final_result[] = $item;
                    }
                }
            }
        }

//      header('Content-Type: application/json');
//      $data = json_encode($data, JSON_OBJECT_AS_ARRAY);

        return $final_result;
    }

    public static function getHotels($q)
    {
        $hotels = new TravelsHotelRepository();
        $hotels->addSimpleSelectFields(['hotel_iata`, `city_'.LNG.'` AS `city']);

        if ($q) {
            $q = SQL::sql_prepare($q, true);
            $hotels->addWhereFieldAsString('
                (`hotel_iata` LIKE "%'. $q .'%") OR
                (`city_lv` LIKE "%'. $q .'%") OR
                (`city_en` LIKE "%'. $q .'%") OR
                (`city_ru` LIKE "%'. $q .'%")
            ');
        }

        $data = $hotels->getAsArrayOfObjectData(true);
        $result = array();

        foreach ($data as $k => $v) {
            if (!empty($v['city'])) {
//                if (empty($result[$v['city']])) {
//                    $result[$v['city']][] = array(
//                        'city' => $v['city']
//                    );
//                }

                $row = $v;

                $result[$v['city']][] = $row;
            }
        }

        $final_result = array();
        foreach ($result as $k => $v) {
            if (count($v)) {
                foreach ($v as $kk => $item) {

//                    if (count($v) < 3) {
//                        if (count($item) > 2) { //first fake item have only 2 fields, not 4!!!
//                            $final_result[] = $item;
//                        }
//                    } else {
//                        $final_result[] = $item;
//                    }

                    $final_result[] = $item;
                }
            }
        }

        return $final_result;
    }

    public static function getAeroport()
    {
        $aeroport = new TravelsAeroportRepository();
        $aeroport->setWhereCountryCode('LV');
        $aeroport->addSimpleSelectFields(['airport_iata`, `city_iata`, `airport_name_'.LNG.'` AS `airport_name`, `city_'.LNG.'` AS `city']);

        return $aeroport->getFirstObjectFromCollection();
    }

    public static function getAeroportsData($code)
    {
        $aeroports = new TravelsAeroportRepository();
        $aeroports->addSimpleSelectFields(['airport_iata`, `country_code`, `info`, `latitude`, `longitude`, `airport_name_'.LNG.'` AS `airport_name`, `city_'.LNG.'` AS `city']);

        if ($code) {
            $aeroports->setWhereCountryCode($code);
        }

        return $aeroports->getAsArrayOfObjectData(true);
    }
}