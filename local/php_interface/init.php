<?php
// Подключаем композитный автозагрузчик вендора (если есть)
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once(__DIR__ . '/../../vendor/autoload.php');
}

// Подключаем наш локальный автозагрузчик классов (папка App)
if (file_exists(__DIR__ . '/../App/autoload.php')) {
    require_once(__DIR__ . '/../App/autoload.php');
}

// Подключаем все обработчики событий системы
if (file_exists(__DIR__ . '/events.php')) {
    require_once(__DIR__ . '/events.php');
}