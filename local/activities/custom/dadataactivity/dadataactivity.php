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
        
        // Используем уникальный ключ company_inn вместо конфликтного inn
        $this->arProperties = [
            "Title"         => "",
            "company_inn"   => "", 
            "COMPANY_NAME"  => "", 
            "LEGAL_ADDRESS" => "",
        ];
    }

    public function Execute()
    {
        $innValue = $this->company_inn;
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

    // Чистый нативный вывод строки параметров с жестким отсечением дефолтных маркеров
    public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $arAllowComment = true)
    {
        // 1. Проверяем сохраненные свойства в шаблоне БП
        $currentActivity = \CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
        
        $currentValue = "";
        if (is_array($currentActivity) && isset($currentActivity["Properties"]["company_inn"])) {
            $currentValue = $currentActivity["Properties"]["company_inn"];
        }

        // 2. Если форма перегружалась (например, была ошибка), берем из текущего ввода
        if (is_array($arCurrentValues) && isset($arCurrentValues["company_inn"]) && $arCurrentValues["company_inn"] !== "") {
            $currentValue = $arCurrentValues["company_inn"];
        }

        if (is_array($currentValue)) {
            $currentValue = current($currentValue);
        }

        // КРИТИЧЕСКАЯ ЗАЩИТА: Если значение совпадает с дефолтным маркером, принудительно очищаем поле
        if (!is_string($currentValue) || trim($currentValue) === "company_inn" || trim($currentValue) === "inn") {
            $currentValue = "";
        }

        ob_start();
        ?>
        <tr>
            <td align="right" width="40%" class="adm-detail-content-cell-l">
                <span class="adm-required-field">ИНН для запроса:</span>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <?php
                echo CBPDocument::ShowParameterField(
                    $documentType, 
                    "string", 
                    "company_inn", 
                    $currentValue, 
                    ["size" => 45]
                );
                ?>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }
    
    // Прямой перехват и валидация сохранения формы
    public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
    {
        $arErrors = [];

        $innValue = "";
        if (is_array($arCurrentValues) && isset($arCurrentValues["company_inn"]) && $arCurrentValues["company_inn"] !== "") {
            $innValue = $arCurrentValues["company_inn"];
        } elseif (isset($_POST["company_inn"]) && $_POST["company_inn"] !== "") {
            $innValue = $_POST["company_inn"];
        }

        if (is_array($innValue)) {
            $innValue = current($innValue);
        }

        // Фильтруем дефолтный мусор при сохранении
        if (!is_string($innValue) || trim($innValue) === "company_inn" || trim($innValue) === "inn") {
            $innValue = "";
        }

        $currentActivity = &\CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);

        $properties = [
            "company_inn" => $innValue,
            "Title" => $arCurrentValues["title"] ?? ($_POST["title"] ?? ($currentActivity["Properties"]["Title"] ?? ""))
        ];

        if (is_array($currentActivity)) {
            $currentActivity["Properties"] = $properties;
        }

        return true;
    }
}