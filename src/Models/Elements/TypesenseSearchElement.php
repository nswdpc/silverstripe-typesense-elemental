<?php

namespace NSWDPC\Typesense\Elemental\Models\Elements;

use DNADesign\Elemental\Models\BaseElement;
use ElliotSawyer\SilverstripeTypesense\Collection;
use NSWDPC\Search\Forms\Forms\SearchForm;
use NSWDPC\Typesense\CMS\Models\TypesenseSearchPage;
use NSWDPC\Typesense\Elemental\Controllers\TypesenseSearchElementController;
use SilverStripe\Forms\DropdownField;
use SilverStripe\View\ArrayData;

/**
 * This element provides a search form for integration with Typesense in Silverstripe
 * Add it to a page or elemental-enabled DataObject where you would like your form to be
 */
class TypesenseSearchElement extends BaseElement {

    private static string $icon = 'font-icon-search';

    private static string $description = 'A content block used to display a search form for Typesense';

    private static string $singular_name = 'Typesense search element';

    private static string $plural_name = 'Typesense search elements';

    private static string $table_name = 'TypesenseSearchElement';

    private static bool $inline_editable = true;

    private static array $has_one = [
        'SearchPage' => TypesenseSearchPage::class // source of Typesense configuration and results display
    ];

    private static $controller_class = TypesenseSearchElementController::class;

    /**
     * @inheritdoc
     */
    public function getType() {
        return _t(static::class . '.BlockType', $this->i18n_singular_name());
    }

    public function getCmsFields() {
        $fields = parent::getCmsFields();
        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create(
                'SearchPageID',
                _t(static::class . '.SEARCH_PAGE_SELECT', 'Select a results page'),
                TypesenseSearchPage::get()->sort(['Title' => 'ASC'])->map('ID','TitleWithCollection')
            )->setEmptyString('')
        );
        return $fields;
    }

    /**
     * Return the template holding the search form
     */
    public function SearchForm(): ?SearchForm {
        return $this->getController()->SearchForm();
    }

    /**
     * Get the current collection being used by the linked search page
     */
    public function getCollection(): ?Collection {
        if($page = $this->SearchPage()) {
            return $page->Collection();
        } else {
            return null;
        }
    }

    /**
     * Anchor can be used for the prefix for the DOM elements holding results
     * Used in InstantSearch result handling
     */
    public function getTypesenseUniqID(): string {
        return $this->getAnchor();
    }

}
