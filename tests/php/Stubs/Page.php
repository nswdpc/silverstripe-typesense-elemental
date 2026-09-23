<?php

declare(strict_types=1);

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\TestOnly;

class Page extends SiteTree implements TestOnly
{
    private static string $table_name = 'NSWDPC_Tests_Page';
}
