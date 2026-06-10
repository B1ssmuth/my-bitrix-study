<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arActivityDescription = [
    "NAME" => "Запрос реквизитов из DaData",
    "DESCRIPTION" => "Получает данные компании из DaData по ИНН для ДЗ №9",
    "TYPE" => "activity",
    "CLASS" => "DataDataActivity",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "COMPANY_NAME" => [
            "NAME" => "Название компании (краткое)",
            "TYPE" => "string",
        ],
        "LEGAL_ADDRESS" => [
            "NAME" => "Юридический адрес",
            "TYPE" => "string",
        ],
    ],
];