<?php

namespace NSWDPC\Typesense\Elemental\Controllers;

use DNADesign\Elemental\Controllers\ElementController;
use ElliotSawyer\SilverstripeTypesense\Collection;
use NSWDPC\Search\Forms\Forms\SearchForm;
use NSWDPC\Search\Typesense\Services\FormCreator;
use NSWDPC\Typesense\CMS\Models\TypesenseSearchPage;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\Form;

/**
 * Controller for the TypesenseSearchElement
 */
class TypesenseSearchElementController extends ElementController {

    private static array $allowed_actions = [
        'SearchForm',
    ];

    /**
     * Return the search form
     */
    public function SearchForm(): ?SearchForm {
        $element = $this->getElement();
        $page = $element->SearchPage();
        if(!$page || !$page->isInDB() || !($page instanceof TypesenseSearchPage)) {
            return null;
        }
        $controller = Controller::curr();// current controller this element is on
        if(!$controller) {
            return null;
        }
        if(!$controller->hasMethod('Link')) {
            // controller must have a Link() method
            return null;
        }
        // the collection is linked to the search page selected
        $collection = $page->Collection();
        if(!($collection instanceof Collection)) {
            return null;
        }
        $form = FormCreator::createForCollection($controller, $collection, "SearchForm", ($this instanceof TypesenseAdvancedSearchElementController));
        $form->setFormAction(
            Controller::join_links(
                $controller->Link(),
                'element',
                $element->ID,
                'SearchForm'
            )
        );

        $request = $controller->getRequest();
        if($request->getVar('q') == 1) {
            $form->loadDataFrom($request->getVars());
        }

        return $form;
    }

    /**
     * Process a typesense search and redirect to results
     */
    public function doSearch(array $data, SearchForm $form): \SilverStripe\Control\HTTPResponse
    {
        $term = $data['Search'] ?? '';
        $term = strip_tags(trim((string)$term));
        $element = $this->getElement();
        $page = $element->SearchPage();
        if(!$page || !$page->isInDB() || !($page instanceof TypesenseSearchPage)) {
            return null;
        }
        return $this->redirect( $page->Link('?q=' . $term));
    }
}
