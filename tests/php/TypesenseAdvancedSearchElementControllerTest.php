<?php

declare(strict_types=1);

namespace NSWDPC\Typesense\Elemental\Tests;

use NSWDPC\Search\Forms\Forms\AdvancedSearchForm;
use NSWDPC\Typesense\CMS\Models\TypesenseSearchPage;
use NSWDPC\Typesense\Elemental\Controllers\TypesenseAdvancedSearchElementController;
use NSWDPC\Typesense\Elemental\Models\Elements\TypesenseAdvancedSearchElement;
use NSWDPC\Typesense\Elemental\Tests\Support\ScaffoldingTrait;
use SilverStripe\Dev\SapphireTest;

require_once __DIR__ . '/Support/ScaffoldingTrait.php';

class TypesenseAdvancedSearchElementControllerTest extends SapphireTest
{
    use ScaffoldingTrait;

    protected $usesDatabase = true;

    protected static $extra_dataobjects = [
        \Page::class,
        TypesenseSearchPage::class
    ];

    private function makeElementWithCollection(): array
    {
        // Title/Body are query-able (indexed) string fields, Category is explicitly
        // not indexed and so must never appear in a filtered query string.
        $collection = $this->createCollection();
        $resultsPage = $this->createSearchPage($collection, 'Search results');
        $element = TypesenseAdvancedSearchElement::create(['SearchPageID' => $resultsPage->ID]);
        $element->write();

        return [$element, $resultsPage, $collection];
    }

    public function testSearchFormBuildsAnAdvancedSearchForm(): void
    {
        [$element, $resultsPage] = $this->makeElementWithCollection();
        $hostPage = $this->createSearchPage(null, 'Host page');
        $controller = TypesenseAdvancedSearchElementController::create($element);

        $form = $this->withCurrentController($hostPage, fn () => $controller->SearchForm());

        $this->assertInstanceOf(AdvancedSearchForm::class, $form);
    }
}
