<?php

namespace Subugoe\Find\ViewHelpers\Find;

/*******************************************************************************
 * Copyright notice
 *
 * Copyright 2013 Sven-S. Porst, Göttingen State and University Library
 *                <porst@sub.uni-goettingen.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 ******************************************************************************/
use Solarium\QueryType\Select\Result\Document;
use Solarium\QueryType\Select\Result\Result;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View Helper for styling the content of index document’s result fields.
 * Requires the query result object for finding the information as well as the
 * document and the field to work on.
 *
 * Expects to find the document’s id in the field »id« which can be overridden
 * using the »idKey« parameter.
 *
 * Tries to avoid issues with creating invalid markup by assuming the highlighted
 * parts of the string are marked by Unicode Private Use Area characters
 * \ueeee and \ueeef. Then replaces these by tags for an em.highlight element.
 * The highlighting tags can be configured using the highlightTagOpen and
 * highlightTagClose arguments.
 */
class HighlightFieldViewHelper extends AbstractViewHelper
{
    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Registers own arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('results', Result::class, 'Query results', true);
        $this->registerArgument(
            'document',
            Document::class,
            'Result document to work on',
            true
        );
        $this->registerArgument('field', 'string', 'name of field in document to highlight', true);
        $this->registerArgument(
            'alternateField',
            'string',
            'name of alternate field in document to use for highlighting',
            false,
            null
        );
        $this->registerArgument(
            'index',
            'int',
            'if the field is an array: index of the single element to highlight',
            false
        );
        $this->registerArgument('idKey', 'string', 'name of the field in document that is its ID', false, 'id');
        $this->registerArgument(
            'highlightTagOpen',
            'string',
            'opening tag to insert to begin highlighting',
            false,
            '<em class="highlight">'
        );
        $this->registerArgument(
            'highlightTagClose',
            'string',
            'closing tag to insert to end highlighting',
            false,
            '</em>'
        );
        $this->registerArgument('raw', 'boolean', 'whether to not HTML escape the output', false, false);
    }

    /**
     * @param array{
     *     results: Result,
     *     document: Document,
     *     field: string,
     *     alternateField?: string|null,
     *     index?: int|null,
     *     idKey: string,
     *     highlightTagOpen: string,
     *     highlightTagClose: string,
     *     raw: bool
     * } $arguments
     * @return array<int, string>|string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
    ): array|string {
        $fields = $arguments['document']->getFields();
        $fieldContent = $fields[$arguments['field']];
        $selectedIndex = $arguments['index'] ?? null;
        if ($selectedIndex !== null) {
            if (is_array($fieldContent) && count($fieldContent) > $selectedIndex) {
                $fieldContent = $fieldContent[$selectedIndex];
            }

            // TODO: error message
        }

        return self::highlightField($fieldContent, $arguments);
    }

    /**
     * Returns string or array of strings with highlighted areas enclosed
     * by \ueeee and \ueeef.
     *
     * @param array{
     *     results: Result,
     *     document: Document,
     *     field: string,
     *     alternateField?: string|null,
     *     index?: int|null,
     *     idKey: string,
     *     highlightTagOpen: string,
     *     highlightTagClose: string,
     *     raw: bool
     * } $arguments
     * @param array<int, string>|string $fieldContent
     * @return array<int, string>|string
     */
    protected static function highlightField(array|string $fieldContent, array $arguments): array|string
    {
        $highlightInfo = self::getHighlightInfo($arguments);

        if (is_array($fieldContent)) {
            $result = [];
            foreach ($fieldContent as $singleField) {
                $result[] = self::highlightSingleField($singleField, $highlightInfo, $arguments);
            }
        } else {
            $result = self::highlightSingleField($fieldContent, $highlightInfo, $arguments);
        }

        return $result;
    }

    /**
     * Returns highlight information for the document and field configured in
     * our arguments.
     *
     * @param array{
     *     results: Result,
     *     document: Document,
     *     field: string,
     *     alternateField?: string|null,
     *     index?: int|null,
     *     idKey: string,
     *     highlightTagOpen: string,
     *     highlightTagClose: string,
     *     raw: bool
     * } $arguments
     * @return array<int, string>
     */
    protected static function getHighlightInfo(array $arguments): array
    {
        $highlightInfo = [];
        $documentID = $arguments['document'][$arguments['idKey']] ?? null;
        if ($documentID !== null && $documentID !== '') {
            $highlighting = $arguments['results']->getHighlighting();

            if ($highlighting !== null) {
                $alternateField = $arguments['alternateField'] ?? null;
                if ($alternateField !== null && $alternateField !== '') {
                    $highlightInfo += $highlighting->getResult((string)$documentID)->getField($alternateField);
                } else {
                    $highlightInfo += $highlighting->getResult((string)$documentID)->getField($arguments['field']);
                }
            }
        }

        return $highlightInfo;
    }

    /**
     * Returns $fieldString with highlighted areas enclosed by \ueeee and \ueeef.
     *
     * @param array<int, string> $highlightInfo
     * @param array{
     *     results: Result,
     *     document: Document,
     *     field: string,
     *     alternateField?: string|null,
     *     index?: int|null,
     *     idKey: string,
     *     highlightTagOpen: string,
     *     highlightTagClose: string,
     *     raw: bool
     * } $arguments
     */
    protected static function highlightSingleField(string $fieldString, array $highlightInfo, array $arguments): string
    {
        $result = null;

        foreach ($highlightInfo as $highlightItem) {
            $highlightItemStripped = str_replace(['\ueeee', '\ueeef'], ['', ''], $highlightItem);
            if (strpos($fieldString, $highlightItemStripped) !== false) {
                // HTML escape the text here if not explicitly configured to not do so.
                // Use f:format.raw in the template to avoid double escaping the HTML tags.
                if (!$arguments['raw']) {
                    $highlightItem = htmlspecialchars($highlightItem, ENT_QUOTES);
                }

                $highlightItemMarkedUp = str_replace(
                    ['\ueeee', '\ueeef'],
                    [$arguments['highlightTagOpen'], $arguments['highlightTagClose']],
                    $highlightItem
                );
                $result = str_replace($highlightItemStripped, $highlightItemMarkedUp, $fieldString);
                break;
            }
        }

        // If no highlighted string is present, use the original one.
        if ($result === null) {
            $result = $arguments['raw'] ? $fieldString : htmlspecialchars($fieldString, ENT_QUOTES);
        }

        return $result;
    }
}
