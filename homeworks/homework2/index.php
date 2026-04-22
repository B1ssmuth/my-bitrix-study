<?

use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("ДЗ №2: Отладка и логирование");

Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');


?>

<h4 class="mb-3">Пояснительная записка</h4>
<div class="alert alert-light border shadow-sm p-4">
    <h3 class="mb-4 text-primary">Отчет о выполнении ДЗ №2</h3>

    <section class="mb-4">
        <h5 class="fw-bold text-secondary">Часть 1: Логирование через HTTP-запрос</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item bg-transparent">
                <strong>Скрипт:</strong> Создан файл <code>/otus/debug.php</code>, доступный по прямому HTTP-запросу.
            </li>
            <li class="list-group-item bg-transparent">
                <strong>Механизм:</strong> Реализована фиксация текущей даты и времени при каждом обращении.
            </li>
            <li class="list-group-item bg-transparent">
                <strong>Хранение:</strong> Запись осуществляется через статический метод кастомного класса в лог-файл <code>local/logs/otus_debug.log</code>.
            </li>
        </ul>
    </section>

    <section>
        <h5 class="fw-bold text-secondary">Часть 2: Кастомный системный логгер</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item bg-transparent">
                <strong>Архитектура:</strong> Разработан класс <code>\App\Debug\Log</code>, наследующий стандартный <code>FileExceptionHandlerLog</code>.
            </li>
            <li class="list-group-item bg-transparent">
                <strong>Инициализация:</strong> Переопределен метод <code>initialize</code> для динамического получения пути к лог-файлу из системного конфига.
            </li>
            <li class="list-group-item bg-transparent">
                <strong>Форматирование:</strong> Модифицирован метод <code>write</code> — теперь каждая строка системного лога автоматически получает префикс <strong>[OTUS]</strong>.
            </li>
            <li class="list-group-item bg-transparent">
                <strong>Интеграция:</strong> Класс зарегистрирован в файле <code>.settings.php</code> (секция <code>exception_handling</code>), что позволило полностью делегировать обработку исключений Битрикса нашему кастомному решению.
            </li>
        </ul>
    </section>
</div>
<br>
<br>
<hr>

<h4 class="mb-3">Часть 1 - Logger</h4>
<ul class="list-group">
    <li class="list-group-item">
        <a href="/local/logs/otus_debug.log">Файл лога из п1 ДЗ</a>
    </li>
    <li class="list-group-item">
        <a href="/otus/debug.php">Добавление в лог из п1 ДЗ</a>
    </li>
    <li class="list-group-item">
        <a href="clearlog.php">Очистить лог из п1 ДЗ</a>
    </li>
    <li class="list-group-item">
        <a href="/bitrix/admin/fileman_file_edit.php?path=%2Fotus%2Fdebug.php&full_src=Y">Файл с классом кастомного логгера</a>
    </li>
</ul>


<h4 class="mb-3 mt-5">Часть 2 - Exception</h4>
<ul class="list-group">
    <li class="list-group-item">
        <a href="/local/logs/exceptions.log">Файл лога из п2 ДЗ</a>
    </li>
    <li class="list-group-item">
        <a href="writeexception.php">Добавление в лог из п2 ДЗ</a>
    </li>
    <li class="list-group-item">
        <a href="clearexception.php">Очистить лог из п2 ДЗ</a>
    </li>
    <li class="list-group-item">
        <a href="/bitrix/admin/fileman_file_edit.php?path=%2Flocal%2FApp%2FDebug%2FLog.php&full_src=Y">Файл с классом системного исключений</a>
    </li>
</ul>



<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>