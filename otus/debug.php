<?php

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use App\Debug\Log;

Log::addLog("Проверка записи в лог", false, "otus_debug");

echo "Готово! Проверь файл в /local/logs/otus_debug";