<?php

namespace Balsama\Tempbot;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class TempBotLogger
{
    private const LOGPATH = __DIR__ . '/../logs/tempbot.log';

    public static function logError(string $message): void
    {
        $log = self::init();
        $log->error($message);
    }

    public static function logNotice(string $message): void
    {
        $log = self::init();
        $log->notice($message);
    }

    public static function logDebug(string $message): void
    {
        $log = self::init();
        $log->debug($message);
    }

    private static function init(): Logger
    {
        $log = new Logger('Tempbot Log');
        $streamHandler = new StreamHandler(self::LOGPATH, Level::Warning);
        $streamHandler->setFormatter(new HtmlFormatter());
        $log->pushHandler($streamHandler);
        return $log;
    }
}