<?php
// 1. Подключаем ядро
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// 2. ОБЯЗАТЕЛЬНО подключаем модуль и класс (этого не было на скрине!)
\Bitrix\Main\Loader::includeModule('iblock');
use App\Models\Lists\ProceduresTable;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['name'])) {
    $el = new CIBlockElement;
    
    // 3. Используем константу из модели (там теперь 18)
    $res = $el->Add([
        "IBLOCK_ID" => ProceduresTable::IBLOCK_ID, 
        "NAME" => $_POST['name'],
        "ACTIVE" => "Y"
    ]);

    if ($res) {
        LocalRedirect("index.php");
    } else {
        // Если не создалось — мы это увидим
        echo "<div style='color:red'>Ошибка: " . $el->LAST_ERROR . "</div>";
    }
}
?>
<form method="POST" style="padding: 20px;">
    <input type="text" name="name" placeholder="Название процедуры" required>
    <button type="submit">Добавить</button>
</form>