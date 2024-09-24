<?php

namespace NSWDPC\Typesense\Elemental\Models\Elements;

use NSWDPC\Search\Forms\Forms\AdvancedSearchForm;
use NSWDPC\Search\Typesense\Services\SearchHandler;
use NSWDPC\Typesense\Elemental\Controllers\TypesenseAdvancedSearchElementController;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;

/**
 * This element provides a search form for integration with Typesense in Silverstripe
 * Add it to a page or elemental-enabled DataObject where you would like your form to be
 */
class TypesenseAdvancedSearchElement extends TypesenseSearchElement {

    private static string $icon = 'font-icon-search';

    private static string $description = 'A content block used to display an advanced search form for Typesense';

    private static string $singular_name = 'Typesense advanced search element';

    private static string $plural_name = 'Typesense advanced search elements';

    private static string $table_name = 'TypesenseAdvancedSearchElement';

    private static bool $inline_editable = true;

    private static string $controller_class = TypesenseAdvancedSearchElementController::class;

    /**
     * @inheritdoc
     */
    public function getType() {
        return _t(static::class . '.BlockType', $this->i18n_singular_name());
    }

    /**
     * Return the template holding the search results
     */
    public function SearchResults(): ?ArrayList {
        $controller = Controller::curr();
        $request = $controller->getRequest();
        $isSearching = $request->getVar('q') == 1;
        if($isSearching) {
            $collection = $this->getCollection();
            if(!$collection) {
                return null;
            }
            $handler = SearchHandler::create();
            $data = $request->getVars();
            unset($data['q']);
            unset($data['flush']);// avoid sending flush to the search
            $results = $handler->doSearch($collection, $data);
            return $results;
        } else {
            return null;
        }
    }


    protected function applyDefaultStyle() {
        // basic css for some alignment, only if no results template provided
        $style = <<<CSS
            .search-outer {
                display: flex;
                gap: 1rem;
            }
            .search-form {
                width: 40%;
            }
            .search-results {
                width: 60%;
            }
        CSS;
        Requirements::customCss($style, "TypesenseAdvancedSearchElement");
    }

    public function forTemplate($holder = true) {
        $this->applyDefaultStyle();
        return parent::forTemplate($holder);
    }

}
