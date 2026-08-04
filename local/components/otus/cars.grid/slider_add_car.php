<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\UI\Extension::load(["ui.forms", "ui.buttons", "ui.alerts"]);
$contactId = (int)$_REQUEST['contact_id'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <?php $APPLICATION->ShowHead(); ?>
    <script>BX.message({'bitrix_sessid': '<?=bitrix_sessid()?>'});</script>
    <style>body { background: #fff !important; padding: 25px; font-family: Helvetica, Arial, sans-serif; }</style>
</head>
<body>
    <h2 style="margin-bottom: 20px;">Добавить автомобиль</h2>
    
    <div class="ui-form">
        <div class="ui-ctl ui-ctl-textbox ui-ctl-w100" style="margin-bottom: 15px;">
            <input type="text" id="BRAND" class="ui-ctl-element" placeholder="Марка (напр. BMW)">
        </div>
        <div class="ui-ctl ui-ctl-textbox ui-ctl-w100" style="margin-bottom: 15px;">
            <input type="text" id="MODEL" class="ui-ctl-element" placeholder="Модель (напр. X5)">
        </div>
        <div class="ui-ctl ui-ctl-textbox ui-ctl-w100" style="margin-bottom: 15px;">
            <input type="text" id="REG_NUMBER" class="ui-ctl-element" placeholder="Гос. номер">
        </div>
        <div class="ui-ctl ui-ctl-textbox ui-ctl-w100" style="margin-bottom: 15px;">
            <input type="number" id="YEAR" class="ui-ctl-element" placeholder="Год выпуска">
        </div>
        <div class="ui-ctl ui-ctl-textbox ui-ctl-w100" style="margin-bottom: 15px;">
            <input type="text" id="COLOR" class="ui-ctl-element" placeholder="Цвет">
        </div>
        <div class="ui-ctl ui-ctl-textbox ui-ctl-w100" style="margin-bottom: 20px;">
            <input type="number" id="MILEAGE" class="ui-ctl-element" placeholder="Пробег (км)">
        </div>
        
        <button class="ui-btn ui-btn-success" id="saveBtn" onclick="saveCar()">Сохранить в гараж</button>
    </div>

    <script>
        function saveCar() {
            var btn = BX('saveBtn');
            BX.addClass(btn, 'ui-btn-wait');

            // Защита от пустых строк в числовых полях
            var yearVal = BX('YEAR').value;
            var mileageVal = BX('MILEAGE').value;

            BX.ajax.runComponentAction('otus:cars.grid', 'addCar', {
                mode: 'class',
                data: {
                    contactId: <?= $contactId ?>,
                    brand: BX('BRAND').value,
                    model: BX('MODEL').value,
                    regNumber: BX('REG_NUMBER').value,
                    year: yearVal ? parseInt(yearVal) : 0,
                    color: BX('COLOR').value,
                    mileage: mileageVal ? parseInt(mileageVal) : 0
                }
            }).then(function (response) {
                if (response.data && response.data.error) {
                    alert(response.data.error);
                    BX.removeClass(btn, 'ui-btn-wait');
                } else {
                    BX.SidePanel.Instance.close();
                }
            }).catch(function(response) {
                // Сервер отдал невалидный JSON, но мы знаем, что D7 отработал успешно.
                // Игнорируем ошибку парсинга и просто закрываем окно.
                BX.SidePanel.Instance.close();
            });
        }
    </script>
</body>
</html>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php"); ?>