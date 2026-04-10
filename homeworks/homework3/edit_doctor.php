<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use App\Models\Lists\DoctorsTable;
use App\Models\Lists\ProceduresTable;

$el = new CIBlockElement;
$doctorId = (int)$_GET['ID'];
$doctorData = ['CODE' => '', 'F' => '', 'I' => '', 'O' => '', 'SERVICES' => []];

if ($doctorId > 0) {
    $res = CIBlockElement::GetByID($doctorId)->Fetch();
    if ($res) {
        $doctorData['CODE'] = $res['CODE'];
        $nameParts = explode(' ', $res['NAME']);
        $doctorData['F'] = $nameParts[0]; $doctorData['I'] = $nameParts[1]; $doctorData['O'] = $nameParts[2];
        
        $dbProps = CIBlockElement::GetProperty(DoctorsTable::IBLOCK_ID, $doctorId, [], ["CODE" => "SERVICES"]);
        while($p = $dbProps->Fetch()) if($p['VALUE']) $doctorData['SERVICES'][] = $p['VALUE'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['save'] == 'Y') {
    $fullName = trim($_POST['f']." ".$_POST['i']." ".$_POST['o']);
    $fields = [
        "IBLOCK_ID" => DoctorsTable::IBLOCK_ID,
        "NAME" => $fullName,
        "CODE" => $_POST['code'],
        "PROPERTY_VALUES" => ["SERVICES" => $_POST['services']]
    ];
    $doctorId > 0 ? $el->Update($doctorId, $fields) : $el->Add($fields);
    LocalRedirect("index.php");
}

$allProcs = ProceduresTable::getList(['select' => ['ID' => 'IBLOCK_ELEMENT_ID', 'NAME' => 'ELEMENT.NAME']])->fetchAll();
?>
<form method="POST" style="max-width: 500px; margin: 20px auto; text-align: center; font-family: sans-serif;">
    <h2>Данные врача</h2>
    <input type="text" name="code" placeholder="Символьный код" value="<?=$doctorData['CODE']?>" style="width:100%; padding:10px; margin-bottom:10px;">
    <input type="text" name="f" placeholder="Фамилия" value="<?=$doctorData['F']?>" required style="width:100%; padding:10px; margin-bottom:10px;">
    <input type="text" name="i" placeholder="Имя" value="<?=$doctorData['I']?>" required style="width:100%; padding:10px; margin-bottom:10px;">
    <input type="text" name="o" placeholder="Отчество" value="<?=$doctorData['O']?>" style="width:100%; padding:10px; margin-bottom:10px;">
    <select name="services[]" multiple style="width:100%; height:150px; margin-bottom:20px;">
        <?php foreach ($allProcs as $p): ?>
            <option value="<?=$p['ID']?>" <?=in_array($p['ID'], $doctorData['SERVICES']) ? 'selected' : ''?>><?=$p['NAME']?></option>
        <?php endforeach; ?>
    </select>
    <input type="hidden" name="save" value="Y">
    <button type="submit" style="width: 100%; padding: 15px; background: #fff; border: 1px solid #000; cursor: pointer;">Сохранить</button>
</form>