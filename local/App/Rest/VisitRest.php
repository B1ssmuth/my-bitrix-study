<?php
namespace App\Rest;

use Bitrix\Rest\RestException;
use App\Models\VisitLogTable;
use App\Debug\Log;

/**
 * Класс для обработки пользовательских REST-методов сущности "Журнал посещений" (VisitLog)
 */
class VisitRest
{
    /**
     * Регистрация кастомных REST методов в ядре Битрикс24
     * * @return array Массив с описанием методов
     */
    public static function OnRestServiceBuildDescription(): array
    {
        return [
            'otus.visit' => [
                'otus.visit.add'    => [__CLASS__, 'add'],
                'otus.visit.update' => [__CLASS__, 'update'],
                'otus.visit.delete' => [__CLASS__, 'delete'],
                'otus.visit.get'    => [__CLASS__, 'get'],
                'otus.visit.list'   => [__CLASS__, 'getList'],
            ]
        ];
    }

    /**
     * Вспомогательный метод для логирования запросов (Используем класс из ДЗ №2)
     * * @param string $method Название метода
     * @param array $params Входящие параметры
     * @param mixed $result Результат выполнения
     */
    private static function logRequest(string $method, array $params, $result): void
    {
        Log::addLog([
            'METHOD' => $method,
            'PARAMS' => $params,
            'RESULT' => $result
        ], false, 'rest_crud');
    }

    /**
     * Создание новой записи (Create)
     */
    public static function add($arParams, $navStart, \CRestServer $server)
    {
        $data = $arParams['FIELDS'] ?? [];
        
        if (empty($data['PATIENT_NAME'])) {
            throw new RestException('Параметр PATIENT_NAME обязателен для заполнения', 'ERROR_ARGUMENT');
        }

        $result = VisitLogTable::add($data);
        
        if ($result->isSuccess()) {
            $id = $result->getId();
            self::logRequest('otus.visit.add', $arParams, ['ID' => $id]);
            return $id;
        }

        throw new RestException(implode(', ', $result->getErrorMessages()), 'ERROR_CORE');
    }

    /**
     * Чтение одной записи по ID (Read)
     */
    public static function get($arParams, $navStart, \CRestServer $server)
    {
        $id = (int)($arParams['ID'] ?? 0);
        
        if ($id <= 0) {
            throw new RestException('Параметр ID обязателен', 'ERROR_ARGUMENT');
        }

        $item = VisitLogTable::getByPrimary($id)->fetch();
        
        if (!$item) {
            throw new RestException('Запись не найдена в базе', 'ERROR_NOT_FOUND');
        }

        self::logRequest('otus.visit.get', $arParams, $item);
        return $item;
    }

    /**
     * Обновление записи (Update)
     */
    public static function update($arParams, $navStart, \CRestServer $server)
    {
        $id = (int)($arParams['ID'] ?? 0);
        $data = $arParams['FIELDS'] ?? [];

        if ($id <= 0 || empty($data)) {
            throw new RestException('Параметры ID и FIELDS обязательны', 'ERROR_ARGUMENT');
        }

        $result = VisitLogTable::update($id, $data);
        
        if ($result->isSuccess()) {
            self::logRequest('otus.visit.update', $arParams, true);
            return true;
        }

        throw new RestException(implode(', ', $result->getErrorMessages()), 'ERROR_CORE');
    }

    /**
     * Удаление записи (Delete)
     */
    public static function delete($arParams, $navStart, \CRestServer $server)
    {
        $id = (int)($arParams['ID'] ?? 0);
        
        if ($id <= 0) {
            throw new RestException('Параметр ID обязателен', 'ERROR_ARGUMENT');
        }

        $result = VisitLogTable::delete($id);
        
        if ($result->isSuccess()) {
            self::logRequest('otus.visit.delete', $arParams, true);
            return true;
        }

        throw new RestException(implode(', ', $result->getErrorMessages()), 'ERROR_CORE');
    }

    /**
     * Получение списка записей (List)
     */
    public static function getList($arParams, $navStart, \CRestServer $server)
    {
        $params = [
            'select' => $arParams['select'] ?? ['*'],
            'filter' => $arParams['filter'] ?? [],
            'order'  => $arParams['order']  ?? ['ID' => 'DESC'],
            'limit'  => $arParams['limit']  ?? 50,
            'offset' => $arParams['offset'] ?? 0,
        ];

        $items = VisitLogTable::getList($params)->fetchAll();
        
        self::logRequest('otus.visit.list', $arParams, ['count' => count($items)]);
        return $items;
    }
}