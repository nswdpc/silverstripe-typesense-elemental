<?php

namespace NSWDPC\Typesense\Elemental\Tests;

use NSWDPC\Typesense\CMS\Models\TypesenseSearchPage;
use NSWDPC\Typesense\Elemental\Models\Elements\TypesenseAdvancedSearchElement;
use NSWDPC\Typesense\Elemental\Tests\Support\ScaffoldingTrait;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Dev\SapphireTest;

require_once __DIR__ . '/Support/ScaffoldingTrait.php';

class TypesenseAdvancedSearchElementTest extends SapphireTest
{
    use ScaffoldingTrait;

    protected $usesDatabase = true;

    protected static $extra_dataobjects = [
        \Page::class,
        TypesenseSearchPage::class
    ];

    private function withRequestVars(array $vars, callable $callback): mixed
    {
        $request = new HTTPRequest('GET', '/', $vars);
        $request->setSession(new Session([]));

        $controller = \SilverStripe\Control\Controller::create();
        $controller->setRequest($request);
        $controller->pushCurrent();

        try {
            return $callback();
        } finally {
            $controller->popCurrent();
        }
    }

    public function testSearchResultsIsNullWhenNotSearching(): void
    {
        $collection = $this->createCollection();
        $page = $this->createSearchPage($collection, 'Search results');

        $element = TypesenseAdvancedSearchElement::create(['SearchPageID' => $page->ID]);
        $element->write();

        // No 'q' request var present at all
        $result = $this->withRequestVars([], fn () => $element->SearchResults());
        $this->assertNull($result);

        // 'q' present but not '1'
        $result = $this->withRequestVars(['q' => '0'], fn () => $element->SearchResults());
        $this->assertNull($result);
    }

    public function testSearchResultsIsNullWhenElementHasNoCollection(): void
    {
        // Searching (q=1), but the element has no linked SearchPage/Collection at all
        $element = TypesenseAdvancedSearchElement::create();
        $element->write();

        $result = $this->withRequestVars(['q' => '1'], fn () => $element->SearchResults());
        $this->assertNull($result);
    }

}
