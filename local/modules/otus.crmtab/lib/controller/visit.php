<?php
namespace Otus\CrmTab\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Loader;
use CIBlockElement;
use CIBlock;

class Visit extends Controller
{
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

    private function getIblockIdByCode(string $code): int
    {
        if (!Loader::includeModule('iblock')) {
            return 0;
        }
        $res = CIBlock::GetList([], ['=CODE' => $code, 'CHECK_PERMISSIONS' => 'N'])->Fetch();
        return $res ? (int)$res['ID'] : 0;
    }

    public function createBookingAction($patientName, $doctorId, $procedureId, $visitDate)
    {
        Loader::includeModule('iblock');

        $bookingIblockId = $this->getIblockIdByCode('BOOKING');
        if ($bookingIblockId <= 0) {
            return ['success' => false, 'message' => 'Инфоблок BOOKING не найден!'];
        }

        $timestamp = strtotime($visitDate);
        if (!$timestamp) {
            return ['success' => false, 'message' => 'Некорректный формат даты'];
        }
        $bitrixDate = date('d.m.Y H:i:00', $timestamp);

        $el = new CIBlockElement;
        $fields = [
            "IBLOCK_ID" => $bookingIblockId,
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