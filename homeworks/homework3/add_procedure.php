<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['name'])) {
    $el = new CIBlockElement;
    $el->Add([
        "IBLOCK_ID" => 16,
        "NAME" => $_POST['name'],
        "ACTIVE" => "Y"
    ]);
    LocalRedirect("index.php");
}
?>
<form method="POST" style="max-width: 400px; margin: 50px auto; text-align: center;">
    <h2>Новая процедура</h2>
    <input type="text" name="name" placeholder="Название услуги (например, ЭКГ)" required style="width: 100%; padding: 10px; margin-bottom: 20px;">
    <button type="submit" style="width: 100%; padding: 10px;">Добавить в список</button>
</form>