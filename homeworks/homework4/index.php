<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use App\Models\VisitLogTable;
use Bitrix\Main\ORM\Fields\ExpressionField;

$APPLICATION->SetTitle("ДЗ №4: Модели и связи");

$query = VisitLogTable::getList([
    'select' => [
        'ID',
        'PATIENT' => 'PATIENT_NAME',
        'DOC_NAME' => 'DOCTOR.NAME',
        'PROC_NAME' => 'PROCEDURE.NAME',
        'PRICE_WITH_TAX'
    ],
    'runtime' => [
        // Считаем цену с налогом 22%
        new ExpressionField('PRICE_WITH_TAX', '%s * 1.22', ['VISIT_PRICE'])
    ],
    'cache' => ['ttl' => 3600],
    'order' => ['ID' => 'DESC']
]);
?>

<div style="padding: 20px; font-family: sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="add_visit.php" style="
            display: inline-block;
            background: #2fc300;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        ">+ Добавить новый визит</a>
    </div>

    <table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">
        <tr style="background: #f0f0f0;">
            <th>ID</th> <th>Пациент</th> <th>Врач</th> <th>Процедура</th> <th>Цена + Налог</th>
        </tr>
        <?php while ($v = $query->fetch()): ?>
            <tr>
                <td><?=$v['ID']?></td>
                <td><?=$v['PATIENT']?></td>
                <td><?=$v['DOC_NAME']?></td>
                <td><?=$v['PROC_NAME']?></td>
                <td><b><?=number_format($v['PRICE_WITH_TAX'], 2)?> руб.</b></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>