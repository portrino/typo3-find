<?php

namespace Subugoe\Find\Service;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    protected string $connectionName;

    protected LoggerInterface $logger;

    protected array $requestArguments = [];

    protected array $settings = [];

    public function initialize(string $connectionName, array $settings): void
    {
        $this->connectionName = $connectionName;
        $this->settings = $settings;
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger('find');
    }

    /**
     * @return array
     */
    public function getRequestArguments(): array
    {
        return $this->requestArguments;
    }

    /**
     * @param array $requestArguments
     */
    public function setRequestArguments(array $requestArguments): void
    {
        $this->requestArguments = $requestArguments;
    }
}
