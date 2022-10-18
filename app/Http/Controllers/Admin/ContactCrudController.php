<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ContactRequest;
use App\Http\Requests\ImportContactRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Excel;
use App\Models\Contact;

/**
 * Class ContactCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ContactCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Contact::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/contact');
        CRUD::setEntityNameStrings('contact', 'contacts');
        
        $this->crud->denyAccess('show');

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
        $this->crud->addButtonFromView('top', 'import_contact', 'import_contact', 'end');

        $this->crud->setColumns([
            [
                'name'  => 'path',
                'label' => 'Profile Pic',
                'type'  => 'image',
                'height' => '50px',
                'width'  => '50px',
            ],
            [
                'name'  => 'name',
                'label' => 'Name',
                'type'  => 'text',
            ],
            [
                'name'  => 'country_code',
                'label' => 'Country Code',
                'type'  => 'text',
                'prefix' => '+',
            ],
            [
                'name'  => 'number',
                'label' => 'Number',
                'type'  => 'text',
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
        CRUD::setValidation(ContactRequest::class);
        CRUD::addFields([
            [
                'name'  => 'name',
                'label' => 'Name',
                'type'  => 'text',
            ],
            [
                'name'  => 'country_code',
                'label' => 'Country Code',
                'type'  => 'text',
                'prefix' => '+',
            ],
            [
                'name'  => 'number',
                'label' => 'Number',
                'type'  => 'text',
            ],
            [
                'name'  => 'path',
                'label' => 'Profile Pic',
                'type'  => 'image',
                'crop' => true,
                'aspect_ratio' => 1,
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

    protected function importContact(ImportContactRequest $request)
    {
        $getCollection = Excel::toCollection(collect([]), $request->file('file'));
        $contactList = $getCollection[0]->toArray();
        $status = 400;
        $message = "";
        $data = [];
        $errorMessage = [];

        if (count($contactList) > 1) {
            foreach ($contactList as $key => $contact) {
                if ($key > 0) {
                    $checkContact = Contact::where('country_code',$contact[1])->where('number',$contact[2])->first();
                    if (empty($checkContact)) {
                        $data[] = [
                            "name" => $contact[0],
                            "country_code" => $contact[1],
                            "number" => $contact[2],
                            "path" => null,
                            "created_at"=> now(),
                            "updated_at"=> now()
                        ];
                    } else {
                        $errorMessage[] = $key+1;
                    }
                }
            }

            if (count($errorMessage)) {
                $message = "On Row-".implode(', ', $errorMessage).", duplicate records were found. Please remove/update it and try importing again.";
            } else {
                $data = collect($data);
                foreach ($data->chunk(100) as $key => $chunk) {
                    Contact::insert($chunk->toArray());
                }
                $status = 200;
                $message = "New contact are imported successfully!";
            }
        } else {
            $message = "No contact found in import file!";
        }

        return response()->json([
            "message" => $message, 
            "status" => $status, 
        ],$status);
    }
}
