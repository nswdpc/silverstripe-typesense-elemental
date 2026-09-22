<?php

namespace NSWDPC\Typesense\Elemental\Tests\Support;

use NSWDPC\Search\Typesense\Models\TypesenseSearchCollection;
use NSWDPC\Typesense\CMS\Models\TypesenseSearchPage;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\ORM\DataObject;

/**
 * Shared fixture builders for element/controller tests.
 * Not a test case itself - mixed into SapphireTest subclasses.
 */
trait ScaffoldingTrait
{
    /**
     * Create a TypesenseSearchCollection with 'Title' and 'Body' as query-able
     * string fields, and 'Category' as a non-indexed (so non-query-able) string field.
     */
    protected function createCollection(string $name = 'test_collection'): TypesenseSearchCollection
    {
        $collection = TypesenseSearchCollection::create([
            'Name' => $name,
            'Enabled' => true,
            'Metadata' => json_encode([
                'name' => $name,
                'fields' => [
                    ['name' => 'Title', 'type' => 'string'],
                    ['name' => 'Body', 'type' => 'string'],
                    ['name' => 'Category', 'type' => 'string', 'index' => false],
                ],
            ], JSON_THROW_ON_ERROR),
        ]);
        $collection->write();
        return $collection;
    }

    protected function createSearchPage(?TypesenseSearchCollection $collection, string $title): TypesenseSearchPage
    {
        $page = TypesenseSearchPage::create([
            'Title' => $title,
            'CollectionID' => $collection->ID ?? 0,
        ]);
        $page->write();
        return $page;
    }

    /**
     * Run $callback with $record's ContentController pushed as Controller::curr(),
     * as would happen when an element's containing page is handling a request.
     * $vars are passed through as GET query vars on that request.
     */
    protected function withCurrentController(DataObject $record, callable $callback, array $vars = []): mixed
    {
        $request = new HTTPRequest('GET', '/', $vars);
        $request->setSession(new Session([]));

        $controller = ContentController::create($record);
        $controller->setRequest($request);
        $controller->pushCurrent();

        try {
            return $callback($controller);
        } finally {
            $controller->popCurrent();
        }
    }
}
