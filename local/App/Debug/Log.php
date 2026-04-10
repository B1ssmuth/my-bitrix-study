<?php

namespace App\Debug;

/*
 * \App\Debug\Log::addLog('OnBeforeHLEAdd');
 */

use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Diag\FileExceptionHandlerLog;

class Log extends FileExceptionHandlerLog
{

    protected $customFilePath;

    /**
     * Запись в лог
     *
     * @param           $message
     * @param   false   $clear
     * @param   string  $fileName
     *
     * @return void
     */
    public static function addLog($message, bool $clear = false, string $fileName = 'custom'): void
    {
        $logFile = $_SERVER["DOCUMENT_ROOT"] . '/local/logs/' . $fileName . '.log';

        $_message = "[OTUS] " . date("d.m.Y H:i:s");
        $_message .= "\n";
        $_message .= print_r($message, true);
        $_message .= "\n";
        $_message .= "---";
        $_message .= "\n";

        if ($clear)
        {
            file_put_contents($logFile, $_message);
        }
        else
        {
            file_put_contents($logFile, $_message, FILE_APPEND);
        }
    }

    public static function cleanLog(string $fileName = 'custom') {
        $logFile = $_SERVER["DOCUMENT_ROOT"] . '/local/logs/' . $fileName;
        $logFile .= '.log';
        file_put_contents($logFile, '');
    }

    public function initialize(array $options): void
    {
        parent::initialize($options);
        
        if (isset($options['file'])) {
            $this->customFilePath = $options['file'];
        }
    }

    /**
     * Запись в лог
     *
     * @param $exception
     * @param $logType
     *
     * @return void
     */
    public function write($exception, $logType): void
    {
        $text = ExceptionHandlerFormatter::format($exception, false);
        
        $text = "[OTUS] " . str_replace("\n", "\n[OTUS] ", $text);
        
        $logFile = $_SERVER["DOCUMENT_ROOT"] . "/" . $this->customFilePath;
        
        file_put_contents($logFile, $text . "\n", FILE_APPEND);
    }

}


