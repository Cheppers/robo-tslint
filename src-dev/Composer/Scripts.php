<?php

namespace Sweetchuck\Robo\TsLint\Composer;

use Sweetchuck\GitHooks\Composer\Scripts as GitHooks;
use Composer\Script\Event;
use Symfony\Component\Process\Process;

class Scripts
{
    /**
     * @var \Composer\Script\Event
     */
    protected static $event;

    /**
     * @var \Closure
     */
    protected static $processCallbackWrapper;

    public static function postInstallCmd(Event $event): bool
    {
        static::init($event);

        GitHooks::deploy($event);
        static::npmInstall($event);

        return true;
    }

    public static function postUpdateCmd(Event $event): bool
    {
        static::init($event);

        GitHooks::deploy($event);

        return true;
    }

    public static function npmInstall(Event $event): bool
    {
        static::init($event);

        $cmdPattern = 'cd %s && npm install';
        $cmdArgs = [
            escapeshellarg('tests/_data')
        ];

        $process = new Process(vsprintf($cmdPattern, $cmdArgs));
        $exitCode = $process->run(static::$processCallbackWrapper);

        return !$exitCode;
    }

    protected static function init(Event $event)
    {
        if (static::$event) {
            return;
        }

        static::$event = $event;
        static::$processCallbackWrapper = function (string $type, string $text) {
            static::processCallback($type, $text);
        };
    }

    protected static function processCallback(string $type, string $text)
    {
        if ($type === Process::OUT) {
            static::$event->getIO()->write($text);
        } else {
            static::$event->getIO()->writeError($text);
        }
    }
}
