<?php
namespace Otus\CrmTab\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Loader;
use CIBlockElement;

class Visit extends Controller
{
    private const BOOKING_IBLOCK_ID = 18;

    public function configureActions()
    {
        return [
            'getCount' => [
                'prefilters' => [new ActionFilter\Csrf(false)],
            ],
            'createBooking' => [
                'prefilters' => [new ActionFilter\Csrf(false)],
            ],
        ];
    }

    public function createBookingAction($patientName, $doctorId, $procedureId, $visitDate)
    {
        Loader::includeModule('iblock');

        $timestamp = strtotime($visitDate);
        if (!$timestamp) {
            return ['success' => false, 'message' => 'Некорректный формат даты и времени'];
        }
        $bitrixDate = date('d.m.Y H:i:00', $timestamp);

        $el = new CIBlockElement;
        
        $fields = [
            "IBLOCK_ID" => self::BOOKING_IBLOCK_ID,
            "NAME" => $patientName,
            "ACTIVE" => "Y",
            "PROPERTY_VALUES" => [
                "DOCTOR" => $doctorId,
                "PROCEDURE" => $procedureId,
                "DATE" => $bitrixDate
            ]
        ];

        $newElementId = $el->Add($fields);

        if ($newElementId) {
            return ['success' => true, 'bookingId' => $newElementId];
        }

        return ['success' => false, 'message' => $el->LAST_ERROR];
    }
}