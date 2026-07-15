<?php
namespace App\Properties;

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Page\Asset;

class BookingProperty
{
    public static function GetUserTypeDescription()
    {
        return [
            "USER_TYPE_ID" => "doctor_booking_system",
            "USER_TYPE" => "doctor_booking_system",
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => "Интерфейс записи (ДЗ 7)",
            "PROPERTY_TYPE" => "S",
            "GetPublicViewHTML" => [__CLASS__, "GetPublicViewHTML"],
            "GetAdminListViewHTML" => [__CLASS__, "GetAdminListViewHTML"],
            "GetPropertyFieldHtml" => [__CLASS__, "GetPropertyFieldHtml"],
            // ВАЖНО: Эта строка делает свойство видимым в публичных Списках!
            "GetPublicEditHTML" => [__CLASS__, "GetPropertyFieldHtml"],
        ];
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        return '<input type="text" value="Интерфейс активен" readonly>';
    }

    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return self::GetPublicViewHTML($arProperty, $value, $strHTMLControlName);
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        $doctorId = intval($arProperty['ELEMENT_ID'] ?? 0);
        if (!$doctorId) return '';

        \Bitrix\Main\Loader::includeModule('iblock');
        Extension::load("ui.buttons");
        
        self::includeScripts();

        $resProcs = \CIBlockElement::GetList(
            ["SORT" => "ASC"], 
            ["IBLOCK_ID" => 18, "ACTIVE" => "Y"], 
            false, 
            false, 
            ["ID", "NAME"]
        );
        
        $html = '<div class="procedures-list" style="margin-top: 5px;">';
        $hasProcs = false;
        while ($proc = $resProcs->GetNext()) {
            $hasProcs = true;
            $procNameEscaped = \CUtil::JSEscape($proc['NAME']);
            
            $html .= '<button class="ui-btn ui-btn-sm ui-btn-primary" ';
            $html .= 'onclick="window.openBookingPopup(' . $doctorId . ', ' . $proc['ID'] . ', \'' . $procNameEscaped . '\'); return false;" ';
            $html .= 'style="margin: 2px; background-color: #2fc6f6; border-color: #2fc6f6; color: #fff; border-radius: 4px; font-size: 11px; padding: 4px 8px; border: none; cursor: pointer;">';
            $html .= '📅 ' . htmlspecialchars($proc['NAME']);
            $html .= '</button>';
        }

        if (!$hasProcs) {
            $html .= '<span style="color: #999; font-size: 11px;">Нет процедур</span>';
        }
        $html .= '</div>';

        return $html; 
    }

    private static function includeScripts()
    {
        static $scriptIncluded = false;
        if ($scriptIncluded) return;
        $scriptIncluded = true;

        $js = <<<JS
<script>
if (typeof window.openBookingPopup === 'undefined') {
    window.openBookingPopup = function(doctorId, procId, procName) {
        var contentHtml = '<div style="padding: 20px; font-family: sans-serif; min-width: 350px;">' +
            '<p style="margin-bottom: 15px; font-size: 14px;"><strong>Процедура:</strong> <span style="color:#2fc6f6; font-weight:bold;">' + procName + '</span></p>' +
            '<div style="margin-bottom: 12px;">' +
                '<label style="display:block; margin-bottom:5px; font-weight:bold; font-size:13px;">ФИО Пациента:</label>' +
                '<input type="text" id="popup_patient_name" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">' +
            '</div>' +
            '<div style="margin-bottom: 15px;">' +
                '<label style="display:block; margin-bottom:5px; font-weight:bold; font-size:13px;">Дата и время записи:</label>' +
                '<input type="datetime-local" id="popup_visit_date" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">' +
            '</div>' +
            '<div id="popup_error_msg" style="color:red; font-weight:bold; font-size:13px;"></div>' +
        '</div>';

        var popup = new BX.PopupWindow('doctor_booking_modal_window', null, {
            content: contentHtml,
            titleBar: 'Новое бронирование',
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

                            BX.ajax.runAction('otus:crmtab.visit.createBooking', {
                                data: {
                                    patientName: pName,
                                    doctorId: doctorId,
                                    procedureId: procId,
                                    visitDate: vDate
                                }
                            }).then(function(response) {
                                popup.close();
                                alert('Успешно забронировано!');
                                var gridObject = BX.Main.gridManager.getInstanceById('b_lists_element_19');
                                if (gridObject) {
                                    gridObject.reload();
                                } else {
                                    window.location.reload();
                                }
                            }).catch(function(response) {
                                errDiv.style.color = 'red';
                                errDiv.innerText = 'Ошибка: ' + response.errors[0].message;
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
}
</script>
JS;
        Asset::getInstance()->addString($js);
    }
}