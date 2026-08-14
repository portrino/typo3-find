<?php

namespace Subugoe\Find\Controller;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2013
 *      Ingo Pfennigstorf <pfennigstorf@sub-goettingen.de>
 *      Sven-S. Porst
 *      Göttingen State and University Library
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Subugoe\Find\Service\ServiceProviderInterface;
use Subugoe\Find\Utility\ArrayUtility;
use Subugoe\Find\Utility\FrontendUtility;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\ArrayUtility as CoreArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Page\PageInformation;

class SearchController extends ActionController
{
    /** @var array<string, mixed> */
    protected array $requestArguments = [];

    protected ?ServiceProviderInterface $searchProvider = null;

    /**
     * @throws NoSuchArgumentException
     */
    public function detailAction(string $id): ResponseInterface
    {
        $arguments = $this->searchProvider->getRequestArguments();
        $detail = $this->searchProvider->getDocumentById($id);

        $underlyingQueryInfo = $arguments['underlyingQuery'] ?? null;
        if (is_array($underlyingQueryInfo) && array_key_exists('q', $underlyingQueryInfo) && isset($underlyingQueryInfo['position'])) {
            FrontendUtility::addQueryInformationAsJavaScript(
                $underlyingQueryInfo['q'],
                $this->settings,
                (int)$underlyingQueryInfo['position'],
                $arguments
            );
        }

        $this->addStandardAssignments();

        $this->view->assignMultiple($detail);
        $this->view->assignMultiple([
            'arguments' => $arguments,
            'config' => $this->searchProvider->getConfiguration(),
        ]);

        return $this->htmlResponse();
    }

    /**
     * Citation Action.
     */
    public function citationAction(): ResponseInterface
    {
        $arguments = $this->requestArguments;
        $detail = $this->searchProvider->getDocumentById($arguments['id']);

        $this->addStandardAssignments();

        $this->view->assignMultiple($detail);
        $this->view->assignMultiple([
            'arguments' => $arguments,
            'config' => $this->searchProvider->getConfiguration(),
            'type' => $arguments['type'],
        ]);

        return $this->htmlResponse();
    }

    /**
     * Index Action.
     */
    public function indexAction(): ResponseInterface
    {
        if (array_key_exists('id', $this->requestArguments)) {
            return new ForwardResponse('detail');
        }

        if (array_key_exists('rsn', $this->requestArguments)) {
            return new ForwardResponse('redirect');
        }

        if (array_key_exists('bc', $this->requestArguments)) {
            return new ForwardResponse('redirect');
        }

        if (array_key_exists('ppn', $this->requestArguments)) {
            return new ForwardResponse('redirect');
        }

        if (array_key_exists('oclc', $this->requestArguments)) {
            return new ForwardResponse('redirect');
        }

        $this->searchProvider->setCounter();
        FrontendUtility::addQueryInformationAsJavaScript(
            $this->searchProvider->getRequestArguments()['q'] ?? [],
            $this->settings,
            null,
            $this->searchProvider->getRequestArguments()
        );

        $this->addStandardAssignments();
        $defaultQuery = $this->searchProvider->getDefaultQuery();

        // Decode facet keys for display
        $arguments = $this->searchProvider->getRequestArguments();
        if (isset($arguments['facet']) && is_array($arguments['facet'])) {
            foreach ($arguments['facet'] as $facetId => $facetTerms) {
                if (is_array($facetTerms)) {
                    $decodedTerms = [];
                    foreach ($facetTerms as $term => $value) {
                        $decodedTerm = urldecode($term);
                        $decodedTerms[$decodedTerm] = $value;
                    }

                    $arguments['facet'][$facetId] = $decodedTerms;
                }
            }
        }

        $viewValues = [
            'arguments' => $arguments,
            'config' => $this->searchProvider->getConfiguration(),
        ];

        CoreArrayUtility::mergeRecursiveWithOverrule($viewValues, $defaultQuery);
        $this->view->assignMultiple($viewValues);

        // if there are no search parameters provided, redirect to the URL given in setting 'nosearchRedirect'
        $noSearch = (bool)($defaultQuery['noSearch'] ?? false);
        $nosearchRedirect = $this->settings['nosearchRedirect'] ?? null;
        if ($noSearch && is_string($nosearchRedirect) && $nosearchRedirect !== '') {
            $response = GeneralUtility::makeInstance(ResponseFactoryInterface::class)->createResponse((int)\TYPO3\CMS\Core\Utility\HttpUtility::HTTP_STATUS_303)->withAddedHeader('location', $nosearchRedirect);
            throw new \TYPO3\CMS\Core\Http\PropagateResponseException($response, 8491898626);
        }

        return $this->htmlResponse();
    }

