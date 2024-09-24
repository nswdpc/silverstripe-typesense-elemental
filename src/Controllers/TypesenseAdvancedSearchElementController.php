<?php

namespace NSWDPC\Typesense\Elemental\Controllers;

use DNADesign\Elemental\Controllers\ElementController;
use ElliotSawyer\SilverstripeTypesense\Collection;
use NSWDPC\Search\Forms\Forms\AdvancedSearchForm;
use NSWDPC\Search\Forms\Forms\SearchForm;
use NSWDPC\Search\Typesense\Services\FormCreator;
use NSWDPC\Typesense\CMS\Models\TypesenseSearchPage;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\Form;

/**
 * Controller for the TypesenseAdvancedSearchElement
 */
class TypesenseAdvancedSearchElementController extends TypesenseSearchElementController {

    private static array $allowed_actions = [
        'SearchForm',
    ];

    /**
     * Process a typesense search and redirect to results
     */
    public function doSearch(array $data, SearchForm $form): \SilverStripe\Control\HTTPResponse
    {
        $element = $this->getElement();
        $page = $element->SearchPage();
        if(!$page || !$page->isInDB() || !($page instanceof TypesenseSearchPage)) {
            // ERROR
            return $this->redirectBack();
        }
        $collection = $page->Collection();
        if(!$collection) {
            // ERROR
            return $this->redirectBack();
        }
        $searchFields = $collection->Fields()->column('name');
        $queryFields = array_filter(
            $data,
            function($v, $k) use ($searchFields) {
                // only allow fields that are known search fields, and non empty string values
                return in_array($k, $searchFields) && $v !== '';
            },
            ARRAY_FILTER_USE_BOTH
        );
        $queryFields['q'] = 1;
        $query = http_build_query($queryFields);
        $controller = Controller::curr();
        return $this->redirect( $controller->Link('?' . $query));
    }
}
