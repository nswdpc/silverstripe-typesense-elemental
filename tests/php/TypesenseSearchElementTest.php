<?php

namespace NSWDPC\Typesense\Elemental\Tests;

use NSWDPC\Typesense\CMS\Models\TypesenseSearchPage;
use NSWDPC\Typesense\Elemental\Models\Elements\TypesenseSearchElement;
use NSWDPC\Typesense\Elemental\Tests\Support\ScaffoldingTrait;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\DropdownField;

require_once __DIR__ . '/Support/ScaffoldingTrait.php';

class TypesenseSearchElementTest extends SapphireTest
{
    use ScaffoldingTrait;

    protected $usesDatabase = true;

    protected static $extra_dataobjects = [
        \Page::class,
        TypesenseSearchPage::class
    ];

    public function testGetCollectionReturnsLinkedPagesCollection(): void
    {
        $collection = $this->createCollection();
        $page = $this->createSearchPage($collection, 'Search results');

        $element = TypesenseSearchElement::create(['SearchPageID' => $page->ID]);
        $element->write();

        $result = $element->getCollection();
        $this->assertNotNull($result);
        $this->assertSame($collection->ID, $result->ID);
    }

    public function testGetCmsFieldsIncludesSearchPageDropdownSourcedFromExistingPages(): void
    {
        $page = $this->createSearchPage(null, 'Search results');

        $element = TypesenseSearchElement::create();
        $element->write();

        $fields = $element->getCmsFields();
        $dropdown = $fields->dataFieldByName('SearchPageID');

        $this->assertInstanceOf(DropdownField::class, $dropdown);
        $this->assertArrayHasKey($page->ID, $dropdown->getSource());
    }

    public function testGetTypesenseUniqIdMatchesAnchor(): void
    {
        $element = TypesenseSearchElement::create(['Title' => 'A search block']);
        $element->write();

        $this->assertSame($element->getAnchor(), $element->getTypesenseUniqID());
    }

    public function testGetTypesenseBindToInputId(): void
    {
        $element = TypesenseSearchElement::create();
        $this->assertSame('SearchForm_SearchForm_Search', $element->getTypesenseBindToInputId());
    }

    public function testGetTypesenseBindToParentId(): void
    {
        $element = TypesenseSearchElement::create();
        $this->assertSame('SearchForm_SearchForm', $element->getTypesenseBindToParentId());
    }
}
