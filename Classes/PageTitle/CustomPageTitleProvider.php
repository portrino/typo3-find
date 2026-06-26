<?php

namespace Subugoe\Find\PageTitle;

use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;

final class CustomPageTitleProvider extends AbstractPageTitleProvider
{
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
