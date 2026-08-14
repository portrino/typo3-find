<?php

namespace Subugoe\Find\ViewHelpers\LinkedData\Renderer;

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

/**
 * @see http://www.w3.org/RDF/
 * @see http://www.w3.org/TR/REC-rdf-syntax/
 */
class RDFRenderer extends AbstractRenderer implements RendererInterface
{
    /**
     * @param array<string, array<string, array<string, array{language?: string|null, type?: string|null}|null>>> $items
     */
    public function renderItems(array $items): string
    {
        $doc = new \DOMDocument();
        $this->prefixes['rdf'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
        /** @var \DOMElement $rdf */
        $rdf = $doc->createElement($this->prefixedName('rdf:RDF'));
        $doc->appendChild($rdf);

        // loop over subjects
        foreach ($items as $subjectURI => $subjectStatements) {
            $subjectDescription = $doc->createElement($this->prefixedName('rdf:Description'));
            $subjectDescription->setAttribute($this->prefixedName('rdf:about'), $this->prefixedName($subjectURI, true));

            // loop over predicates
            foreach ($subjectStatements as $predicate => $objects) {
                // loop over objects
                foreach ($objects as $object => $properties) {
                    $predicateElement = $doc->createElement($this->prefixedName($predicate));
                    $subjectDescription->appendChild($predicateElement);

                    if ($properties === null) {
                        $objectParts = explode(':', $object, 2);
                        $objectPrefix = $this->prefixes[$objectParts[0]] ?? '';
                        if ($objectPrefix !== '' && count($objectParts) === 2) {
                            $object = $objectPrefix . $objectParts[1];
                        }

                        $predicateElement->setAttribute(
                            $this->prefixedName('rdf:resource'),
                            $this->prefixedName($object, true)
                        );
                    } else {
                        $language = $properties['language'] ?? null;
                        $type = $properties['type'] ?? null;
                        if ($language !== null && $language !== '') {
                            $predicateElement->setAttribute($this->prefixedName('xml:lang'), $language);
                        }

                        if ($type !== null && $type !== '') {
                            $predicateElement->setAttribute(
                                $this->prefixedName('rdf:datatype'),
                                $this->prefixedName($type, true)
                            );
                        }

                        $predicateElement->appendChild($doc->createTextNode($object));
                    }

                    $subjectDescription->appendChild($predicateElement);
                }
            }

            $rdf->appendChild($subjectDescription);
        }

        // Add the prefixes that are used as xmlns.
        /** @var \DOMElement $rootElement */
        $rootElement = $doc->documentElement;
        foreach (array_keys($this->usedPrefixes) as $prefix) {
            if (($this->prefixes[$prefix] ?? '') !== '') {
                $rootElement->setAttribute('xmlns:' . $prefix, $this->prefixes[$prefix]);
            }
        }

        $doc->formatOutput = true;

        $result = $doc->saveXML();
        if ($result === false) {
            throw new \RuntimeException('Unable to render RDF output.', 1755168634);
        }

        return $result;
    }

    protected function prefixedName(string $name, bool $expand = false): string
    {
        $nameParts = explode(':', $name, 2);
        $prefixValue = $this->prefixes[$nameParts[0]] ?? '';
        if ($prefixValue !== '') {
            $this->usedPrefixes[$nameParts[0]] = true;
            if ($expand && count($nameParts) > 1) {
                $name = $prefixValue . $nameParts[1];
            }
        }

        return $name;
    }
}
