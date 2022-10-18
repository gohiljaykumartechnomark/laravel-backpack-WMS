<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BroadcastHasContactRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Broadcast;
use App\Models\Contact;

/**
 * Class BroadcastHasContactCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BroadcastHasContactCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\BroadcastHasContact::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/broadcast-has-contact');
        CRUD::setEntityNameStrings('broadcast has contact', 'broadcast has contacts');

        $this->crud->denyAccess('update');
        $this->crud->denyAccess('show');
        $this->crud->denyAccess('create');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        
        $this->crud->setColumns([
            [
               'name' => 'broadcast_id',
               'type' => 'relationship',
               'label' => 'Broadcast',
               'entity'    => 'broadcast', 
               'attribute' => 'name',
               'model'     => App\Models\Broadcast::class,
            ],
            [
               'name' => 'contact_id', 
               'type' => 'relationship',
               'label' => 'Contact', 
               'entity'    => 'contact', 
               'attribute' => 'name',
               'model'     => App\Models\Contact::class,
            ],
        ]);
        
        $this->crud->addFilter([
          'name'  => 'broadcast_id',
          'type'  => 'select2_multiple',
          'label' => 'Filter by Broadcast'
        ], function() {
            return Broadcast::get()->pluck('name','id')->toArray();
        }, function($values) { // if the filter is active
            $this->crud->addClause('whereIn', 'broadcast_id', json_decode($values));
        });
        
        
        $this->crud->addFilter([
          'name'  => 'contact_id',
          'type'  => 'select2_multiple',
          'label' => 'Filter by Contacts'
        ], function() {
            return Contact::get()->pluck('name','id')->toArray();
        }, function($values) { // if the filter is active
            $this->crud->addClause('whereIn', 'contact_id', json_decode($values));
        });

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(BroadcastHasContactRequest::class);
/*
        CRUD::addFields([
            [   // Checklist
               'label'     => "Broadcast",
               'type'      => 'select2',
               'name'      => 'broadcast_id',
               'entity'    => 'broadcast',
               'model'     => "App\Models\Broadcast",
               'attribute' => 'name',
               'options'   => (function ($query) {
                    return $query->orderBy('name', 'ASC')->get();
                }), 
            ],
            [   // Checklist
               'label'     => "Contact",
               'type'      => 'select2',
               'name'      => 'contact_id',
               'entity'    => 'contact',
               'model'     => "App\Models\Contact",
               'attribute' => 'name',
               'options'   => (function ($query) {
                    return $query->orderBy('name', 'ASC')->get();
                }), 
            ],
            [   
                'name'        => 'contact_id',
                'label'       => "Contact",
                'type'        => 'select2_multiple',

                 'entity'    => 'contacts', // the method that defines the relationship in your Model
                 'model'     => "App\Models\Contact", // foreign key model
                 'attribute' => 'name', // foreign key attribute that is shown to user
                 'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?
                 'select_all' => true, // show Select All and Clear buttons?

                'options'   => (function ($query) {
                     return $query->orderBy('name', 'ASC')->get();
                 }),
            ],
        ]);
*/
        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
