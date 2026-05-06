<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("ДЗ №5: Компонент валют");

$APPLICATION->IncludeComponent(
	"otus:currency.rate", 
	".default", 
	array(
		"SELECTED_CURRENCY" => "EUR",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>