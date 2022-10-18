<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MessageRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class MessageCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MessageCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Message::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/message');
        CRUD::setEntityNameStrings('message', 'messages');
        
        $this->crud->denyAccess('update');
        $this->crud->denyAccess('delete');

        $this->crud->addFilter([
          'type'  => 'date_range',
          'name'  => 'created_at',
          'label' => 'Date range'
        ],
        false,
        function ($value) { // if the filter is active, apply these constraints
          $dates = json_decode($value);
          $this->crud->addClause('where', 'created_at', '>=', $dates->from);
          $this->crud->addClause('where', 'created_at', '<=', $dates->to . ' 23:59:59');
        });
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
               'name' => 'message', // name of relationship method in the model
               'type' => 'textarea',
               'label' => 'Message', 
               'limit'  => 120,
            ],
            [
               'name' => 'path', // name of relationship method in the model
               'type' => 'upload',
               'label' => 'Message', 
            ],
            [
               'name' => 'created_at', // name of relationship method in the model
               'type' => 'datetime',
               'label' => 'Created At', 
            ],
        ]);


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
        CRUD::setValidation(MessageRequest::class);

        CRUD::addFields([
            [   
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
            [   
               'label'     => "Message",
               'type'      => 'textarea',
               'name'      => 'message',
               'attributes' => [
                    'rows' => 12
                ]
            ],
            [   
                'name'      => 'path',
                'label'     => 'File',
                'type'      => 'upload',
                'upload'    => true,
                'disk'      => 'root',
            ],
        ]);

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
