<?php

declare(strict_types=1);

namespace Subugoe\Find\SignalSlot;

use TYPO3\CMS\Core\Utility\GeneralUtility;

final class Dispatcher
{
    /**
     * @var array<string, array<int, array{slotClassName:string, slotMethodName:string, passSignalInformation:bool}>>
     */
    private static array $connections = [];

    public function connect(
        string $signalClassName,
        string $signalName,
        string $slotClassName,
        string $slotMethodName,
        bool $passSignalInformation = false,
    ): void {
        $key = $this->buildKey($signalClassName, $signalName);

        self::$connections[$key][] = [
            'slotClassName' => $slotClassName,
            'slotMethodName' => $slotMethodName,
            'passSignalInformation' => $passSignalInformation,
        ];
    }

    /**
     * @param array<int, mixed> $signalArguments
     */
    public function dispatch(string $signalClassName, string $signalName, array $signalArguments = []): void
    {
        $key = $this->buildKey($signalClassName, $signalName);

        foreach (self::$connections[$key] ?? [] as $connection) {
            /** @var class-string<object> $slotClassName */
            $slotClassName = $connection['slotClassName'];
            $slotInstance = GeneralUtility::makeInstance($slotClassName);
            $arguments = $signalArguments;

            if ($connection['passSignalInformation']) {
                $arguments[] = [
                    'class' => $signalClassName,
                    'signal' => $signalName,
                ];
            }

            $methodName = $connection['slotMethodName'];
            if (!is_callable([$slotInstance, $methodName])) {
                throw new \RuntimeException(
                    sprintf('Configured slot method "%s::%s" is not callable.', $slotClassName, $methodName),
                    1755168631
                );
            }

            call_user_func_array([$slotInstance, $methodName], $arguments);
        }
    }

    private function buildKey(string $signalClassName, string $signalName): string
    {
        return $signalClassName . '::' . $signalName;
    }
}
