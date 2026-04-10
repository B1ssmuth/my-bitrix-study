<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use App\Models\Lists\DoctorsTable;
use App\Models\Lists\ProceduresTable;

$el = new CIBlockElement;
$doctorId = (int)$_GET['ID'];

$doctorData = [
    'CODE' => '',
    'SURNAME' => '',
    'NAME' => '',
    'LAST_NAME' => '',
    'SELECTED_PROCS' => []
];

// 1. ЗАГРУЗКА ДАННЫХ
if ($doctorId > 0) {
    // Получаем базовые поля (Имя и Код)
    $res = CIBlockElement::GetByID($doctorId)->Fetch();
    if ($res) {
        $doctorData['CODE'] = $res['CODE'];
        
        // Разбиваем ФИО обратно на части
        $fioParts = explode(' ', $res['NAME']);
        $doctorData['SURNAME'] = $fioParts[0] ?? '';
        $doctorData['NAME'] = $fioParts[1] ?? '';
        $doctorData['LAST_NAME'] = $fioParts[2] ?? '';
    }

    // Получаем текущие ID выбранных услуг
    $dbProps = CIBlockElement::GetProperty(17, $doctorId, array(), array("CODE" => "SERVICES"));
    while($prop = $dbProps->Fetch()) {
        if ($prop['VALUE']) {
            $doctorData['SELECTED_PROCS'][] = $prop['VALUE'];
        }
    }
}

// 2. СОХРАНЕНИЕ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['save'] == 'Y') {
    $fullName = trim($_POST['surname'] . " " . $_POST['name'] . " " . $_POST['last_name']);
    
    $fields = [
        "IBLOCK_ID" => 17,
        "NAME" => $fullName,
        "CODE" => $_POST['code'],
        "PROPERTY_VALUES" => ["SERVICES" => $_POST['services']]
    ];

    if ($doctorId > 0) {
        $el->Update($doctorId, $fields);
    } else {
        $doctorId = $el->Add($fields);
    }
    
    if ($doctorId) LocalRedirect("index.php");
}

// Получаем все доступные процедуры для списка
$allProcedures = ProceduresTable::getList(['select' => ['ID' => 'IBLOCK_ELEMENT_ID', 'NAME' => 'ELEMENT.NAME']])->fetchAll();
?>

<form method="POST" style="max-width: 500px; margin: 20px auto; text-align: center; font-family: sans-serif;">
    <h2 style="font-weight: normal;"><?= $doctorId > 0 ? "Редактирование" : "Данные" ?> врача</h2>
    
    <input type="text" name="code" placeholder="Символьный код (латиницей)" value="<?= $doctorData['CODE'] ?>" style="width: 100%; margin-bottom: 10px; padding: 10px; border: 1px solid #ccc;">
    <input type="text" name="surname" placeholder="Фамилия" value="<?= $doctorData['SURNAME'] ?>" required style="width: 100%; margin-bottom: 10px; padding: 10px; border: 1px solid #ccc;">
    <input type="text" name="name" placeholder="Имя" value="<?= $doctorData['NAME'] ?>" required style="width: 100%; margin-bottom: 10px; padding: 10px; border: 1px solid #ccc;">
    <input type="text" name="last_name" placeholder="Отчество" value="<?= $doctorData['LAST_NAME'] ?>" style="width: 100%; margin-bottom: 10px; padding: 10px; border: 1px solid #ccc;">
    
    <select name="services[]" multiple style="width: 100%; height: 200px; margin-bottom: 20px; padding: 10px; border: 1px solid #ccc;">
        <?php foreach ($allProcedures as $p): ?>
            <?php 
                // Проверяем, выбрана ли эта услуга у врача
                $selected = in_array($p['ID'], $doctorData['SELECTED_PROCS']) ? 'selected' : ''; 
            ?>
            <option value="<?= $p['ID'] ?>" <?= $selected ?>><?= $p['NAME'] ?></option>
        <?php endforeach; ?>
    </select>

    <input type="hidden" name="save" value="Y">
    <button type="submit" style="width: 100%; padding: 15px; background: #fff; border: 1px solid #000; cursor: pointer; font-size: 16px;">
        Сохранить
    </button>
</form>