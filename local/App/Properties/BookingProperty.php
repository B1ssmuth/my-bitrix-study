<?php
namespace App\Properties;

use App\Models\Lists\ProceduresTable;

class BookingProperty
{
    public static function GetUserTypeDescription()
    {
        return [
            "PROPERTY_TYPE" => "S", // Хранить будем строку
            "USER_TYPE" => "otus_doctor_booking", // Уникальный код типа свойства
            "DESCRIPTION" => "OTUS: Запись на процедуру (Попап)",
            "GetPropertyFieldHtml" => [__CLASS__, "GetPropertyFieldHtml"],
        ];
    }

    public static function GetPropertyFieldHtml($arProperty, $arValue, $strHTMLControlName)
    {
        \Bitrix\Main\UI\Extension::load(['ui.buttons', 'core', 'popup', 'ajax']);

        $procedures = ProceduresTable::getList([
            'select' => ['ID' => 'IBLOCK_ELEMENT_ID', 'NAME' => 'ELEMENT.NAME']
        ])->fetchAll();

        $html = '<div class="otus-booking-prop-wrapper" style="padding: 10px; background: #f9fafb; border: 1px solid #eef2f4; border-radius: 4px;">';
        $html .= '<h5 style="margin: 0 0 10px 0; color: #4b5563;">Связанные процедуры (кликните для записи):</h5>';
        $html .= '<div style="display: flex; flex-wrap: wrap; gap: 8px;">';

        foreach ($procedures as $proc) {
            $html .= sprintf(
                '<button type="button" class="ui-btn ui-btn-xs ui-btn-light-border" 
                         onclick="initOtusBookingPopup(%d, \'%s\')">
                    %s
                 </button>',
                (int)$proc['ID'],
                htmlspecialchars($proc['NAME'], ENT_QUOTES),
                htmlspecialchars($proc['NAME'])
            );
        }

        $html .= '</div></div>';

        $html .= '
        <script>
        function initOtusBookingPopup(procId, procName) {
            var doctorIdField = document.querySelector(\'input[name="ID"]\');
            var doctorId = doctorIdField ? doctorIdField.value : 0;

            var contentHtml = \'<div style="padding: 15px; font-family: sans-serif;">\' +
                \'<p><strong>Процедура:</strong> \' + procName + \'</p>\' +
                \'<div style="margin-bottom: 12px;">\' +
                    \'<label style="display:block; margin-bottom:5px;">ФИО Пациента:</label>\' +
                    \'<input type="text" id="otus_patient_name" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">\' +
                \'</div>\' +
                \'<div style="margin-bottom: 15px;">\' +
                    \'<label style="display:block; margin-bottom:5px;">Дата и время записи:</label>\' +
                    \'<input type="datetime-local" id="otus_visit_date" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">\' +
                \'</div>\' +
                \'<div id="otus_booking_error" style="color:red; margin-bottom:10px; font-weight:bold;"></div>\' +
            \'</div>\';

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

                                // Ajax-запрос к обработчику создания брони (напишем на следующем шаге)
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
                                    errorDiv.innerText = "Серверная ошибка или это время занято!";
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
        }
        </script>';

        return $html;
    }
}