<?php

namespace App\Properties;

class BookingProperty
{
    public static function GetUserTypeDescription()
    {
        return [
            "USER_TYPE_ID" => "doctor_booking_system",
            "USER_TYPE" => "doctor_booking_system",
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => "Интерфейс записи (ДЗ 7)",
            "PROPERTY_TYPE" => "S",
            "GetPublicViewHTML" => [__CLASS__, "GetPublicViewHTML"],
            "GetPropertyFieldHtml" => [__CLASS__, "GetPropertyFieldHtml"],
        ];
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        return '<input type="text" value="Интерфейс активен" readonly>';
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        $doctorId = intval($arProperty['ELEMENT_ID'] ?? 0);
        if (!$doctorId) return '';

        \Bitrix\Main\Loader::includeModule('iblock');
        
        $resProcs = \CIBlockElement::GetList(
            ["SORT" => "ASC"], 
            ["IBLOCK_ID" => 18, "ACTIVE" => "Y"], 
            false, 
            false, 
            ["ID", "NAME"]
        );
        
        $html = '<div class="procedures-list" style="margin-top: 10px;">';
        $html .= '<p style="margin-bottom: 8px; font-weight: bold; font-size: 14px; color: #555;">Доступные процедуры:</p>';
        
        $hasProcs = false;
        while ($proc = $resProcs->GetNext()) {
            $hasProcs = true;
            $procNameEscaped = \CUtil::JSEscape($proc['NAME']);
            
            $html .= '<button class="ui-btn ui-btn-sm ui-btn-primary" ';
            $html .= 'onclick="window.openBookingPopup(' . $doctorId . ', ' . $proc['ID'] . ', \'' . $procNameEscaped . '\'); return false;" ';
            $html .= 'style="margin: 4px; background-color: #2fc6f6; border-color: #2fc6f6; color: #fff; font-weight: bold; border-radius: 4px; cursor: pointer; border: none; padding: 5px 10px; font-size: 12px;">';
            $html .= '📅 ' . htmlspecialchars($proc['NAME']);
            $html .= '</button>';
        }

        if (!$hasProcs) {
            $html .= '<span style="color: #999; font-size: 13px;">В инфоблоке №18 нет процедур.</span>';
        }
        $html .= '</div>';

        return $html; 
    }
}