<?php
namespace Otus\CrmTab\Controller;

use Bitrix\Main\Engine\Controller;
use App\Models\VisitLogTable;
// Добавь этот импорт для фильтров
use Bitrix\Main\Engine\ActionFilter;

class Visit extends Controller
{
    public function configureActions()
    {
        return [
            'getCount' => [
                'prefilters' => [
                    new ActionFilter\Csrf(false),
                ],
            ],
        ];
    }

    public function getCountAction()
    {
        return [
            'count' => VisitLogTable::getCount()
        ];
    }
}