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
        
        $this->arProperties = [
            "Title"         => "",
            "inn"           => "", 
            "COMPANY_NAME"  => "", 
            "LEGAL_ADDRESS" => "",
        ];
    }

    public function Execute()
    {
        // Извлекаем и парсим значение ИНН
        $inn = $this->inn;
        if (is_array($inn)) {
            $inn = current($inn);
        }
        $inn = trim($this->ParseValue($inn, "string"));

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

    public static function GetPropertiesDialog($documentType, $activityName, $arAllProperties, $arCurrentProperties, $arAllowComent = true)
    {
        // Жестко гарантируем, что значение подставится как чистая строка, убирая слово Array
        $currentValue = "";
        if (isset($arCurrentProperties["inn"])) {
            if (is_array($arCurrentProperties["inn"])) {
                $currentValue = isset($arCurrentProperties["inn"]["inn"]) ? $arCurrentProperties["inn"]["inn"] : current($arCurrentProperties["inn"]);
            } else {
                $currentValue = $arCurrentProperties["inn"];
            }
        }

        ob_start();
        ?>
        <tr>
            <td align="right" width="40%" class="adm-detail-content-cell-l">
                <span class="adm-required-field">ИНН для запроса:</span>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <?php
                // Передаем плоское имя поля "inn", но Битрикс сам свяжет его с $activityName на бэкенде
                echo CBPDocument::ShowParameterField(
                    $documentType, 
                    "string", 
                    "inn", 
                    $currentValue, 
                    ["size" => 50]
                );
                ?>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }
    
    public static function GetPropertiesDialogValues($documentType, $activityName, &$arCurrentUserProperties, &$arErrors)
    {
        $arErrors = [];

        // Вытаскиваем значение из POST-запроса, учитывая все варианты битриксовой обертки
        $innValue = "";
        if (isset($_POST["inn"])) {
            $innValue = $_POST["inn"];
        } elseif (isset($_POST[$activityName . "_inn"])) {
            $innValue = $_POST[$activityName . "_inn"];
        }

        if (is_array($innValue)) {
            $innValue = current($innValue);
        }

        // Сохраняем в виде чистого плоского массива значений свойств
        $arCurrentUserProperties = [
            "inn" => $innValue
        ];

        return true;
    }
}