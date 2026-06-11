<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arActivityDescription = [
    "NAME" => "Запрос реквизитов из DaData (OTUS)",
    "DESCRIPTION" => "Получает данные компании из DaData по ИНН для ДЗ №9",
    "TYPE" => "activity",
    "CLASS" => "DadataActivity",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "document",
    ],
    "RETURN" => [
        "COMPANY_NAME" => [
            "NAME" => "Название компании",
            "TYPE" => "string",
        ],
        "LEGAL_ADDRESS" => [
            "NAME" => "Юридический адрес",
            "TYPE" => "string",
        ],
    ],
];