<?php

namespace NSWDPC\Typesense\Elemental\Tests;

use NSWDPC\Search\Forms\Forms\AdvancedSearchForm;
use NSWDPC\Search\Forms\Forms\SearchForm;
use NSWDPC\Typesense\CMS\Models\TypesenseSearchPage;
use NSWDPC\Typesense\Elemental\Controllers\TypesenseAdvancedSearchElementController;
use NSWDPC\Typesense\Elemental\Models\Elements\TypesenseAdvancedSearchElement;
use NSWDPC\Typesense\Elemental\Tests\Support\ScaffoldingTrait;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

class TypesenseAdvancedSearchElementControllerTest extends SapphireTest
{
    use ScaffoldingTrait;

    protected $usesDatabase = true;

    protected static $extra_dataobjects = [
        \Page::class,
        TypesenseSearchPage::class
    ];

    private function emptyForm(Controller $controller): SearchForm
    {
        return SearchForm::create($controller, 'SearchForm', FieldList::create(), FieldList::create());
    }

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
