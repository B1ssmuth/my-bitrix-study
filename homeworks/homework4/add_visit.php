<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use App\Models\VisitLogTable;
use App\Models\Lists\DoctorsTable;
use App\Models\Lists\ProceduresTable;

// 1. Получаем списки врачей и процедур для выпадающих списков
$docs = DoctorsTable::getList(['select' => ['ID' => 'IBLOCK_ELEMENT_ID', 'NAME' => 'ELEMENT.NAME']])->fetchAll();
$procs = ProceduresTable::getList(['select' => ['ID' => 'IBLOCK_ELEMENT_ID', 'NAME' => 'ELEMENT.NAME']])->fetchAll();

// 2. Логика сохранения
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['patient'])) {
    $result = VisitLogTable::add([
        'PATIENT_NAME' => $_POST['patient'],
        'DOCTOR_ID'    => $_POST['doctor_id'],
        'PROCEDURE_ID' => $_POST['procedure_id'],
        'VISIT_PRICE'  => $_POST['price'],
    ]);

    if ($result->isSuccess()) {
        LocalRedirect("index.php");
    } else {
        echo "Ошибка: " . implode(', ', $result->getErrorMessages());
    }
}
?>

<form method="POST" style="max-width: 400px; margin: 20px auto; font-family: sans-serif;">
    <h2>Записать пациента</h2>
    <input type="text" name="patient" placeholder="Имя пациента" required style="width:100%; padding:10px; margin-bottom:10px;">
    
    <select name="doctor_id" required style="width:100%; padding:10px; margin-bottom:10px;">
        <option value="">Выберите врача</option>
        <?php foreach($docs as $d): ?>
            <option value="<?=$d['ID']?>"><?=$d['NAME']?></option>
        <?php endforeach; ?>
    </select>

    <select name="procedure_id" required style="width:100%; padding:10px; margin-bottom:10px;">
        <option value="">Выберите процедуру</option>
        <?php foreach($procs as $p): ?>
            <option value="<?=$p['ID']?>"><?=$p['NAME']?></option>
        <?php endforeach; ?>
    </select>

    <input type="number" name="price" placeholder="Цена" required style="width:100%; padding:10px; margin-bottom:20px;">
    
    <button type="submit" style="width:100%; padding:15px; background:#000; color:#fff; cursor:pointer;">Записать</button>
</form>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>