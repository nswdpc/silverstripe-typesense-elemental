<?php

namespace NSWDPC\Typesense\Elemental\Tests;

use NSWDPC\Search\Forms\Forms\SearchForm;
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

    private function emptyForm(Controller $controller): SearchForm
    {
        return SearchForm::create($controller, 'SearchForm', FieldList::create(), FieldList::create());
    }

    public function testSearchFormIsNullWithoutSearchPage(): void
    {
        $element = TypesenseSearchElement::create();
        $element->write();

        $hostPage = $this->createSearchPage(null, 'Host page');
        $controller = TypesenseSearchElementController::create($element);

        $form = $this->withCurrentController($hostPage, fn () => $controller->SearchForm());

        $this->assertNull($form);
    }

    public function testSearchFormIsNullWhenSearchPageHasNoCollection(): void
    {
        $resultsPage = $this->createSearchPage(null, 'Search results'); // no Collection
        $element = TypesenseSearchElement::create(['SearchPageID' => $resultsPage->ID]);
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

    public function testDoSearchStripsTagsAndTrimsTheSearchTerm(): void
    {
        $resultsPage = $this->createSearchPage($this->createCollection(), 'Search results');
        $element = TypesenseSearchElement::create(['SearchPageID' => $resultsPage->ID]);
        $element->write();

        $controller = TypesenseSearchElementController::create($element);
        $controller->setRequest(new HTTPRequest('POST', '/'));

        $response = $controller->doSearch(['Search' => "  <b>widgets</b>  "], $this->emptyForm($controller));

        parse_str((string) parse_url((string) $response->getHeader('Location'), PHP_URL_QUERY), $parsed);
        $this->assertSame('widgets', $parsed['q'] ?? null);
    }

    public function testDoSearchRedirectsToSearchPageWithACorrectlyEncodedTerm(): void
    {
        // A term containing '&' and spaces must round-trip correctly through the
        // redirect URL - a regression guard for building the query string via
        // http_build_query() rather than raw string concatenation.
        $resultsPage = $this->createSearchPage($this->createCollection(), 'Search results');
        $element = TypesenseSearchElement::create(['SearchPageID' => $resultsPage->ID]);
        $element->write();

        $controller = TypesenseSearchElementController::create($element);
        $controller->setRequest(new HTTPRequest('POST', '/'));

        $response = $controller->doSearch(['Search' => 'cats & dogs'], $this->emptyForm($controller));
        $location = (string) $response->getHeader('Location');

        $this->assertStringStartsWith($resultsPage->Link(), $location);

        parse_str((string) parse_url($location, PHP_URL_QUERY), $parsed);
        $this->assertSame('cats & dogs', $parsed['q'] ?? null);
    }

    public function testDoSearchRedirectsBackWithoutSearchPage(): void
    {
        $element = TypesenseSearchElement::create(); // no SearchPageID
        $element->write();

        $controller = TypesenseSearchElementController::create($element);
        $controller->setRequest(new HTTPRequest('POST', '/'));

        $response = $controller->doSearch(['Search' => 'widgets'], $this->emptyForm($controller));

        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
        $this->assertLessThan(400, $response->getStatusCode());
    }
}
