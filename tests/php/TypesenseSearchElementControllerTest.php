<?php

namespace NSWDPC\Typesense\Elemental\Tests;

use NSWDPC\Search\Forms\Forms\SearchForm;
use NSWDPC\Typesense\CMS\Models\TypesenseSearchPage;
use NSWDPC\Typesense\Elemental\Controllers\TypesenseSearchElementController;
use NSWDPC\Typesense\Elemental\Models\Elements\TypesenseSearchElement;
use NSWDPC\Typesense\Elemental\Tests\Support\ScaffoldingTrait;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

class TypesenseSearchElementControllerTest extends SapphireTest
{
    use ScaffoldingTrait;

    protected $usesDatabase = true;

    protected static $extra_dataobjects = [
        \Page::class,
        TypesenseSearchPage::class
    ];

    public function testSearchFormIsNullWithoutSearchPage(): void
    {
        $element = TypesenseSearchElement::create();
        $element->write();

        $hostPage = $this->createSearchPage(null, 'Host page');
        $controller = TypesenseSearchElementController::create($element);

        $form = $this->withCurrentController($hostPage, fn () => $controller->SearchForm());

        $this->assertNull($form);
    }

    public function testSearchFormActionUrlPointsAtTheElementOnTheHostPage(): void
    {
        $collection = $this->createCollection();
        $resultsPage = $this->createSearchPage($collection, 'Search results');
        $hostPage = $this->createSearchPage(null, 'Host page');

        $element = TypesenseSearchElement::create(['SearchPageID' => $resultsPage->ID]);
        $element->write();

        $controller = TypesenseSearchElementController::create($element);

        [$form, $expectedAction] = $this->withCurrentController($hostPage, function ($hostController) use ($controller, $element): array {
            $form = $controller->SearchForm();
            $expectedAction = Controller::join_links($hostController->Link(), 'element', $element->ID, 'SearchForm');
            return [$form, $expectedAction];
        });

        $this->assertNotNull($form);
        $this->assertSame($expectedAction, $form->FormAction());
    }

    public function testSearchFormLoadsDataFromRequestWhenSearching(): void
    {
        $collection = $this->createCollection();
        $resultsPage = $this->createSearchPage($collection, 'Search results');
        $hostPage = $this->createSearchPage(null, 'Host page');

        $element = TypesenseSearchElement::create(['SearchPageID' => $resultsPage->ID]);
        $element->write();

        $controller = TypesenseSearchElementController::create($element);

        $form = $this->withCurrentController(
            $hostPage,
            fn () => $controller->SearchForm(),
            ['q' => '1', 'Search' => 'widgets']
        );

        $this->assertNotNull($form);
        $this->assertSame('widgets', $form->Fields()->dataFieldByName('Search')->Value());
    }

    public function testSearchFormDoesNotLoadDataWhenNotSearching(): void
    {
        $collection = $this->createCollection();
        $resultsPage = $this->createSearchPage($collection, 'Search results');
        $hostPage = $this->createSearchPage(null, 'Host page');

        $element = TypesenseSearchElement::create(['SearchPageID' => $resultsPage->ID]);
        $element->write();

        $controller = TypesenseSearchElementController::create($element);

        // 'Search' var present but q!=1, so it should NOT be loaded onto the form
        $form = $this->withCurrentController(
            $hostPage,
            fn () => $controller->SearchForm(),
            ['Search' => 'widgets']
        );

        $this->assertNotNull($form);
        $this->assertNotSame('widgets', $form->Fields()->dataFieldByName('Search')->Value());
    }
}
