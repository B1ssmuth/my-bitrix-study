<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Page\Asset;

$APPLICATION->SetTitle("ДЗ №9: Кастомное активити для БП");

Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
?>

<div class="container mt-4" style="font-family: sans-serif; position: relative; z-index: 100;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0" style="color: #fff !important;">Демонстрация ДЗ №9: Интеграция БП с DaData API</h4>
        </div>
        <div class="card-body bg-light">
            
            <div class="alert alert-info border-0 shadow-sm mb-4">
                <h5 class="alert-heading fw-bold">🎯 Суть задания</h5>
                <p class="mb-0">
                    Разработать собственное действие (активити) для дизайнера бизнес-процессов Битрикс24. <br>
                    Принимая <strong>ИНН</strong>, активити обращается к внешнему API <strong>DaData.ru</strong>, получает название и юридический адрес компании, а затем передает их дальше по цепочке БП для автоматического создания карточки компании в CRM и привязки её к исходному документу.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100 border-secondary" style="background: #fff;">
                        <div class="card-body">
                            <h5 class="card-title text-primary fw-bold" style="color: #0d6efd !important;">🛠 Что было реализовано</h5>
                            <ul class="list-group list-group-flush mt-3">
                                <li class="list-group-item bg-transparent">
                                    ✅ Создан класс <code>CBPDadataActivity</code> для отправки HTTP-запросов к API.
                                </li>
                                <li class="list-group-item bg-transparent">
                                    ✅ Разработан нативный интерфейс настроек действия, устойчивый к багам JS-ядра визуального редактора Битрикс24.
                                </li>
                                <li class="list-group-item bg-transparent">
                                    ✅ Настроен бизнес-процесс для ИБ <strong>«Реестр работ»</strong> с автозапуском.
                                </li>
                                <li class="list-group-item bg-transparent">
                                    ✅ Выстроена цепочка БП: <em>Активити DaData ➔ Создание Компании в CRM ➔ Изменение документа (привязка ID)</em>.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border-success" style="background: #fff;">
                        <div class="card-body">
                            <h5 class="card-title text-success fw-bold" style="color: #198754 !important;">🚀 Как протестировать</h5>
                            <ol class="mt-3" style="line-height: 1.6;">
                                <li>Перейдите в раздел <a href="/bizproc/processes/" target="_blank">Процессы</a> (или Списки) ➔ <strong>Реестр работ</strong>.</li>
                                <li>Нажмите кнопку <strong>«Добавить элемент»</strong>.</li>
                                <li>Заполните базовые поля (Название, Вид работ, Сумма).</li>
                                <li>В поле <strong>«Заказчик ИНН»</strong> укажите реальный ИНН (например, Яндекс: <code>7736207543</code>).</li>
                                <li>Оставьте поле <strong>«Заказчик»</strong> (привязка к CRM) пустым.</li>
                                <li>Нажмите <strong>«Сохранить»</strong>.</li>
                            </ol>
                            <p class="text-muted small mt-2">
                                <em>Бизнес-процесс мгновенно отработает в фоне. Обновите страницу созданного элемента — в поле «Заказчик» автоматически появится привязка к новой карточке CRM.</em>
                            </p>
                            <a href="/bizproc/processes/" class="btn btn-success w-100 fw-bold mt-2" target="_blank" style="background-color: #198754; border-color: #198754; color: #fff;">Перейти в Реестр работ</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-center">
                <a href="/bitrix/admin/fileman_file_edit.php?path=%2Flocal%2Factivities%2Fcustom%2Fdadataactivity%2Fdadataactivity.php&full_src=Y" target="_blank" class="btn btn-outline-secondary btn-sm">
                    👀 Посмотреть код активити (dadataactivity.php)
                </a>
            </div>

        </div>
    </div>
</div>

<div style="margin-bottom: 60px;"></div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>