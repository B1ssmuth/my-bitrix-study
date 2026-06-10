<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class CBPDadataActivity extends CBPActivity
{
    // Твой бесплатный токен DaData
    private const API_KEY = "4f8a3d16279f01e84ab4bde551717284839fd4a4";

    public function __construct($name)
    {
        parent::__construct($name);
        
        // Все ключи свойств делаем СТРОГО в нижнем регистре
        $this->arProperties = [
            "Title"         => "",
            "inn"           => "", // Было Inn
            "COMPANY_NAME"  => "", // Должны быть тут, чтобы улетать в "Дополнительные результаты"
            "LEGAL_ADDRESS" => "",
        ];
    }

    public function Execute()
    {
        // Извлекаем значение inn в нижнем регистре
        $inn = trim($this->ParseValue($this->inn, "string"));

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
                    
                    // Пишем напрямую в свойства объекта (Битрикс сам прокинет их в RETURN)
                    $this->COMPANY_NAME = $party["value"] ?? "";
                    $this->LEGAL_ADDRESS = $party["data"]["address"]["value"] ?? "";

                    $this->WriteToTrackingService("Успешно получены данные по ИНН для: " . $this->COMPANY_NAME);
                } else {
                    $this->WriteToTrackingService("Компания с ИНН " . $inn . " не найдена в DaData.");
                }
            } catch (\Exception $e) {
                $this->WriteToTrackingService("Ошибка парсинга JSON DaData: " . $e->getMessage());
            }
        } else {
            $this->WriteToTrackingService("Ошибка запроса к DaData. Код ответа: " . $httpClient->getStatus());
        }

        return CBPActivityExecutionStatus::Closed;
    }

    public static function GetPropertiesDialog($documentType, $activityName, $arAllProperties, $arCurrentProperties, $arAllowComent = true)
    {
        // Проверяем ключ inn в нижнем регистре
        if (!array_key_exists("inn", $arCurrentProperties)) {
            $arCurrentProperties["inn"] = "";
        }

        return '<tr>
            <td align="right" width="40%"><span class="adm-required-field">ИНН для запроса:</span></td>
            <td width="60%">
                <input type="text" name="inn" id="id_inn_field" value="'.htmlspecialcharsbx($arCurrentProperties["inn"]).'" style="width:70%">
                <input type="button" value="..." onclick="BPBDShowVariablesDialog(\'id_inn_field\', \'/bitrix/admin/bizproc_selector.php\', \'string\');">
            </td>
        </tr>';
    }
    
    public static function GetPropertiesDialogValues($documentType, $activityName, &$arCurrentUserProperties, &$arErrors)
    {
        // Сохраняем строго в ключе inn
        $arCurrentUserProperties = [
            "inn" => $_POST["inn"]
        ];

        return true;
    }
}