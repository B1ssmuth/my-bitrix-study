<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class CBPDadataActivity extends CBPActivity
{
    private const API_KEY = "ТВОЙ_API_КЛЮЧ_DADATA";

    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            "Title" => "",
            "Inn"   => "",
        ];
    }

    public function Execute()
    {
        $inn = trim($this->ParseValue($this->Inn, "string"));

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
                    
                    $this->arProperties["COMPANY_NAME"] = $party["value"] ?? "";
                    $this->arProperties["LEGAL_ADDRESS"] = $party["data"]["address"]["value"] ?? "";

                    $this->WriteToTrackingService("Успешно получены данные по ИНН для: " . $this->arProperties["COMPANY_NAME"]);
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
        $runtime = CBPRuntime::GetRuntime();
        $runtime->StartRuntime();

        if (!array_key_exists("Inn", $arCurrentProperties)) {
            $arCurrentProperties["Inn"] = "";
        }

        return '<tr id="dadata_inn_field">
            <td align="right" width="40%" class="adm-detail-content-cell-l"><span class="adm-required-field">ИНН Заказчика:</span></td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="text" name="inn" id="id_inn" value="'.htmlspecialcharsbx($arCurrentProperties["Inn"]).'" style="width:70%">
                <input type="button" value="..." onclick="BPBDShowVariablesDialog(\'id_inn\', \'/bitrix/admin/bizproc_selector.php\', \'string\');">
            </td>
        </tr>';
    }

    public static function GetPropertiesDialogValues($documentType, $activityName, &$arCurrentUserProperties, &$arErrors)
    {
        $arCurrentUserProperties = [
            "Inn" => $_POST["inn"]
        ];

        return true;
    }
}