<?php
namespace Otus\CrmTab\Controller;

use Bitrix\Main\Engine\Controller;
use App\Models\VisitLogTable;

class Visit extends Controller
{
    public function getCountAction()
    {
        return [
            'count' => VisitLogTable::getCount()
        ];
    }
}