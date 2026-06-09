<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Page\Asset;

$APPLICATION->SetTitle("Демонстрация ДЗ №7: Бронирование процедур");

// Подключаем стили и UI
Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
\Bitrix\Main\UI\Extension::load("ui.buttons");

// Жестко подключаем модуль ИБ на самом верху страницы
\Bitrix\Main\Loader::includeModule('iblock');

// Скрипт всплывающего окна
Asset::getInstance()->addString("
<script>
window.openBookingPopup = function(doctorId, procId, procName) {
    var contentHtml = '<div style=\"padding: 20px; font-family: sans-serif; min-width: 350px;\">' +
        '<p style=\"margin-bottom: 15px; font-size: 14px;\"><strong>Процедура:</strong> <span style=\"color:#2fc6f6; font-weight:bold;\">' + procName + '</span></p>' +
        '<div style=\"margin-bottom: 12px;\">' +
            '<label style=\"display:block; margin-bottom:5px; font-weight:bold; font-size:13px;\">ФИО Пациента:</label>' +
            '<input type=\"text\" id=\"popup_patient_name\" style=\"width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;\">' +
        '</div>' +
        '<div style=\"margin-bottom: 15px;\">' +
            '<label style=\"display:block; margin-bottom:5px; font-weight:bold; font-size:13px;\">Дата и время записи:</label>' +
            '<input type=\"datetime-local\" id=\"popup_visit_date\" style=\"width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;\">' +
        '</div>' +
        '<div id=\"popup_error_msg\" style=\"color:red; font-weight:bold; font-size:13px;\"></div>' +
    '</div>';

    var popup = new BX.PopupWindow('doctor_booking_modal_window', null, {
        content: contentHtml,
        titleBar: 'Новое бронирование времени',
        closeIcon: true,
        autoHide: false,
        overlay: true,
        buttons: [
            new BX.PopupWindowButton({
                text: 'Забронировать',
                className: 'ui-btn ui-btn-success',
                events: {
                    click: function() {
                        var pName = document.getElementById('popup_patient_name').value;
                        var vDate = document.getElementById('popup_visit_date').value;
                        var errDiv = document.getElementById('popup_error_msg');

                        if (!pName || !vDate) {
                            errDiv.innerText = 'Пожалуйста, заполните все поля!';
                            return;
                        }

                        errDiv.style.color = 'green';
                        errDiv.innerText = 'Сохранение...';

                        BX.ajax({
                            url: window.location.href,
                            method: 'POST',
                            dataType: 'json',
                            data: JSON.stringify({
                                action: 'create_booking',
                                patientName: pName,
                                doctorId: doctorId,
                                procedureId: procId,
                                visitDate: vDate
                            }),
                            onsuccess: function(response) {
                                if (response && response.success) {
                                    popup.close();
                                    alert('Бронирование успешно создано!');
                                    window.location.reload();
                                } else {
                                    errDiv.style.color = 'red';
                                    errDiv.innerHTML = 'Ошибка: ' + (response.message || 'не удалось сохранить');
                                }
                            },
                            onfailure: function() {
                                errDiv.style.color = 'red';
                                errDiv.innerText = 'Ошибка при отправке запроса.';
                            }
                        });
                    }
                }
            }),
            new BX.PopupWindowButton({
                text: 'Отмена',
                className: 'ui-btn ui-btn-link',
                events: { click: function() { popup.close(); } }
            })
        ]
    });

    popup.show();
};
</script>
");

// Обработчик AJAX бронирования (ИБ 18)
if ($_input = file_get_contents('php://input')) {
    $requestData = json_decode($_input, true);
    if ($requestData['action'] === 'create_booking') {
        $APPLICATION->RestartBuffer();
        
        $bitrixDate = "";
        if (!empty($requestData['visitDate'])) {
            try {
                $objDateTime = new \Bitrix\Main\Type\DateTime($requestData['visitDate'], 'Y-m-d\TH:i');
                $bitrixDate = $objDateTime->toString(); 
            } catch (\Exception $e) {
                $bitrixDate = $requestData['visitDate'];
            }
        }

        $el = new \CIBlockElement;
        
        $propValues = [
            67 => intval($requestData['doctorId']),       
            68 => intval($requestData['procedureId']), 
            69 => $bitrixDate         
        ];

        $fields = [
            "IBLOCK_ID" => 18, 
            "NAME" => $requestData['patientName'], 
            "ACTIVE" => "Y",
            "PROPERTY_VALUES" => $propValues
        ];

        if ($bookingId = $el->Add($fields)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => $el->LAST_ERROR]);
        }
        die();
    }
}

// Запрашиваем врачей напрямую из ИБ 16 без учета старого ORM кэша
$res = \CIBlockElement::GetList(
    ["SORT" => "ASC"], 
    ["IBLOCK_ID" => 16, "ACTIVE" => "Y"], 
    false, 
    false, 
    ["ID", "NAME"]
);

$doctors = [];
while ($ob = $res->GetNextElement()) {
    $doctors[] = $ob->GetFields();
}
?>

<div class="container mt-4" style="font-family: sans-serif; position: relative; z-index: 100; background: #fff; padding: 20px; border-radius: 8px;">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0" style="color: #fff !important;">Демонстрация ДЗ №7: Онлайн-запись к врачам</h4>
        </div>
        <div class="card-body bg-light">
            <div class="row g-4">
                <?php if (empty($doctors)): ?>
                    <div class="col-12 text-center text-danger py-4">Врачи в инфоблоке №16 не найдены. Проверьте активность элементов.</div>
                <?php else: ?>
                    <?php foreach ($doctors as $doctor): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-secondary" style="background: #fff;">
                                <div class="card-body">
                                    <h5 class="card-title text-primary fw-bold" style="font-size: 20px; color: #0d6efd !important;">👨‍⚕️ <?= htmlspecialchars($doctor['NAME']) ?></h5>
                                    <p class="text-muted small">ID Врача: <code><?= $doctor['ID'] ?></code></p>
                                    
                                    <?php \App\Properties\BookingProperty::GetPublicViewHTML(['ELEMENT_ID' => $doctor['ID']], [], []); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div style="margin-bottom: 60px;"></div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>