<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use App\Models\Lists\ProceduresTable;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['name'])) {
    $el = new CIBlockElement;
    $el->Add([
        "IBLOCK_ID" => ProceduresTable::IBLOCK_ID,
        "NAME" => $_POST['name'],
        "ACTIVE" => "Y"
    ]);
    LocalRedirect("index.php");
}
?>
<form method="POST" style="max-width: 400px; margin: 50px auto; text-align: center; font-family: sans-serif;">
    <h2>Новая процедура</h2>
    <input type="text" name="name" placeholder="Название (УЗИ, МРТ...)" required style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc;">
    <button type="submit" style="width: 100%; padding: 12px; background: #fff; border: 1px solid #000; cursor: pointer;">Сохранить</button>
</form>