    /**
     * Redirect View to detail action.
     */
    public function redirectAction(): void
    {
        $queryArguments = ['q' => []];
        $queryArgumentsDefault = '';

        if (array_key_exists('rsn', $this->requestArguments)) {
            $queryArguments['q']['rsn'] = $this->requestArguments['rsn'];
            $queryArgumentsDefault = $this->requestArguments['rsn'];
        } elseif (array_key_exists('bc', $this->requestArguments)) {
            $queryArguments['q']['barcode'] = $this->requestArguments['bc'];
            $queryArgumentsDefault = $this->requestArguments['bc'];
        } elseif (array_key_exists('ppn', $this->requestArguments)) {
            $queryArguments['q']['ppn'] = $this->requestArguments['ppn'];
            $queryArgumentsDefault = $this->requestArguments['ppn'];
        } elseif (array_key_exists('oclc', $this->requestArguments)) {
            $queryArguments['q']['oclc'] = $this->requestArguments['oclc'];
            $queryArgumentsDefault = $this->requestArguments['oclc'];
        }

        $selectResults = $this->searchProvider->search($queryArguments);

        if (count($selectResults) === 1) {
            $resultSet = $selectResults->getDocuments();

            $arguments = [
                'tx_find_find' => [
                    'action' => 'detail',
                    'controller' => 'Search',
                    'id' => $resultSet[0]['id'],
                ],
            ];
        } else {
            $arguments = [
                'tx_find_find' => [
                    'action' => 'index',
                    'controller' => 'Search',
                    'q' => [
                        'default' => $queryArgumentsDefault,
                    ],
                ],
            ];
        }

        $uri = $this->uriBuilder->reset()->setTargetPageUid($this->request->getAttribute('frontend.page.information')->getId())->setCreateAbsoluteUri(true)->setArguments($arguments)->build();

        $response = GeneralUtility::makeInstance(ResponseFactoryInterface::class)->createResponse((int)\TYPO3\CMS\Core\Utility\HttpUtility::HTTP_STATUS_303)->withAddedHeader('location', $uri);
        throw new \TYPO3\CMS\Core\Http\PropagateResponseException($response, 6097036578);
    }

    /**
     * Initialisation and setup.
     */
    protected function initializeAction(): void
    {
        ksort($this->settings['queryFields']);

        $this->initializeConnection($this->settings['activeConnection']);

        $this->requestArguments = $this->request->getArguments();
        $this->requestArguments = ArrayUtility::cleanArgumentsArray($this->requestArguments);

        $this->searchProvider->setRequestArguments($this->requestArguments);
        $this->searchProvider->setAction($this->request->getControllerActionName());
        $this->searchProvider->setControllerExtensionKey($this->request->getControllerExtensionKey());
    }

    /**
     * Suggest/Autocomplete action.
     */
    public function suggestAction(): ResponseInterface
    {
        $results = $this->searchProvider->suggestQuery($this->searchProvider->getRequestArguments());
        $this->view->assign('suggestions', $results);

        return $this->htmlResponse();
    }

    /**
     * Query indexed terms for given fields.
     */
    public function termAction(): ResponseInterface
    {
        $results = $this->searchProvider->getTerms($this->searchProvider->getRequestArguments());
        $this->view->assign('terms', $results);

        return $this->htmlResponse();
    }

    /**
     * Assigns standard variables to the view.
     */
    protected function addStandardAssignments(): void
    {
        $contentObject = $this->request->getAttribute('currentContentObject');
        $siteLanguage = $this->request->getAttribute('language');

        if (!$siteLanguage instanceof SiteLanguage) {
            $site = $this->request->getAttribute('site');

            if ($site instanceof Site) {
                $siteLanguage = $site->getDefaultLanguage();
            }
        }

        $this->searchProvider->setConfigurationValue('extendedSearch', $this->searchProvider->isExtendedSearch());
        $this->searchProvider->setConfigurationValue(
            'uid',
            $contentObject instanceof ContentObjectRenderer ? (int)($contentObject->data['uid'] ?? 0) : 0
        );
        $this->searchProvider->setConfigurationValue('prefixID', 'tx_find_find');

        $pageTitle = '';
        $pageInformation = $this->request->getAttribute('frontend.page.information');
        if ($pageInformation instanceof PageInformation) {
            $pageRecord = $pageInformation->getPageRecord();
            $pageTitle = (string)($pageRecord['title'] ?? '');
        }

        $this->searchProvider->setConfigurationValue('pageTitle', $pageTitle);

        $this->searchProvider->setConfigurationValue(
            'language',
            $siteLanguage instanceof SiteLanguage ? $siteLanguage->getTypo3Language() : ''
        );
    }

    /**
     * @param string $activeConnection
     */
    protected function initializeConnection(string $activeConnection): void
    {
        $connectionConfiguration = $this->settings['connections'][$activeConnection];

        /** @var class-string<ServiceProviderInterface> $providerClass */
        $providerClass = $connectionConfiguration['provider'];
        $this->searchProvider = GeneralUtility::makeInstance($providerClass);
        $this->searchProvider->initialize($activeConnection, $this->settings);
        $this->searchProvider->connect();
    }
}
