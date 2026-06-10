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
        
        // Регистрируем входящее свойство inn и возвращаемые результаты
        $this->arProperties = [
            "Title"         => "",
            "inn"           => "", 
            "COMPANY_NAME"  => "", 
            "LEGAL_ADDRESS" => "",
        ];
    }

    public function Execute()
    {
        // Извлекаем значение ИНН
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
                    
                    // Передаем полученные значения наружу в БП
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

    // Правильная отрисовка диалога с явным указанием ID и имени поля
    public static function GetPropertiesDialog($documentType, $activityName, $arAllProperties, $arCurrentProperties, $arAllowComent = true)
    {
        if (!array_key_exists("inn", $arCurrentProperties)) {
            $arCurrentProperties["inn"] = "";
        }

        ob_start();
        ?>
        <tr>
            <td align="right" width="40%" class="adm-detail-content-cell-l">
                <span class="adm-required-field">ИНН для запроса:</span>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <?php
                // Передаем имя поля "inn" третьим параметром, чтобы Битрикс собрал правильный $_POST
                echo CBPDocument::ShowParameterField(
                    $documentType, 
                    "string", 
                    "inn", 
                    $arCurrentProperties["inn"], 
                    ["size" => 40, "id" => "id_inn_field"]
                );
                ?>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }
    
    // Метод парсинга и сохранения значений из диалога параметров
    public static function GetPropertiesDialogValues($documentType, $activityName, &$arCurrentUserProperties, &$arErrors)
    {
        $arErrors = [];

        // Проверяем, пришел ли ИНН из отправленной формы дизайна БП
        if (isset($_POST["inn"]) && $_POST["inn"] <> '') {
            $arCurrentUserProperties = [
                "inn" => $_POST["inn"]
            ];
        } else {
            // Если поле пустое, Битрикс не должен ломать схему, просто пишем пустую строку
            $arCurrentUserProperties = [
                "inn" => ""
            ];
        }

        return true;
    }
}