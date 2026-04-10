<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Loader;
use App\Models\Lists\ProceduresTable;

Loader::includeModule('iblock');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['name'])) {
    $el = new CIBlockElement;
    
    $fields = [
        "IBLOCK_ID" => ProceduresTable::IBLOCK_ID,
        "NAME" => $_POST['name'],
        "ACTIVE" => "Y",
        "CODE" => $_POST['code']
    ];

    if ($el->Add($fields)) {
        LocalRedirect("index.php");
    }
}
?>

<div style="max-width: 500px; margin: 50px auto; font-family: sans-serif;">
    <h2 style="text-align: center;">Новая процедура</h2>

    <form method="POST" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background: #fafafa;">
        <div style="margin-bottom: 15px;">
            <label>Название услуги:</label><br>
            <input type="text" name="name" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc;">
        </div>

        <div style="margin-bottom: 20px;">
            <label>Символьный код:</label><br>
            <input type="text" name="code" placeholder="mrt_test" style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc;">
        </div>

        <button type="submit" style="width: 100%; padding: 12px; background: #fff; border: 1px solid #000; cursor: pointer; font-weight: bold;">
            Сохранить услугу
        </button>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="index.php" style="color: #666; text-decoration: none;">← Назад</a>
        </div>
    </form>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>