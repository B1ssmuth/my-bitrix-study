<?php
namespace App\Events;

use Bitrix\Main\Loader;

class SyncDealIblock 
{
    public static $isSyncing = false;
    
    const IBLOCK_REQ_ID = 23; 

    public static function syncFromIblock(&$arFields) 
    {
        if (self::$isSyncing || $arFields['IBLOCK_ID'] != self::IBLOCK_REQ_ID) {
            return;
        }

        Loader::includeModule('crm');
        Loader::includeModule('iblock');

        $dbProps = \CIBlockElement::GetProperty(self::IBLOCK_REQ_ID, $arFields['ID']);
        $props = [];
        while ($prop = $dbProps->Fetch()) {
            $props[$prop['CODE']] = $prop['VALUE'];
        }

        $dealIdRaw = $props['DEAL'] ?? '';
        $dealId = intval(str_replace('D_', '', $dealIdRaw));

        if ($dealId > 0) {
            $dealFields = [];
            
            if (!empty($props['SUMMA'])) {
                $dealFields['OPPORTUNITY'] = $props['SUMMA'];
            }
            if (!empty($props['RESPONSIBLE'])) {
                $dealFields['ASSIGNED_BY_ID'] = $props['RESPONSIBLE'];
            }
            if (!empty($props['CLIENT'])) {
                $dealFields['CONTACT_ID'] = intval(str_replace('C_', '', $props['CLIENT'])); 
            }

            if (!empty($dealFields)) {
                self::$isSyncing = true; 
                
                $CCrmDeal = new \CCrmDeal(false);
                $CCrmDeal->Update($dealId, $dealFields); 
                
                self::$isSyncing = false; 
            }
        }
    }

    public static function syncFromDeal(&$arFields) 
    {
        if (self::$isSyncing) {
            return;
        }

        $dealId = intval($arFields['ID']);
        if ($dealId <= 0) {
            return;
        }

        Loader::includeModule('iblock');

        $propValues = [];
        if (isset($arFields['OPPORTUNITY'])) {
            $propValues['SUMMA'] = $arFields['OPPORTUNITY'];
        }
        if (isset($arFields['ASSIGNED_BY_ID'])) {
            $propValues['RESPONSIBLE'] = $arFields['ASSIGNED_BY_ID'];
        }
        if (isset($arFields['CONTACT_ID'])) {
            $propValues['CLIENT'] = "C_" . $arFields['CONTACT_ID'];
        }

        if (empty($propValues)) {
            return;
        }

        self::$isSyncing = true; 

        $rsElements = \CIBlockElement::GetList(
            [],
            [
                "IBLOCK_ID" => self::IBLOCK_REQ_ID,
                [
                    "LOGIC" => "OR",
                    ["PROPERTY_DEAL" => $dealId],
                    ["PROPERTY_DEAL" => "D_" . $dealId]
                ]
            ],
            false,
            false,
            ["ID"]
        );

        while ($el = $rsElements->Fetch()) {
            \CIBlockElement::SetPropertyValuesEx($el['ID'], self::IBLOCK_REQ_ID, $propValues);
        }

        self::$isSyncing = false; 
    }
}