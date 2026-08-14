<?php

namespace Subugoe\Find\Service;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    protected string $connectionName;

    protected LoggerInterface $logger;

    /** @var array<string, mixed> */
    protected array $requestArguments = [];

    /** @var array<string, mixed> */
    protected array $settings = [];

    /** @param array<string, mixed> $settings */
    public function initialize(string $connectionName, array $settings): void
    {
        $this->connectionName = $connectionName;
        $this->settings = $settings;
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger('find');
    }

    /**
     * @return array
     */
    /** @return array<string, mixed> */
    public function getRequestArguments(): array
    {
        return $this->requestArguments;
    }

    /** @param array<string, mixed> $requestArguments */
    public function setRequestArguments(array $requestArguments): void
    {
        $this->requestArguments = $requestArguments;
    }
}
