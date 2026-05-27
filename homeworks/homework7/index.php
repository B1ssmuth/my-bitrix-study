<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use App\Models\Lists\DoctorsTable;
use Bitrix\Main\Page\Asset;

$APPLICATION->SetTitle("ДЗ №7: Кастомное свойство бронирования");

Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

$doctors = DoctorsTable::getList([
    'select' => ['ID' => 'IBLOCK_ELEMENT_ID', 'NAME' => 'ELEMENT.NAME']
])->fetchAll();
?>

<div class="container mt-5" style="font-family: sans-serif;">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Демонстрация ДЗ №7: Бронирование процедур</h4>
                    <span class="badge bg-success">Модуль otus.crmtab активен</span>
                </div>
                <div class="card-body bg-light">
                    <p class="text-muted">
                        Ниже выведен список врачей из инфоблока. Внутри карточки каждого врача динамически 
                        подключается наше кастомное свойство. Кликните на процедуру, чтобы открыть окно бронирования.
                    </p>
                    
                    <div class="row g-4 mt-2">
                        <?php if (empty($doctors)): ?>
                            <div class="col-12 text-center text-danger">
                                Врачи не найдены! Пожалуйста, добавьте врачей в админке.
                            </div>
                        <?php else: ?>
                            <?php foreach ($doctors as $doctor): ?>
                                <div class="col-md-6">
                                    <div class="card h-100 border-secondary">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div>
                                                <h5 class="card-title text-primary fw-bold mb-3">
                                                    👨‍⚕️ <?= htmlspecialchars($doctor['NAME']) ?>
                                                </h5>
                                                <p class="card-text text-muted small">ID Врача в базе данных: <code><?= $doctor['ID'] ?></code></p>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <?php
                                                echo \App\Properties\BookingProperty::GetPropertyFieldHtml(
                                                    [], 
                                                    [], 
                                                    ''
                                                );
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer text-muted d-flex justify-content-between">
                    <a href="../index.php" class="btn btn-sm btn-outline-secondary">← К списку всех ДЗ</a>
                    <span class="small">Занятое время валидируется на стороне бэкенда API</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
BX.ready(function() {
    window.initOtusBookingPopup = function(procId, procName) {
        var button = event.target;
        var cardBody = button.closest('.card-body');
        var doctorIdCode = cardBody.querySelector('code');
        var doctorId = doctorIdCode ? doctorIdCode.innerText : 0;

        var contentHtml = '<div style="padding: 15px; font-family: sans-serif;">' +
            '<p><strong>Процедура:</strong> ' + procName + '</p>' +
            '<div style="margin-bottom: 12px;">' +
                '<label style="display:block; margin-bottom:5px;">ФИО Пациента:</label>' +
                '<input type="text" id="otus_patient_name" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">' +
            '</div>' +
            '<div style="margin-bottom: 15px;">' +
                '<label style="display:block; margin-bottom:5px;">Дата и время записи:</label>' +
                '<input type="datetime-local" id="otus_visit_date" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">' +
            '</div>' +
            '<div id="otus_booking_error" style="color:red; margin-bottom:10px; font-weight:bold;"></div>' +
        '</div>';

        var popup = new BX.PopupWindow("otus_booking_modal", null, {
            content: contentHtml,
            titleBar: "Новое бронирование времени",
            closeIcon: true,
            autoHide: false,
            overlay: true,
            buttons: [
                new BX.PopupWindowButton({
                    text: "Забронировать",
                    className: "ui-btn ui-btn-success",
                    events: {
                        click: function() {
                            var patientName = document.getElementById("otus_patient_name").value;
                            var visitDate = document.getElementById("otus_visit_date").value;
                            var errorDiv = document.getElementById("otus_booking_error");

                            if (!patientName || !visitDate) {
                                errorDiv.innerText = "Пожалуйста, заполните все поля!";
                                return;
                            }

                            errorDiv.innerText = "Сохранение...";

                            BX.ajax.runAction("otus:crmtab.visit.createBooking", {
                                data: {
                                    patientName: patientName,
                                    doctorId: doctorId,
                                    procedureId: procId,
                                    visitDate: visitDate
                                }
                            }).then(function(response) {
                                if (response.data.success) {
                                    popup.close();
                                    alert("Бронирование успешно создано!");
                                } else {
                                    errorDiv.innerText = "Ошибка: " + response.data.message;
                                }
                            }).catch(function(error) {
                                errorDiv.innerText = "Это время уже занято у данного врача!";
                            });
                        }
                    }
                }),
                new BX.PopupWindowButton({
                    text: "Отмена",
                    className: "ui-btn ui-btn-link",
                    events: { click: function() { popup.close(); } }
                })
            ]
        });

        popup.show();
    };
});
</script>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>