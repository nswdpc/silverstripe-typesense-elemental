<?php

namespace NSWDPC\Typesense\Elemental\Controllers;

use NSWDPC\Search\Forms\Forms\SearchForm;
use NSWDPC\Typesense\CMS\Models\TypesenseSearchPage;
use NSWDPC\Typesense\Elemental\Models\Elements\TypesenseAdvancedSearchElement;
use SilverStripe\Control\Controller;

/**
 * Controller for the TypesenseAdvancedSearchElement
 */
class TypesenseAdvancedSearchElementController extends TypesenseSearchElementController
{
    private static array $allowed_actions = [
        'SearchForm',
    ];

    /**
     * Process a typesense search and redirect to results
     */
    #[\Override]
    public function doSearch(array $data, SearchForm $form): \SilverStripe\Control\HTTPResponse
    {
        $element = $this->getElement();
        if(!$element instanceof TypesenseAdvancedSearchElement) {
            // ERROR
            return $this->redirectBack();
        }
        $page = $element->SearchPage();
        if (!$page || !$page->isInDB()) {
            // ERROR
            return $this->redirectBack();
        }

        $collection = $page->Collection();
        if (!$collection) {
            // ERROR
            return $this->redirectBack();
        }

        $searchFields = $collection->Fields()->column('name');
        $queryFields = array_filter(
            $data,
            fn ($v, $k): bool =>
                // only allow fields that are known search fields, and non empty string values
                in_array($k, $searchFields) && $v !== '',
            ARRAY_FILTER_USE_BOTH
        );
        $queryFields['q'] = 1;
        $query = http_build_query($queryFields);
        $controller = Controller::curr();
        return $this->redirect($controller->Link('?' . $query));
    }
}
