<?php

namespace App\Http\Controllers;

use App\Models\Blocks;
use App\Models\Tab;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormFieldController extends Controller
{
    /**
     * Fetch form fields dynamically based on tab name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFormFields(Request $request)
    {
        try {
            $tabName = $request->input('name');

            // Validate that tabName is present in the request
            if (!$tabName) {
                Log::error('Tab name is missing from the request.');
                return response()->json(['error' => 'Tab name is required'], 400);
            }

            // Log the received tab name for debugging
            Log::info('Tab name received: ' . $tabName);

            // Fetch the tab from the database using the tab name
            $tab = Tab::where('name', $tabName)->first();

            // Handle case where no tab is found
            if (!$tab) {
                Log::error('No tab found for the given tab name', ['name' => $tabName]);
                return response()->json(['error' => 'Tab not found'], 404);
            }

            $tabId = $tab->tabid;

            // Fetch blocks with their associated fields based on tab ID
            $blocks = Blocks::where('tabid', $tabId)->with('fields')->get();

            // Log the SQL queries executed
            Log::info('SQL Query: ', DB::getQueryLog());

            // Handle case where no blocks are found
            if ($blocks->isEmpty()) {
                Log::error('No blocks found for the given tab ID', ['tabid' => $tabId]);
                return response()->json([]);
            }

            // Prepare an array to store form fields data dynamically
            $formFields = [];

            foreach ($blocks as $block) {
                // Log the processing of each block for debugging
                Log::info('Processing block: ' . $block->blocklabel);

                $fields = [];
                foreach ($block->fields as $field) {
                    // Fetch additional details about the field using the Fields model
                    $fieldDetails = Field::where('fieldid', $field->fieldid)->first();

                    // Prepare basic field data
                    $fieldData = [
                        'name' => $field->fieldname,
                        'type' => $field->uitype,
                        'label' => $field->fieldlabel,
                            ];

                    // Example: Fetch options for specific field types
                    if (in_array($field->uitype, [33, 16, 56])) {
                        $options = $this->getFieldOptions($field->fieldname);
                        if ($options) {
                            $fieldData['options'] = $options;
                        }
                    }

                    // Example: Add validation rules if specified in typeofdata
                    if (
                        str_contains($field->typeofdata, 'V~M') || 
                        str_contains($field->typeofdata, 'D~M') || 
                        str_contains($field->typeofdata, 'N~M')
                    ) {
                        $fieldData['rules'][] = [
                            'required' => true,
                            'message' => 'Enter your ' . strtolower($field->fieldlabel),
                        ];
                    }
                    

                    // Add the field data to the fields array
                    $fields[] = $fieldData;
                }

                // Add the block data with its fields to the formFields array
                $formFields[] = [
                    'blockid' => $block->blockid,
                    'blockname' => $block->blocklabel,
                    'fields' => $fields,
                ];

                // Log the form fields added for each block
                Log::info('Form fields for block: ', ['blockname' => $block->blocklabel, 'fields' => $fields]);
            }

            // Return the dynamically fetched form fields as JSON response
            return response()->json($formFields);
        } catch (\Exception $e) {
            // Log the error encountered during form fields fetching
            Log::error('Error fetching form fields: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching form fields'], 500);
        }
    }

    /**
     * Fetch options for a specific field dynamically.
     *
     * @param string $fieldName
     * @return array
     */
  
private function getFieldOptions($fieldName)
{
    // Initialize an empty options array
    $options = [];

    // Define options fetching logic based on field name
    $optionsMap = [
        'tags' => function () {
            $options = DB::table('jo_tags')->select('id', 'tags_name as value', 'tags_name as label', 'tag_color as color')->get()->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a tag...', 'color' => '', 'id' => null]);
            return $options;
        },
        
        'projects' => function () {
            $options = DB::table('jo_projects')->pluck('project_name', 'id')->map(function ($project, $id) {
                return ['value' => $project, 'label' => $project, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a project...', 'id' => null]);
            return $options;
        },

        'project_id' => function () {
            $options = DB::table('jo_projects')->pluck('project_name', 'id')->map(function ($project, $id) {
                return ['value' => $project, 'label' => $project, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a project...', 'id' => null]);
            return $options;
        },
        
        'teams' => function () {
            $options = DB::table('jo_teams')->pluck('team_name', 'id')->map(function ($teams, $id) {
                return ['value' => $teams, 'label' => $teams, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a team...', 'id' => null]);
            return $options;
        },

        'team_id' => function () {
            $options = DB::table('jo_teams')->pluck('team_name', 'id')->map(function ($teams, $id) {
                return ['value' => $teams, 'label' => $teams, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a team...', 'id' => null]);
            return $options;
        },
        
        'contacts' => function () {
            $customers = DB::table('jo_customers')->pluck('name', 'id')->map(function ($customer, $id) {
                return ['value' => $customer, 'label' => $customer, 'id' => $id];
            })->toArray();

            $leads = DB::table('jo_leads')->pluck('name', 'id')->map(function ($lead, $id) {
                return ['value' => $lead, 'label' => $lead, 'id' => $id];
            })->toArray();

            $clients = DB::table('jo_clients')->pluck('name', 'id')->map(function ($client, $id) {
                return ['value' => $client, 'label' => $client, 'id' => $id];
            })->toArray();

            $options = array_merge($customers, $leads, $clients);
            array_unshift($options, ['value' => '', 'label' => 'Select a contact...', 'id' => null]);
            return $options;
        },
        
        'client_id' => function () {
            $options = DB::table('jo_clients')->pluck('name', 'id')->map(function ($client, $id) {
                return ['value' => $client, 'label' => $client, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a client...', 'id' => null]);
            return $options;
        },

        'Employee that generate income' => function () {
            $options = DB::table('jo_manage_employees')->pluck('first_name', 'id')->map(function ($employee, $id) {
                return ['value' => $employee, 'label' => $employee, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an employee...', 'id' => null]);
            return $options;
        },
        
        'invoice_number' => function () {
            $options = DB::table('jo_invoices')->pluck('invoicenumber', 'id')->map(function ($invoice, $id) {
                return ['value' => $invoice, 'label' => $invoice, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an invoice number...', 'id' => null]);
            return $options;
        },
        
        'product_type' => function () {
            $options = DB::table('jo_product_types')->pluck('name', 'id')->map(function ($product_type, $id) {
                return ['value' => $product_type, 'label' => $product_type, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a product type...', 'id' => null]);
            return $options;
        },
        
        'product_category' => function () {
            $options = DB::table('jo_product_categories')->pluck('name', 'id')->map(function ($product_category, $id) {
                return ['value' => $product_category, 'label' => $product_category, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a product category...', 'id' => null]);
            return $options;
        },
        
        'employees_that_generate' => function () {
            $options = DB::table('jo_manage_employees')->pluck('first_name', 'id')->map(function ($employee, $id) {
                return ['value' => $employee, 'label' => $employee, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an employee...', 'id' => null]);
            return $options;
        },
        'addorremoveemployee' => function () {
            $options = DB::table('jo_manage_employees')->pluck('first_name', 'id')->map(function ($employee, $id) {
                return ['value' => $employee, 'label' => $employee, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an employee...', 'id' => null]);
            return $options;
        },
        'choose_employees' => function () {
            $options = DB::table('jo_manage_employees')->pluck('first_name', 'id')->map(function ($employee, $id) {
                return ['value' => $employee, 'label' => $employee, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an employee...', 'id' => null]);
            return $options;
        },
        'employee_id'  => function () {
            $options = DB::table('jo_manage_employees')->pluck('first_name', 'id')->map(function ($employee, $id) {
                return ['value' => $employee, 'label' => $employee, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an employee...', 'id' => null]);
            return $options;
        },
        'approval_policy' => function () {
            $options = DB::table('jo_approval_policy')->pluck('name', 'id')->map(function ($approvalpolicy, $id) {
                return ['value' => $approvalpolicy, 'label' => $approvalpolicy, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an approvalpolicy...', 'id' => null]);
            return $options;
        },
        'chooseteams' => function () {
            $options = DB::table('jo_teams')->pluck('team_name', 'id')->map(function ($team, $id) {
                return ['value' => $team, 'label' => $team, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an team...', 'id' => null]);
            return $options;
        },
        'choose_teams' => function () {
            $options = DB::table('jo_teams')->pluck('team_name', 'id')->map(function ($team, $id) {
                return ['value' => $team, 'label' => $team, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an team...', 'id' => null]);
            return $options;
        },
        
        'categories' => function () {
            $options = DB::table('jo_manage_categories')->pluck('expense_name', 'id')->map(function ($category, $id) {
                return ['value' => $category, 'label' => $category, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a category...', 'id' => null]);
            return $options;
        },
        
        'category_name' => function () {
            $options = DB::table('jo_manage_categories')->pluck('expense_name', 'id')->map(function ($category, $id) {
                return ['value' => $category, 'label' => $category, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a category...', 'id' => null]);
            return $options;
        },
        
        'vendor' => function () {
            $options = DB::table('jo_vendors')->pluck('vendor_name', 'id')->map(function ($vendor, $id) {
                return ['value' => $vendor, 'label' => $vendor, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a vendor...', 'id' => null]);
            return $options;
        },

        'task_id' => function () {
            $options = DB::table('jo_tasks')->pluck('title', 'id')->map(function ($task, $id) {
                return ['value' => $task, 'label' => $task, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a task...', 'id' => null]);
            return $options;
        },

        // Add more mappings as needed
    ];
    // Default options if fieldName doesn't match specific cases
    $defaultOptions = [
        'status' => [
            ['value' => '', 'label' => 'Select status...'],
            ['value' => 'none', 'label' => 'None'],
            ['value' => 'open', 'label' => 'Open'],
            ['value' => 'inprogress', 'label' => 'In Progress'],
            ['value' => 'inreview', 'label' => 'In Review'],
            ['value' => 'completed', 'label' => 'Completed'],
            ['value' => 'closed', 'label' => 'Closed'],
        ],
        'priority' => [
            ['value' => '', 'label' => 'Select priority...'],
            ['value' => 'none', 'label' => 'None'],
            ['value' => 'low', 'label' => 'Low'],
            ['value' => 'medium', 'label' => 'Medium'],
            ['value' => 'high', 'label' => 'High'],
            ['value' => 'urgent', 'label' => 'Urgent'],
        ],
        'size' => [
            ['value' => '', 'label' => 'Select size...'],
            ['value' => 'none', 'label' => 'None'],
            ['value' => 'large', 'label' => 'Large'],
            ['value' => 'medium', 'label' => 'Medium'],
            ['value' => 'small', 'label' => 'Small'],
            ['value' => 'tiny', 'label' => 'Tiny'],
        ],
        // 'currency' => [
        //     ['value' => '', 'label' => 'Select currency...'],
        //     ['value' => 'india(INR)', 'label' => 'India(INR)'],
        //     ['value' => 'canadian dollar(CAD)', 'label' => 'Canadian Dollar(CAD)'],
        //     ['value' => 'israeli pound(ILP)', 'label' => 'Israeli Pound(ILP)'],
        // ],
        'currency' => [
            ['value' => '', 'label' => 'Select currency...'],
            ['value' => 'Albania, Leke', 'label' => 'Albania, Leke'],
            ['value' => 'Argentina, Pesos', 'label' => 'Argentina, Pesos'],
            ['value' => 'Aruba, Guilders', 'label' => 'Aruba, Guilders'],
            ['value'=> 'Australia, Dollars', 'label'=>'Australia, Dollars'],
            ['value'=>'Azerbaijan, New Manats','label'=> 'Azerbaijan, New Manats'],
            ['value'=>'Bahamas, Dollars','label'=> 'Bahamas, Dollars'],
            ['value'=>'Bahrain, Dinar','label'=> 'Bahrain, Dinar'],
            ['value'=>'Barbados, Dollars','label'=> 'Barbados, Dollars'],
            ['value'=>'Belarus, Rubles','label'=> 'Belarus, Rubles'],
            ['value'=>'Belize, Dollars','label'=> 'Belize, Dollars'],
            ['value'=>'Bermuda, Dollars' ,'label'=> 'Bermuda, Dollars'],
            ['value'=>'Bolivia, Bolivianos','label'=> 'Bolivia, Bolivianos'],
            ['value'=>'Convertible Marka','label'=> 'Convertible Marka'],
            ['value'=>'Botswana, Pulas','label'=> 'Botswana, Pulas'],
            ['value'=>'Bulgaria, Leva', 'label'=> 'Bulgaria, Leva'],
            ['value'=>'Brazil, Reais','label'=> 'Brazil, Reais'],
            ['value'=>'Great Britain Pounds','label'=> 'Great Britain Pounds'],
            ['value'=>'Brunei Darussalam, Dollars','label' => 'Brunei Darussalam, Dollars'],
            ['value'=>'Canada, Dollars' ,'label'=> 'Canada, Dollars'],
            ['value'=>'Cayman Islands, Dollars','label' => 'Cayman Islands, Dollars'],
            ['value'=>'Chile, Pesos','label'=> 'Chile, Pesos'],
            ['value'=>'Colombia, Pesos','label'=> 'Colombia, Pesos'],
            ['value'=>'Costa Rica, Colón','label'=> 'Costa Rica, Colón'],
            ['value'=>'Croatia, Kuna','label'=> 'Croatia, Kuna'],
            ['value'=>'Cuba, Pesos','label'=> 'Cuba, Pesos'],
            ['value'=>'Cyprus, Pounds','label'=> 'Cyprus, Pounds'],
            ['value'=>'Czech Republic, Koruny','label'=> 'Czech Republic, Koruny'],
            ['value'=>'Denmark, Kroner','label'=> 'Denmark, Kroner'],
            ['value'=>'Dominican Republic, Pesos','label'=> 'Dominican Republic, Pesos'],
            ['value'=>'East Caribbean, Dollars','label' => 'East Caribbean, Dollars'],
            ['value'=>'Egypt, Pounds','label'=> 'Egypt, Pounds'],
            ['value'=>'El Salvador, Colón','label'	=> 'El Salvador, Colón'],
            ['value'=>'England, Pounds','label'=> 'England, Pounds'],
            ['value'=>'Estonia, Krooni','label'=> 'Estonia, Krooni'],
            ['value'=>'Euro','label'=> 'Euro'],
            ['value'=>'Falkland Islands, Pounds' ,'label'=> 'Falkland Islands, Pounds'],
            ['value'=>'Fiji, Dollars','label'=> 'Fiji, Dollars'],
            ['value'=>'Ghana, Cedis','label'=> 'Ghana, Cedis'],
            ['value'=>'Gibraltar, Pounds' ,'label'=> 'Gibraltar, Pounds'],
            ['value'=>'Guatemala, Quetzales' ,'label'=> 'Guatemala, Quetzales'],
            ['value'=>'Guernsey, Pounds','label'=> 'Guernsey, Pounds'],
            ['value'=>'Guyana, Dollars' ,'label'=> 'Guyana, Dollars'],
            ['value'=>'Honduras, Lempiras','label'=> 'Honduras, Lempiras'],
            ['value'=>'LvHong Kong, Dollars ','label'=> 'LvHong Kong, Dollars'],
            ['value'=>'Hungary, Forint','label'=> 'Hungary, Forint'],
            ['value'=>'Iceland, Krona','label'=> 'Iceland, Krona'],
            ['value'=>'India, Rupees','label'=> 'India, Rupees'],
            ['value'=>'Indonesia, Rupiahs','label'	=> 'Indonesia, Rupiahs'],
            ['value'=>'Iran, Rials' ,'label'=> 'Iran, Rials'],
            ['value'=>'Isle of Man, Pounds','label'=> 'Isle of Man, Pounds'],
            ['value'=>'Isle of Man, Pounds' ,'label'=> 'Isle of Man, Pounds'],
            ['value'=>'Israel, New Shekels' ,'label'=> 'Israel, New Shekels'],
            ['value'=>'Jamaica, Dollars' ,'label'=> 'Jamaica, Dollars'],
            ['value'=>'Japan, Yen' ,'label'=> 'Japan, Yen'],
            ['value'=>'Jersey, Pounds','label'=> 'Jersey, Pounds'],
            ['value'=>'Kazakhstan, Tenge','label'=> 'Kazakhstan, Tenge'],
            ['value'=>'Korea (North), Won','label'=> 'Korea (North), Won'],
            ['value'=>'Korea (South), Won','label'=> 'Korea (South), Won'],
            ['value'=>'Kyrgyzstan, Soms','label'=> 'Kyrgyzstan, Soms'],
            ['value'=>'Laos, Kips','label'=> 'Laos, Kips'],
            ['value'=>'Latvia, Lati','label'=> 'Latvia, Lati'],
            ['value'=>'Lebanon, Pounds','label'=> 'Lebanon, Pounds'],
            ['value'=>'Liberia, Dollars','label'=> 'Liberia, Dollars'],
            ['value'=>'Switzerland Francs','label'	=> 'Switzerland Francs'],
            ['value'=>'Lithuania, Litai','label'=> 'Lithuania, Litai'],
            ['value'=>'Macedonia, Denars','label'=> 'Macedonia, Denars'],
            ['value'=>'Malaysia, Ringgits','label'	=> 'Malaysia, Ringgits'],
            ['value'=>'Malta, Liri'	,'label'=> 'Malta, Liri'],
            ['value'=>'Mauritius, Rupees','label'=> 'Mauritius, Rupees'],
            ['value'=>'Mexico, Pesos','label'=> 'Mexico, Pesos'],
            ['value'=>'Mongolia, Tugriks','label'=> 'Mongolia, Tugriks'],
            ['value'=>'Mozambique, Meticais','label'=> 'Mozambique, Meticais'],
            ['value'=>'Namibia, Dollars' ,'label'=> 'Namibia, Dollars'],
            ['value'=>'Nepal, Rupees','label'=> 'Nepal, Rupees'],
            ['value'=>'Netherlands Antilles, Guilders','label' => 'Netherlands Antilles, Guilders'],
            ['value'=>'New Zealand, Dollars' ,'label'=> 'New Zealand, Dollars'],
            ['value'=>'Nicaragua, Cordobas','label'=> 'Nicaragua, Cordobas'],
            ['value'=>'Nigeria, Nairas','label'=> 'Nigeria, Nairas'],
            ['value'=>'North Korea, Won','label'=> 'North Korea, Won'],
            ['value'=>'Norway, Krone','label'=> 'Norway, Krone'],
            ['value'=>'Oman, Rials','label'=> 'Oman, Rials'],
            ['value'=>'Pakistan, Rupees','label'=> 'Pakistan, Rupees'],
            ['value'=>'Panama, Balboa','label'=> 'Panama, Balboa'],
            ['value'=>'Paraguay, Guarani','label'=> 'Paraguay, Guarani'],
            ['value'=>'Peru, Nuevos Soles','label'=> 'Peru, Nuevos Soles'],
            ['value'=>'Philippines, Pesos','label'=> 'Philippines, Pesos'],
            ['value'=>'Poland, Zlotych','label'=> 'Poland, Zlotych'],
            ['value'=>'Qatar, Rials','label'=> 'Qatar, Rials'],
            ['value'=>'Romania, New Lei','label'=> 'Romania, New Lei'],
            ['value'=>'Russia, Rubles','label'=> 'Russia, Rubles'],
            ['value'=>'Saint Helena, Pounds','label'=> 'Saint Helena, Pounds'],
            ['value'=>'Saudi Arabia, Riyals','label'=> 'Saudi Arabia, Riyals'],
            ['value'=>'Serbia, Dinars','label'=> 'Serbia, Dinars'],
            ['value'=>'Seychelles, Rupees','label'	=> 'Seychelles, Rupees'],
            ['value'=>'Singapore, Dollars','label'	=> 'Singapore, Dollars'],
            ['value'=>'Solomon Islands, Dollars' ,'label'=> 'Solomon Islands, Dollars'],
            ['value'=>'Somalia, Shillings','label'=> 'Somalia, Shillings'],
            ['value'=>'South Africa, Rand','label'=> 'South Africa, Rand'],
            ['value'=>'South Korea, Won','label'=> 'South Korea, Won'],
            ['value'=>'Sri Lanka, Rupees','label'=> 'Sri Lanka, Rupees'],
            ['value'=>'Sweden, Kronor','label'=> 'Sweden, Kronor'],
            ['value'=>'Switzerland, Francs','label'=> 'Switzerland, Francs'],
            ['value'=>'Suriname, Dollars','label'=> 'Suriname, Dollars'],
            ['value'=>'Syria, Pounds','label'=> 'Syria, Pounds'],
            ['value'=>'Taiwan, New Dollars','label'=> 'Taiwan, New Dollars'],
            ['value'=>'Thailand, Baht','label'=> 'Thailand, Baht'],
            ['value'=>'Trinidad and Tobago, Dollars','label' => 'Trinidad and Tobago, Dollars'],
            ['value'=>'Turkey, New Lira','label'=> 'Turkey, New Lira'],
            ['value'=>'Turkey, Liras','label'=> 'Turkey, Liras'],
            ['value'=>'Tuvalu, Dollars','label'=> 'Tuvalu, Dollars'],
            ['value'=>'Ukraine, Hryvnia','label'=> 'Ukraine, Hryvnia'],
            ['value'=>'United Kingdom, Pounds','label'=> 'United Kingdom, Pounds'],
            ['value'=>'USA, Dollars','label'=> 'USA, Dollars'],
            ['value'=>'Uruguay, Pesos','label'=> 'Uruguay, Pesos'],
            ['value'=>'Uzbekistan, Sums','label'=> 'Uzbekistan, Sums'],
            ['value'=>'Venezuela, Bolivares Fuertes','label'=> 'Venezuela, Bolivares Fuertes'],
            ['value'=>'Vietnam, Dong','label'=> 'Vietnam, Dong'],
            ['value'=>'Zimbabwe Dollars','label'=> 'Zimbabwe Dollars'],
            ['value'=>'China, Yuan Renminbi','label'=> 'China, Yuan Renminbi'],
            ['value'=>'Afghanistan, Afghanis','label' => 'Afghanistan, Afghanis'],
            ['value'=>'Cambodia, Riels','label'=> 'Cambodia, Riels'],
            ['value'=>'China, Yuan Renminbi','label'=> 'China, Yuan Renminbi'],
            ['value'=>'Jordan, Dinar','label'=> 'Jordan, Dinar'],
            ['value'=>'Kenya, Shilling','label'=> 'Kenya, Shilling'],
            ['value'=>'MADAGASCAR, Malagasy Ariary','label'=> 'MADAGASCAR, Malagasy Ariary'],
            ['value'=>'United Arab Emirates, Dirham','label'=> 'United Arab Emirates, Dirham'],
            ['value'=>'United Republic of Tanzania, Shilling','label' => 'United Republic OF Tanzania, Shilling'],
            ['value'=>'Yemen, Rials','label'=> 'Yemen, Rials'],
            ['value'=>'Zambia, Kwacha','label'=> 'Zambia, Kwacha'],
            ['value'=>'Malawi, kwacha','label'=> 'Malawi, kwacha'],
            ['value'=>'Tunisian, Dinar','label'=> 'Tunisian, Dinar'],
            ['value'=>'Moroccan, Dirham','label'=> 'Moroccan, Dirham'],
        ],
        'Currency' => [
            ['value' => '', 'label' => 'Select currency...'],
            ['value' => 'india(INR)', 'label' => 'India(INR)'],
            ['value' => 'canadian dollar(CAD)', 'label' => 'Canadian Dollar(CAD)'],
            ['value' => 'israeli pound(ILP)', 'label' => 'Israeli Pound(ILP)'],
        ],
        'employee_bonus_type' => [
            ['value' => '', 'label' => 'Select bonus type...'],
            ['value' => 'none', 'label' => 'None'],
            ['value' => 'profit bonus type', 'label' => 'Profit Bonus Type'],
            ['value' => 'revenue based bonus', 'label' => 'Revenue Based Bonus'],
        ],
        'start_week_on' => [
            ['value' => '', 'label' => 'Select start day...'],
            ['value' => 'monday', 'label' => 'Monday'],
            ['value' => 'tuesday', 'label' => 'Tuesday'],
            ['value' => 'wednesday', 'label' => 'Wednesday'],
            ['value' => 'thursday', 'label' => 'Thursday'],
            ['value' => 'friday', 'label' => 'Friday'],
            ['value' => 'saturday', 'label' => 'Saturday'],
            ['value' => 'sunday', 'label' => 'Sunday'],
        ],
        'default_date_type' => [
            ['value' => '', 'label' => 'Select date type...'],
            ['value' => 'today', 'label' => 'Today'],
            ['value' => 'end of the month', 'label' => 'End Of The Month'],
            ['value' => 'start of the month', 'label' => 'Start Of The Month'],
        ],
        'type' => [
            ['value' => '', 'label' => 'Select type...'],
            ['value' => 'cost', 'label' => 'Cost'],
            ['value' => 'hours', 'label' => 'Hours'],
    ],
    'contact_type' => [
        ['value' => '', 'label' => 'Select contact type...'],
        ['value' => 'client', 'label' => 'Client'],
        ['value' => 'customer', 'label' => 'Customer'],
        ['value' => 'lead', 'label' => 'Lead'],
    ],
    'estimate' => [
        ['value' => '', 'label' => 'Select estimate...'],
        ['value' => 'days', 'label' => 'Day'],
        ['value' => 'hours', 'label' => 'Hours'],
        ['value' => 'minutes', 'label' => 'Minutes'],
    ],
    'invoice_status' => [
        ['value' => '', 'label' => 'Select invoice status...'],
        ['value' => 'none', 'label' => 'None'],
        ['value' => 'open', 'label' => 'Open'],
        ['value' => 'inprogress', 'label' => 'In Progress'],
        ['value' => 'inreview', 'label' => 'In Review'],
        ['value' => 'completed', 'label' => 'Completed'],
        ['value' => 'closed', 'label' => 'Closed'],
    ],
    'payment_method' => [
        ['value' => '', 'label' => 'Select payment method...'],
        ['value' => 'bank transfer', 'label' => 'Bank Transfer'],
        ['value' => 'cash', 'label' => 'Cash'],
        ['value' => 'cheque', 'label' => 'Cheque'],
        ['value' => 'credit card', 'label' => 'Credit Card'],
        ['value' => 'debit', 'label' => 'Debit'],
        ['value' => 'online', 'label' => 'Online'],
    ],
    'select_status' => [
        ['value' => '', 'label' => 'Select status...'],
        ['value' => 'not billable', 'label' => 'Not Billable'],
    ],
    'discount_suffix' => [
        ['value' => '', 'label' => 'Select discount suffix...'],
        ['value' => '%', 'label' => '%'],
        ['value' => 'flat', 'label' => 'Flat'],
    ],
    'taxtype' => [
        ['value' => '', 'label' => 'Select tax type...'],
        ['value' => 'individual', 'label' => 'Individual'],
        ['value' => 'group', 'label' => 'Group'],
    ],
    'estimate_status' => [
        ['value' => '', 'label' => 'Select estimate status...'],
        ['value' => 'created', 'label' => 'Created'],
        ['value' => 'delivered', 'label' => 'Delivered'],
        ['value' => 'reviewed', 'label' => 'Reviewed'],
        ['value' => 'accepted', 'label' => 'Accepted'],
        ['value' => 'rejected', 'label' => 'Rejected'],
    ],
    'select_invoice_type' => [
        ['value' => '', 'label' => 'Select invoice type...'],
        ['value' => 'employees', 'label' => 'Employee'],
        ['value' => 'projects', 'label' => 'Projects'],
        ['value' => 'tasks', 'label' => 'Tasks'],
        ['value' => 'products', 'label' => 'Products'],
        ['value' => 'expenses', 'label' => 'Expenses'],
    ],
    'billing'=> [
            ['value' => '', 'label' => 'Select billing...'],
            ['value' => 'tax_deductible', 'label' => 'Tax deductible'],
            ['value' => 'not_tax_deductible', 'label' => 'Not Tax deductible'],
            ['value' => 'billable_to_contact', 'label' => 'Billable To Contact'],
        ],
    'choose' => [
        ['value' => '', 'label' => 'Select any...'],
        ['value' => 'addorremoveemployee', 'label' => 'Employees'],
        ['value' => 'chooseteams', 'label' => 'Teams'],
        
    ],

    'start_time' => [
            ['value' => '', 'label' => 'Select time...'],
            ['value' => '12:00AM', 'label' => '12:00AM'],
            ['value' => '1:00AM', 'label' => '1:00AM'],
            ['value' => '2:00AM', 'label' => '2:00AM'],
            ['value' => '3:00AM', 'label' => '3:00AM'],
            ['value' => '4:00AM', 'label' => '4:00AM'],
            ['value' => '5:00AM', 'label' => '5:00AM'],
            ['value' => '6:00AM', 'label' => '6:00AM'],
            ['value' => '7:00AM', 'label' => '7:00AM'],
            ['value' => '8:00AM', 'label' => '8:00AM'],
            ['value' => '9:00AM', 'label' => '9:00AM'],
            ['value' => '10:00AM', 'label' => '10:00AM'],
            ['value' => '11:00AM', 'label' => '11:00AM'],
            ['value' => '12:00PM', 'label' => '12:00PM'],
            ['value' => '1:00PM', 'label' => '1:00PM'],
            ['value' => '2:00PM', 'label' => '2:00PM'],
            ['value' => '3:00PM', 'label' => '3:00PM'],
            ['value' => '4:00PM', 'label' => '4:00PM'],
            ['value' => '5:00PM', 'label' => '5:00PM'],
            ['value' => '6:00PM', 'label' => '6:00PM'],
            ['value' => '7:00PM', 'label' => '7:00PM'],
            ['value' => '8:00PM', 'label' => '8:00PM'],
            ['value' => '9:00PM', 'label' => '9:00PM'],
            ['value' => '10:00PM', 'label' => '10:00PM'],
            ['value' => '11:00PM', 'label' => '11:00PM'],
            ],

        'end_time' => [
            ['value' => '', 'label' => 'Select time...'],
            ['value' => '12:00AM', 'label' => '12:00AM'],
            ['value' => '1:00AM', 'label' => '1:00AM'],
            ['value' => '2:00AM', 'label' => '2:00AM'],
            ['value' => '3:00AM', 'label' => '3:00AM'],
            ['value' => '4:00AM', 'label' => '4:00AM'],
            ['value' => '5:00AM', 'label' => '5:00AM'],
            ['value' => '6:00AM', 'label' => '6:00AM'],
            ['value' => '7:00AM', 'label' => '7:00AM'],
            ['value' => '8:00AM', 'label' => '8:00AM'],
            ['value' => '9:00AM', 'label' => '9:00AM'],
            ['value' => '10:00AM', 'label' => '10:00AM'],
            ['value' => '11:00AM', 'label' => '11:00AM'],
            ['value' => '12:00PM', 'label' => '12:00PM'],
            ['value' => '1:00PM', 'label' => '1:00PM'],
            ['value' => '2:00PM', 'label' => '2:00PM'],
            ['value' => '3:00PM', 'label' => '3:00PM'],
            ['value' => '4:00PM', 'label' => '4:00PM'],
            ['value' => '5:00PM', 'label' => '5:00PM'],
            ['value' => '6:00PM', 'label' => '6:00PM'],
            ['value' => '7:00PM', 'label' => '7:00PM'],
            ['value' => '8:00PM', 'label' => '8:00PM'],
            ['value' => '9:00PM', 'label' => '9:00PM'],
            ['value' => '10:00PM', 'label' => '10:00PM'],
            ['value' => '11:00PM', 'label' => '11:00PM'],
            ],

    // Add more options arrays as needed
];

// Execute the appropriate function if fieldName matches a specific case
$optionsFunction = $optionsMap[$fieldName] ?? null;
if ($optionsFunction && is_callable($optionsFunction)) {
    return $optionsFunction();
}

// Log a warning if no options function is defined for the fieldName
Log::warning('No options defined for fieldName: ' . $fieldName);

// Return default options if fieldName doesn't match specific cases
return $defaultOptions[$fieldName] ?? [];
}
}
