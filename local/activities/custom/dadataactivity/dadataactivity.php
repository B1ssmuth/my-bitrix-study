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
        
        // Регистрируем входящее свойство инпута и возвращаемые результаты для БП
        $this->arProperties = [
            "Title"         => "",
            "inn"           => "", 
            "COMPANY_NAME"  => "", 
            "LEGAL_ADDRESS" => "",
        ];
    }

    public function Execute()
    {
        // Защита: если значение ИНН прилетело в виде массива макроса, берем первый элемент
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
                    
                    // Передаем вытащенные данные наружу в переменные БП
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

    // Чистый нативный вывод строки параметров с защитой от перезаписи
    public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $arAllowComment = true)
    {
        // 1. Сначала пытаемся получить ранее сохраненное значение из шаблона БП
        $currentActivity = \CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
        
        $currentValue = "";
        if (is_array($currentActivity) && isset($currentActivity["Properties"]["inn"])) {
            $currentValue = $currentActivity["Properties"]["inn"];
        }

        // 2. Если форма перегружалась (например, была ошибка валидации), берем из текущего ввода
        if (is_array($arCurrentValues) && isset($arCurrentValues["inn"]) && $arCurrentValues["inn"] !== "") {
            $currentValue = $arCurrentValues["inn"];
        }

        // 3. КРИТИЧЕСКАЯ ЗАЩИТА: Если значение равно дефолтной строке "inn", очищаем его,
        // чтобы системный маркер не затирал пустые или кастомные настройки
        if ($currentValue === "inn") {
            $currentValue = "";
        }

        if (is_array($currentValue)) {
            $currentValue = current($currentValue);
        }

        ob_start();
        ?>
        <tr>
            <td align="right" width="40%" class="adm-detail-content-cell-l">
                <span class="adm-required-field">ИНН для запроса:</span>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <?php
                // Генерируем инпут с привязкой к конструктору БП
                echo CBPDocument::ShowParameterField(
                    $documentType, 
                    "string", 
                    "inn", 
                    $currentValue, 
                    ["size" => 45]
                );
                ?>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }
    
    // Прямой перехват и сохранение формы с защитой от дефолтных значений
    public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
    {
        $arErrors = [];

        // Читаем значение ИНН из параметров Битрикса или напрямую из POST для надежности
        $innValue = "";
        if (is_array($arCurrentValues) && isset($arCurrentValues["inn"]) && $arCurrentValues["inn"] !== "") {
            $innValue = $arCurrentValues["inn"];
        } elseif (isset($_POST["inn"]) && $_POST["inn"] !== "") {
            $innValue = $_POST["inn"];
        }

        if (is_array($innValue)) {
            $innValue = current($innValue);
        }

        // Если прилетела дефолтная строка "inn", сбрасываем в пустоту, чтобы не сохранять мусор
        if ($innValue === "inn") {
            $innValue = "";
        }

        // Находим активити по ссылке в структуре БП
        $currentActivity = &\CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);

        // Формируем финальный массив свойств для сохранения
        $properties = [
            "inn" => $innValue,
            "Title" => $arCurrentValues["title"] ?? ($_POST["title"] ?? ($currentActivity["Properties"]["Title"] ?? ""))
        ];

        if (is_array($currentActivity)) {
            $currentActivity["Properties"] = $properties;
        }

        return true;
    }
}