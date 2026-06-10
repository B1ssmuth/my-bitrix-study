<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class CBPDadataActivity extends CBPActivity
{
    // Твой токен DaData
    private const API_KEY = "4f8a3d16279f01e84ab4bde551717284839fd4a4";

    public function __construct($name)
    {
        parent::__construct($name);
        
        // Регистрируем входящие свойства и возвращаемые результаты
        $this->arProperties = [
            "Title"         => "",
            "inn"           => "", // Входящее свойство ИНН
            "COMPANY_NAME"  => "", 
            "LEGAL_ADDRESS" => "",
        ];
    }

    public function Execute()
    {
        // Безопасное извлечение значения ИНН, даже если оно пришло как массив
        $innValue = $this->inn;
        if (is_array($innValue)) {
            $innValue = current($innValue);
        }
        
        $inn = trim($this->ParseValue($innValue, "string"));

        if (empty($inn)) {
            $this->WriteToTrackingService("ИНН не задан, пропускаем запрос.");
            return CBPActivityExecutionStatus::Closed;
        }

        $httpClient = new HttpClient([
            "version" => "1.1",
            "socketTimeout" => 5,
            "streamTimeout" => 5,
        ]);
        
        $httpClient->setHeader("Content-Type", "application/json");
        $httpClient->setHeader("Accept", "application/json");
        $httpClient->setHeader("Authorization", "Token " . self::API_KEY);

        $url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party";
        $params = Json::encode(["query" => $inn]);

        $response = $httpClient->post($url, $params);

        if ($httpClient->getStatus() == 200 && !empty($response)) {
            try {
                $result = Json::decode($response);
                if (!empty($result["suggestions"][0])) {
                    $party = $result["suggestions"][0];
                    
                    // Записываем результаты во внешние свойства БП
                    $this->COMPANY_NAME = $party["value"] ?? "";
                    $this->LEGAL_ADDRESS = $party["data"]["address"]["value"] ?? "";

                    $this->WriteToTrackingService("DaData: Найдена компания " . $this->COMPANY_NAME);
                } else {
                    $this->WriteToTrackingService("DaData: По ИНН " . $inn . " ничего не найдено.");
                }
            } catch (\Exception $e) {
                $this->WriteToTrackingService("Ошибка парсинга JSON DaData: " . $e->getMessage());
            }
        } else {
            $this->WriteToTrackingService("Ошибка запроса к DaData. Код ответа: " . $httpClient->getStatus());
        }

        return CBPActivityExecutionStatus::Closed;
    }

    // Полностью делегируем отрисовку интерфейса ядру Битрикса.
    // Больше никакой сырой HTML вёрстки — Битрикс сам создаст инпут и свяжет JS-селектор макросов
    public static function GetPropertiesDialog($documentType, $activityName, $arAllProperties, $arCurrentProperties, $arAllowComent = true)
    {
        $runtime = CBPRuntime::GetRuntime();
        $runtime->StartRuntime();

        // Формируем карту полей для автоматического рендеринга диалога параметров
        $arProperties = [
            "inn" => [
                "Name" => "ИНН для запроса",
                "Type" => "string",
                "Required" => true,
                "Multiple" => false,
                "Default" => "",
            ],
        ];

        return $runtime->ExecuteResource("activity_custom_properties_dialog.php", [
            "activityName" => $activityName,
            "arProperties" => $arProperties,
            "arCurrentProperties" => $arCurrentProperties,
        ]);
    }
    
    // Автоматический сбор и валидация данных формы штатными средствами движка CBPActivity
    public static function GetPropertiesDialogValues($documentType, $activityName, &$arCurrentUserProperties, &$arErrors)
    {
        $arErrors = [];

        $arProperties = [
            "inn" => [
                "Name" => "ИНН для запроса",
                "Type" => "string",
                "Required" => true,
            ],
        ];

        // Вызываем штатный метод валидации текущего класса
        $arErrors = self::ValidatePropertiesDialog($documentType, $activityName, $arProperties, $arCurrentUserProperties);
        if (count($arErrors) > 0) {
            return false;
        }

        return true;
    }
}