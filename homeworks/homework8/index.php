<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("ДЗ №8 — Модификация интерфейса");

$jsPath = '/local/js/timeman_modifier.js';
$jsFileExists = file_exists($_SERVER['DOCUMENT_ROOT'] . $jsPath);
?>

<div class="ui-slider-no-padding" style="padding: 20px; max-width: 800px; background: #fff; border-radius: 4px; margin: 20px auto; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    
    <h2 style="color: #2fc6f6; margin-bottom: 20px;">📘 Результат выполнения ДЗ №8</h2>
    <p style="font-size: 15px; color: #555; line-height: 1.6;">
        <strong>Цель задания:</strong> Закрепить способы подключения произвольного JS кода без редактирования стандартных шаблонов Битрикс24, а также научиться перехватывать системные интерфейсные события на стороне клиента.
    </p>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr style="background: #f9f9f9;">
            <td style="padding: 10px; border: 1px solid #eee; font-weight: bold; width: 30%;">Метод подключения:</td>
            <td style="padding: 10px; border: 1px solid #eee; color: #2e7d32;">Глобальный перехват через <code>onPageStart</code> в <code>init.php</code></td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #eee; font-weight: bold;">Путь к скрипту:</td>
            <td style="padding: 10px; border: 1px solid #eee;"><code><?= $jsPath ?></code></td>
        </tr>
        <tr style="background: #f9f9f9;">
            <td style="padding: 10px; border: 1px solid #eee; font-weight: bold;">Статус файла на сервере:</td>
            <td style="padding: 10px; border: 1px solid #eee;">
                <? if ($jsFileExists): ?>
                    <span style="background: #e8f5e9; color: #2e7d32; padding: 3px 8px; border-radius: 3px; font-size: 13px; font-weight: bold;">✓ Файл активен и загружен</span>
                <? else: ?>
                    <span style="background: #ffebee; color: #c62828; padding: 3px 8px; border-radius: 3px; font-size: 13px; font-weight: bold;">❌ Файл не найден</span>
                <? endif; ?>
            </td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #eee; font-weight: bold;">Логика перехвата:</td>
            <td style="padding: 10px; border: 1px solid #eee; font-size: 14px; line-height: 1.4;">
                Скрипт работает на фазе погружения (<code>capture: true</code>). Он изолирует клики по кнопкам управления тайм-менеджера («Начать день», «Пауза», «Продолжить», «Завершить»), блокирует стандартные события Vue-компонентов и вызывает кастомное подтверждение через <code>BX.UI.Dialogs.MessageBox</code>.
            </td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

    <div style="background: #fff8e1; border-left: 4px solid #ffb300; padding: 15px; border-radius: 0 4px 4px 0; margin-bottom: 20px;">
        <ol style="margin: 0; padding-left: 20px; line-height: 1.8; font-size: 14px; color: #6d4c41;">
            <li>Обратите внимание на правый верхний угол экрана — там находится стандартный виджет рабочего дня Битрикс24.</li>
            <li>Кликните по нему, чтобы вызвать контекстное меню или слайдер управления временем.</li>
            <li>Попробуйте нажать на любую кнопку смены статуса: <strong>«Начать рабочий день»</strong>, <strong>«Пауза»</strong>, <strong>«Продолжить»</strong> или <strong>«Завершить»</strong>.</li>
            <li>На экране появится кастомное модальное окно с запросом на подтверждение действия.</li>
            <li>При нажатии на кнопку <strong>«Отмена»</strong> — действие блокируется, статус дня остаётся прежним. При нажатии <strong>«Подтверждаю»</strong> — скрипт прозрачно передаёт управление обратно Битриксу.</li>
        </ol>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="/crm/deal/kanban/" class="ui-btn ui-btn-md ui-btn-primary" style="background: #2fc6f6; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-weight: bold;">Перейти в CRM для теста</a>
    </div>

</div>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>