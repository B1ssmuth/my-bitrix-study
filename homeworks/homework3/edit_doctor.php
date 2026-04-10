<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Loader;
use App\Models\Lists\DoctorsTable;
use App\Models\Lists\ProceduresTable;

Loader::includeModule('iblock');

$el = new CIBlockElement;
$doctorId = (int)$_GET['ID'];

$doctorData = ['CODE' => '', 'SURNAME' => '', 'NAME' => '', 'LAST_NAME' => '', 'SELECTED_PROCS' => []];

if ($doctorId > 0) {
    $res = CIBlockElement::GetByID($doctorId)->Fetch();
    if ($res) {
        $doctorData['CODE'] = $res['CODE'];
        $fioParts = explode(' ', $res['NAME']);
        $doctorData['SURNAME'] = $fioParts[0] ?? '';
        $doctorData['NAME'] = $fioParts[1] ?? '';
        $doctorData['LAST_NAME'] = $fioParts[2] ?? '';
    }

    $dbProps = CIBlockElement::GetProperty(DoctorsTable::IBLOCK_ID, $doctorId, [], ["CODE" => "SERVICES"]);
    while($prop = $dbProps->Fetch()) {
        if ($prop['VALUE']) $doctorData['SELECTED_PROCS'][] = $prop['VALUE'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['save'] == 'Y') {
    $fullName = trim($_POST['surname'] . " " . $_POST['name'] . " " . $_POST['last_name']);
    
    $fields = [
        "IBLOCK_ID" => DoctorsTable::IBLOCK_ID,
        "NAME" => $fullName,
        "CODE" => $_POST['code'],
        "PROPERTY_VALUES" => ["SERVICES" => $_POST['services']]
    ];

    $res = ($doctorId > 0) ? $el->Update($doctorId, $fields) : $el->Add($fields);
    if ($res) LocalRedirect("index.php");
}

$allProcedures = ProceduresTable::getList(['select' => ['ID' => 'IBLOCK_ELEMENT_ID', 'NAME' => 'ELEMENT.NAME']])->fetchAll();
?>

<div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: sans-serif;">
    <form method="POST">
        <h2 style="text-align: center;">Данные врача</h2>
        
        <div style="margin-bottom: 15px;">
            <label>Символьный код (латиницей):</label><br>
            <input type="text" name="code" value="<?= $doctorData['CODE'] ?>" style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Фамилия:</label><br>
            <input type="text" name="surname" value="<?= $doctorData['SURNAME'] ?>" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Имя:</label><br>
            <input type="text" name="name" value="<?= $doctorData['NAME'] ?>" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Отчество:</label><br>
            <input type="text" name="last_name" value="<?= $doctorData['LAST_NAME'] ?>" style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label>Доступные услуги:</label><br>
            <select name="services[]" multiple style="width: 100%; height: 150px; padding: 8px;">
                <?php foreach ($allProcedures as $p): ?>
                    <option value="<?= $p['ID'] ?>" <?= in_array($p['ID'], $doctorData['SELECTED_PROCS']) ? 'selected' : '' ?>>
                        <?= $p['NAME'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <input type="hidden" name="save" value="Y">
        <button type="submit" style="width: 100%; padding: 15px; background: #fff; border: 1px solid #000; cursor: pointer; font-size: 16px;">
            Сохранить
        </button>
        
        <p style="text-align: center; margin-top: 15px;">
            <a href="index.php" style="color: #666;">← Назад к списку</a>
        </p>
    </form>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>