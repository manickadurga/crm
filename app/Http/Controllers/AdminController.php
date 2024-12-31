<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function index()
    {
        try {
            // Fetch all actions from the 'admin' table
            $admins = DB::table('admin')->get();

            // Prepare the response data
            $response = $admins->map(function ($admin) {
                return [
                    'id' => $admin->id,
                    'action_name' => $admin->action_name,
                    'values' => json_decode($admin->values, true), // Decode JSON values
                ];
            });

            return response()->json($response);
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }
    
    public function show($action_name)
{
    try {
        // Fetch the admin record by action_name
        $admin = DB::table('admin')->where('action_name', $action_name)->first();

        // Check if the record exists
        if (!$admin) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Decode the JSON in the values column
        $values = json_decode($admin->values, true);

        // Process the values
        $processedValues = [];
        foreach ($values as $field) {
            $processedField = [
                'fieldname' => $field['fieldname'],
                'fieldlabel' => $field['fieldlabel'],
                'uitype' => $field['uitype'],
            ];

            // Add rules if available
            if (!empty($field['rules'])) {
                $processedField['rules'] = $field['rules'];
            }

            // Fetch and add options if available
            if (in_array($field['uitype'], [33, 16, 56])) {
                $options = $this->getFieldOptions($field['fieldname']);
                if ($options) {
                    $processedField['options'] = $options;
                }
            }

            $processedValues[] = $processedField;
        }

        // Prepare the response data
        $response = [
            'id' => $admin->id,
            'action_name' => $admin->action_name,
            'values' => $processedValues,
        ];

        return response()->json($response);
    } catch (\Exception $e) {
        // Handle any unexpected errors
        return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
    }
}



    private function getFieldOptions($fieldName)
    {
        // Initialize an empty options array
        $options = [];

        // Define options fetching logic based on field name
        $optionsMap = [
            'select_pipeline' => function () {
            $options = DB::table('jo_pipelines')->pluck('name', 'id')->map(function ($pipeline, $id) {
                return ['value' => $pipeline, 'label' => $pipeline, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a pipeline...', 'id' => null]);
            return $options;
            },

            'select_stage' => function () {
                    // Get the selected pipeline ID from the request input
                    $pipelineId = request()->input('pipeline_id'); // This assumes pipeline_id is being passed

                    if ($pipelineId) {
                        // Fetch the pipeline by ID
                        $pipeline = DB::table('jo_pipelines')->where('id', $pipelineId)->first();

                    if ($pipeline && isset($pipeline->stages)) {
                        // Decode the stages from JSON
                        $stages = json_decode($pipeline->stages, true);

                        // Map stages into the desired format (same as select_pipeline)
                        $options = collect($stages)->map(function ($stage) {
                        return ['value' => $stage, 'label' => $stage];
                        })->toArray();

                        // Add a default "Select a stage..." option at the beginning
                        array_unshift($options, ['value' => '', 'label' => 'Select a pipeline stage...', 'id' => null]);
                        return $options;
                    }
                }
               // If no pipeline selected or no stages found, return default empty option
               return [['value' => '', 'label' => 'Select a pipeline stage...', 'id' => null]];
            },

            'invoice_template' => function () {
            $options = DB::table('jo_invoices')->pluck('invoicenumber', 'id')->map(function ($invoice, $id) {
                return ['value' => $invoice, 'label' => $invoice, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an invoice template...', 'id' => null]);
            return $options;
            },

            'workflow_id'  => function () {
                $options = DB::table('workflows')->pluck('workflow_name', 'id')->map(function ($workflow, $id) {
                    return ['value' => $workflow, 'label' => $workflow, 'id' => $id];
                })->toArray();
                array_unshift($options, ['value' => '', 'label' => 'Select a workflow...', 'id' => null]);
                return $options;
            },

            'tags' => function () {
                $options = DB::table('jo_tags')->select('id', 'tags_name as value', 'tags_name as label', 'tag_color as color')->get()->toArray();
                array_unshift($options, ['value' => '', 'label' => 'Select a tag...', 'color' => '', 'id' => null]);
                return $options;
            },

            'select' => function () {
                $options = DB::table('jo_tags')->select('id', 'tags_name as value', 'tags_name as label', 'tag_color as color')->get()->toArray();
                array_unshift($options, ['value' => '', 'label' => 'Select a tag...', 'color' => '', 'id' => null]);
                return $options;
            },

            'assign_user' => function () {
                $options = DB::table('users')->pluck('name', 'id')->map(function ($user, $id) {
                    return ['value' => $user, 'label' => $user, 'id' => $id];
                })->toArray();
                array_unshift($options, ['value' => '', 'label' => 'Select user...', 'id' => null]);
                return $options;
            },

            'assign_to' => function () {
                $options = DB::table('jo_manage_employees')->pluck('first_name', 'id')->map(function ($employee, $id) {
                    return ['value' => $employee, 'label' => $employee, 'id' => $id];
                })->toArray();
                array_unshift($options, ['value' => '', 'label' => 'Select an employee...', 'id' => null]);
                return $options;
            },
    ];
        // Default options if fieldName doesn't match specific cases
        $defaultOptions = [
            'opportunity_status' => [
                ['value' => '', 'label' => 'Select status...'],
                ['value' => 'none', 'label' => 'None'],
                ['value' => 'open', 'label' => 'Open'],
                ['value' => 'lost', 'label' => 'Lost'],
                ['value' => 'won', 'label' => 'Won'],
                ['value' => 'abandon', 'label' => 'Abandon'],
            ],

            'payment_mode' => [
                ['value' => '', 'label' => 'Select any...'],
                ['value' => 'live', 'label' => 'Live'],
                ['value' => 'test', 'label' => 'Test'],
            ],

            'from_user' => [
                ['value' => '', 'label' => 'Select any...'],
                ['value' => 'manickadurga@gmail.com', 'label' => 'manickadurga@gmail.com'],
            ],

            'action_type' => [
                ['value' => '', 'label' => 'Select type...'],
                ['value' => 'texttonumber', 'label' => 'Text to Number'],
                ['value' => 'formatnumber', 'label' => 'Format Number'],
                ['value' => 'formatphonenumber', 'label' => 'Format Phone Number'],
                ['value' => 'randomnumber', 'label' => 'Random Number'],
            ],

            'type'  => [
                ['value' => '', 'label' => 'Select type...'],
                ['value' => 'formatdate', 'label' => 'Format Date'],
                ['value' => 'formatdateandtime', 'label' => 'Format Date and Time'],
                ['value' => 'comparedates', 'label' => 'Compare Dates'],
            ],

            'types' => [
                ['value' => '', 'label' => 'Select type...'],
                ['value' => 'customfield', 'label' => 'Custom Field'],
                ['value' => 'specificdate/time', 'label' => 'Specific Date/Time'],
                ['value' => 'specificday', 'label' => 'Specific Day'],
            ],

            'Type' => [
                ['value' => '', 'label' => 'Select type...'],
                ['value' => 'uppercase', 'label' => 'Upper Case'],
                ['value' => 'lowercase', 'label' => 'Lower Case'],
                ['value' => 'titlecase', 'label' => 'Title Case'],
                ['value' => 'capitalize', 'label' => 'Capitalize'],
                ['value' => 'defaultvalue', 'label' => 'Default Value'],
                ['value' => 'trim', 'label' => 'Trim'],
                ['value' => 'trimwhitespace', 'label' => 'Trim Whitespace'],
                ['value' => 'replacetext', 'label' => 'Replace Text'],
                ['value' => 'find', 'label' => 'Find'],
                ['value' => 'wordcount', 'label' => 'Word Count'],
                ['value' => 'length', 'label' => 'Length'],
                ['value' => 'splittext', 'label' => 'Split Text'],
                ['value' => 'removehtmltags', 'label' => 'Remove HTML Tags'],
                ['value' => 'extractemail', 'label' => 'Extract Email'],
                ['value' => 'extracturl', 'label' => 'Extract URL'],
            ],

            'Types' => [
                ['value' => '', 'label' => 'Select type...'],
                ['value' => 'receivedanemailevent', 'label' => 'Received an Email Event'],
                ['value' => 'addedacontacttag', 'label' => 'Added a Contact Tag'],
                ['value' => 'removedacontacttag', 'label' => 'Removed a Contact Tag'],
            ],



            'distribution_type'  => [
                ['value' => '', 'label' => 'Select type...'],
                ['value' => 'randomsplit', 'label' => 'Random Split'],
            ],

            'due_in' => [
                ['value' => '', 'label' => 'Select due...'],
                ['value' => 'none', 'label' => 'None'],
                ['value' => '1day', 'label' => '1 day'],
                ['value' => '2days', 'label' => '2 days'],
                ['value' => '5days', 'label' => '5 days'],
                ['value' => 'now', 'label' => 'Now'],
            ],

            'decimal_mark' => [
                ['value' => '', 'label' => 'Select decimal mark...'],
                ['value' => 'comma(123,45)', 'label' => 'Comma (123,45)'],
                ['value' => 'period(123.45)', 'label' => 'Period (123.45)'],
            ],

            'to_format' => [
                ['value' => '', 'label' => 'Select to format...'],
                ['value' => 'commaforgroupingandperiodfordecimal', 'label' => 'Comma for Grouping and Period for Decimal'],
                ['value' => 'periodforgroupingandcommafordecimal', 'label' => 'Period for Grouping and Comma for Decimal'],
                ['value' => 'spaceforgroupingandcommafordecimal', 'label' => 'Space for Grouping and Comma for Decimal'],
                ['value' => 'spaceforgroupingandperiodfordecimal', 'label' => 'Space for Grouping and Period for Decimal'],
            ],

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

        'operator' => [
            ['value' => '', 'label' => 'Select a operator...'],
            ['value' => 'add', 'label' => 'Add'],
            ['value' => 'subtract', 'label' => 'Subtract'],
        ],

        'day_type' => [
            ['value' => '', 'label' => 'Select a type...'],
            ['value' => 'currentdayofmonth', 'label' => 'Current Day of Month'],
            ['value' => 'currentdayofweek', 'label' => 'Current Day of Week'],
        ],

        'time' => [
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

    'select_email_event' => [
        ['value' => '', 'label' => 'Select an event...'],
        ['value' => 'open', 'label' => 'Open'],
        ['value' => 'clicked', 'label' => 'Clicked'],
    ],

    'outcome' => [
        ['value' => '', 'label' => 'Select an outcome...'],
        ['value' => 'endthisworkflow', 'label' => 'End This Workflow'],
        ['value' => 'continueanyway', 'label' => 'Continue Anyway'],
        ['value' => 'waituntilthegoalismet', 'label' => 'Wait until the Goal is Met'],
    ],

    'country_code' => [
    ['value' => '', 'label' => 'Select country code...'],
    ['value' => 'afghanistan', 'label' => 'Afghanistan'],
    ['value' => 'alandislands', 'label' => 'Åland Islands'],
    ['value' => 'algeria', 'label' => 'Algeria'],
    
    ['value' => 'argentina', 'label' => 'Argentina'],
    ['value' => 'armenia', 'label' => 'Armenia'],
    ['value' => 'australia', 'label' => 'Australia'],
    
    ['value' => 'austria', 'label' => 'Austria'],
    ['value' => 'azerbaijan', 'label' => 'Azerbaijan'],
    
    ['value' => 'bahamas', 'label' => 'Bahamas'],
    ['value' => 'bahrain', 'label' => 'Bahrain'],
    ['value' => 'bangladesh', 'label' => 'Bangladesh'],
    
    ['value' => 'barbados', 'label' => 'Barbados'],
    ['value' => 'belgium', 'label' => 'Belgium'],
    ['value' => 'belize', 'label' => 'Belize'],
    
    ['value' => 'benin', 'label' => 'Benin'],
    ['value' => 'bhutan', 'label' => 'Bhutan'],
    ['value' => 'bolivia', 'label' => 'Bolivia'],
    
    ['value' => 'bosniaandherzegovina', 'label' => 'Bosnia and Herzegovina'],
    ['value' => 'botswana', 'label' => 'Botswana'],
    
    ['value' => 'brazil', 'label' => 'Brazil'],
    ['value' => 'brunei', 'label' => 'Brunei'],
    
    ['value' => 'bulgaria', 'label' => 'Bulgaria'],
    ['value' => 'burkinafaso', 'label' => 'Burkina Faso'],
    ['value' => 'burundi', 'label' => 'Burundi'],
    
    ['value' => 'cambodia', 'label' => 'Cambodia'],
    ['value' => 'cameroon', 'label' => 'Cameroon'],
    ['value' => 'canada', 'label' => 'Canada'],
    
    ['value' => 'capeverde', 'label' => 'Cape Verde'],
    ['value' => 'centralafricanrepublic', 'label' => 'Central African Republic'],
    ['value' => 'chad', 'label' => 'Chad'],
    
    ['value' => 'chile', 'label' => 'Chile'],
    ['value' => 'china', 'label' => 'China'],
    ['value' => 'colombia', 'label' => 'Colombia'],
    
    ['value' => 'comoros', 'label' => 'Comoros'],
    ['value' => 'congorepublic', 'label' => 'Congo (Republic)'],
    ['value' => 'congodemocratic', 'label' => 'Congo (Democratic)'],
    
    ['value' => 'costa rica', 'label' => 'Costa Rica'],
    ['value' => 'croatia', 'label' => 'Croatia'],
    ['value' => 'cuba', 'label' => 'Cuba'],
    
    ['value' => 'cyprus', 'label' => 'Cyprus'],
    ['value' => 'czechrepublic', 'label' => 'Czech Republic'],
    
    ['value' => 'denmark', 'label' => 'Denmark'],
    ['value' => 'djibouti', 'label' => 'Djibouti'],
    ['value' => 'dominica', 'label' => 'Dominica'],
    
    ['value' => 'dominicanrepublic', 'label' => 'Dominican Republic'],
    ['value' => 'ecuador', 'label' => 'Ecuador'],
    ['value' => 'egypt', 'label' => 'Egypt'],
    
    ['value' => 'el salvador', 'label' => 'El Salvador'],
    ['value' => 'equatorialguinea', 'label' => 'Equatorial Guinea'],
    ['value' => 'eritrea', 'label' => 'Eritrea'],
    
    ['value' => 'estonia', 'label' => 'Estonia'],
    ['value' => 'eswatini', 'label' => 'Eswatini'],
    ['value' => 'ethiopia', 'label' => 'Ethiopia'],
    
    ['value' => 'fiji', 'label' => 'Fiji'],
    ['value' => 'finland', 'label' => 'Finland'],
    ['value' => 'france', 'label' => 'France'],
    
    ['value' => 'gabon', 'label' => 'Gabon'],
    ['value' => 'gambia', 'label' => 'Gambia'],
    ['value' => 'georgia', 'label' => 'Georgia'],
    
    ['value' => 'germany', 'label' => 'Germany'],
    ['value' => 'ghana', 'label' => 'Ghana'],
    ['value' => 'greece', 'label' => 'Greece'],
    
    ['value' => 'grenada', 'label' => 'Grenada'],
    ['value' => 'guatemala', 'label' => 'Guatemala'],
    ['value' => 'guinea', 'label' => 'Guinea'],
    
    ['value' => 'guineabissau', 'label' => 'Guinea-Bissau'],
    ['value' => 'guyana', 'label' => 'Guyana'],
    ['value' => 'haiti', 'label' => 'Haiti'],
    
    ['value' => 'honduras', 'label' => 'Honduras'],
    ['value' => 'hungary', 'label' => 'Hungary'],
    ['value' => 'iceland', 'label' => 'Iceland'],
    
    ['value' => 'india', 'label' => 'India'],
    ['value' => 'indonesia', 'label' => 'Indonesia'],
    ['value' => 'iran', 'label' => 'Iran'],
    
    ['value' => 'iraq', 'label' => 'Iraq'],
    ['value' => 'ireland', 'label' => 'Ireland'],
    ['value' => 'israel', 'label' => 'Israel'],
    
    ['value' => 'italy', 'label' => 'Italy'],
    ['value' => 'ivorycoast', 'label' => 'Ivory Coast'],
    ['value' => 'jamaica', 'label' => 'Jamaica'],
    
    ['value' => 'japan', 'label' => 'Japan'],
    ['value' => 'jordan', 'label' => 'Jordan'],
    ['value' => 'kazakhstan', 'label' => 'Kazakhstan'],
    
    ['value' => 'kenya', 'label' => 'Kenya'],
    ['value' => 'kiribati', 'label' => 'Kiribati'],
    ['value' => 'korea', 'label' => 'Korea'],
    
    ['value' => 'kosovo', 'label' => 'Kosovo'],
    ['value' => 'kuwait', 'label' => 'Kuwait'],
    ['value' => 'kyrgyzstan', 'label' => 'Kyrgyzstan'],
    
    ['value' => 'laos', 'label' => 'Laos'],
    ['value' => 'latvia', 'label' => 'Latvia'],
    ['value' => 'lebanon', 'label' => 'Lebanon'],
    
    ['value' => 'lesotho', 'label' => 'Lesotho'],
    ['value' => 'liberia', 'label' => 'Liberia'],
    ['value' => 'libya', 'label' => 'Libya'],
    
    ['value' => 'liechtenstein', 'label' => 'Liechtenstein'],
    ['value' => 'lithuania', 'label' => 'Lithuania'],
    ['value' => 'luxembourg', 'label' => 'Luxembourg'],
    
    ['value' => 'madagascar', 'label' => 'Madagascar'],
    ['value' => 'malawi', 'label' => 'Malawi'],
    ['value' => 'malaysia', 'label' => 'Malaysia'],
    
    ['value' => 'maldives', 'label' => 'Maldives'],
    ['value' => 'mali', 'label' => 'Mali'],
    ['value' => 'malta', 'label' => 'Malta'],
    
    ['value' => 'marshallislands', 'label' => 'Marshall Islands'],
    ['value' => 'mauritania', 'label' => 'Mauritania'],
    ['value' => 'mauritius', 'label' => 'Mauritius'],
    
    ['value' => 'mexico', 'label' => 'Mexico'],
    ['value' => 'micronesia', 'label' => 'Micronesia'],
    ['value' => 'moldova', 'label' => 'Moldova'],
    
    ['value' => 'monaco', 'label' => 'Monaco'],
    ['value' => 'mongolia', 'label' => 'Mongolia'],
    ['value' => 'montenegro', 'label' => 'Montenegro'],
    
    ['value' => 'morocco', 'label' => 'Morocco'],
    ['value' => 'mozambique', 'label' => 'Mozambique'],
    ['value' => 'myanmar', 'label' => 'Myanmar'],
],

'currency_locale' => [
    ['value' => '', 'label' => 'Select country...'],
    ['value' => 'afghanistan(AFN)', 'label' => 'Afghanistan (AFN)'],
    ['value' => 'alandislands(AKR)', 'label' => 'Åland Islands (AKR)'],
    ['value' => 'algeria(DZD)', 'label' => 'Algeria (DZD)'],
    
    ['value' => 'argentina(ARS)', 'label' => 'Argentina (ARS)'],
    ['value' => 'armenia(AMD)', 'label' => 'Armenia (AMD)'],
    ['value' => 'australia(AUD)', 'label' => 'Australia (AUD)'],
    
    ['value' => 'austria(EUR)', 'label' => 'Austria (EUR)'],
    ['value' => 'azerbaijan(AZN)', 'label' => 'Azerbaijan (AZN)'],
    
    ['value' => 'bahamas(BSD)', 'label' => 'Bahamas (BSD)'],
    ['value' => 'bahrain(BHD)', 'label' => 'Bahrain (BHD)'],
    ['value' => 'bangladesh(BDT)', 'label' => 'Bangladesh (BDT)'],
    
    ['value' => 'barbados(BBD)', 'label' => 'Barbados (BBD)'],
    ['value' => 'belgium(EUR)', 'label' => 'Belgium (EUR)'],
    ['value' => 'belize(BZD)', 'label' => 'Belize (BZD)'],
    
    ['value' => 'benin(CFA)', 'label' => 'Benin (CFA)'],
    ['value' => 'bhutan(INR)', 'label' => 'Bhutan (INR)'],
    ['value' => 'bolivia(BOB)', 'label' => 'Bolivia (BOB)'],
    
    ['value' => 'bosniaandherzegovina(BAM)', 'label' => 'Bosnia and Herzegovina (BAM)'],
    ['value' => 'botswana(BWP)', 'label' => 'Botswana (BWP)'],
    
    ['value' => 'brazil(BRL)', 'label' => 'Brazil (BRL)'],
    ['value' => 'brunei(BND)', 'label' => 'Brunei (BND)'],
    
    ['value' => 'bulgaria(BGN)', 'label' => 'Bulgaria (BGN)'],
    ['value' => 'burkinafaso(CFA)', 'label' => 'Burkina Faso (CFA)'],
    ['value' => 'burundi(BIF)', 'label' => 'Burundi (BIF)'],
    
    ['value' => 'cambodia(KHR)', 'label' => 'Cambodia (KHR)'],
    ['value' => 'cameroon(CFA)', 'label' => 'Cameroon (CFA)'],
    ['value' => 'canada(CAD)', 'label' => 'Canada (CAD)'],
    
    ['value' => 'capeverde(CVE)', 'label' => 'Cape Verde (CVE)'],
    ['value' => 'centralafricanrepublic(CFA)', 'label' => 'Central African Republic (CFA)'],
    ['value' => 'chad(CFA)', 'label' => 'Chad (CFA)'],
    
    ['value' => 'chile(CLP)', 'label' => 'Chile (CLP)'],
    ['value' => 'china(CNY)', 'label' => 'China (CNY)'],
    ['value' => 'colombia(COP)', 'label' => 'Colombia (COP)'],
    
    ['value' => 'comoros(KMF)', 'label' => 'Comoros (KMF)'],
    ['value' => 'congorepublic(CDF)', 'label' => 'Congo (Republic) (CDF)'],
    ['value' => 'congodemocratic(CDF)', 'label' => 'Congo (Democratic) (CDF)'],
    
    ['value' => 'costa rica(CRC)', 'label' => 'Costa Rica (CRC)'],
    ['value' => 'croatia(HRK)', 'label' => 'Croatia (HRK)'],
    ['value' => 'cuba(CUP)', 'label' => 'Cuba (CUP)'],
    
    ['value' => 'cyprus(CYP)', 'label' => 'Cyprus (CYP)'],
    ['value' => 'czechrepublic(CZK)', 'label' => 'Czech Republic (CZK)'],
    
    ['value' => 'denmark(DKK)', 'label' => 'Denmark (DKK)'],
    ['value' => 'djibouti(DJF)', 'label' => 'Djibouti (DJF)'],
    ['value' => 'dominica(DOM)', 'label' => 'Dominica (DOM)'],
    
    ['value' => 'dominicanrepublic(DOP)', 'label' => 'Dominican Republic (DOP)'],
    ['value' => 'ecuador(USD)', 'label' => 'Ecuador (USD)'],
    ['value' => 'egypt(EGP)', 'label' => 'Egypt (EGP)'],
    
    ['value' => 'el salvador(SVC)', 'label' => 'El Salvador (SVC)'],
    ['value' => 'equatorialguinea(XAF)', 'label' => 'Equatorial Guinea (XAF)'],
    ['value' => 'eritrea(ERN)', 'label' => 'Eritrea (ERN)'],
    
    ['value' => 'estonia(EUR)', 'label' => 'Estonia (EUR)'],
    ['value' => 'eswatini(SZL)', 'label' => 'Eswatini (SZL)'],
    ['value' => 'ethiopia(ETB)', 'label' => 'Ethiopia (ETB)'],
    
    ['value' => 'fiji(FJD)', 'label' => 'Fiji (FJD)'],
    ['value' => 'finland(EUR)', 'label' => 'Finland (EUR)'],
    ['value' => 'france(EUR)', 'label' => 'France (EUR)'],
    
    ['value' => 'gabon(CFA)', 'label' => 'Gabon (CFA)'],
    ['value' => 'gambia(GMD)', 'label' => 'Gambia (GMD)'],
    ['value' => 'georgia(GEL)', 'label' => 'Georgia (GEL)'],
    
    ['value' => 'germany(EUR)', 'label' => 'Germany (EUR)'],
    ['value' => 'ghana(GHS)', 'label' => 'Ghana (GHS)'],
    ['value' => 'greece(EUR)', 'label' => 'Greece (EUR)'],
    
    ['value' => 'grenada(XCD)', 'label' => 'Grenada (XCD)'],
    ['value' => 'guatemala(GTZ)', 'label' => 'Guatemala (GTZ)'],
    ['value' => 'guinea(GNF)', 'label' => 'Guinea (GNF)'],
    
    ['value' => 'guineabissau(GNB)', 'label' => 'Guinea-Bissau (GNB)'],
    ['value' => 'guyana(GYD)', 'label' => 'Guyana (GYD)'],
    ['value' => 'haiti(HTG)', 'label' => 'Haiti (HTG)'],
    
    ['value' => 'honduras(HNL)', 'label' => 'Honduras (HNL)'],
    ['value' => 'hungary(HUF)', 'label' => 'Hungary (HUF)'],
    ['value' => 'iceland(ISK)', 'label' => 'Iceland (ISK)'],
    
    ['value' => 'india(INR)', 'label' => 'India (INR)'],
    ['value' => 'indonesia(IDR)', 'label' => 'Indonesia (IDR)'],
    ['value' => 'iran(IRR)', 'label' => 'Iran (IRR)'],
    
    ['value' => 'iraq(IQD)', 'label' => 'Iraq (IQD)'],
    ['value' => 'ireland(EUR)', 'label' => 'Ireland (EUR)'],
    ['value' => 'israel(ILS)', 'label' => 'Israel (ILS)'],
    
    ['value' => 'italy(EUR)', 'label' => 'Italy (EUR)'],
    ['value' => 'ivorycoast(CFA)', 'label' => 'Ivory Coast (CFA)'],
    ['value' => 'jamaica(JMD)', 'label' => 'Jamaica (JMD)'],
    
    ['value' => 'japan(JPY)', 'label' => 'Japan (JPY)'],
    ['value' => 'jordan(JOD)', 'label' => 'Jordan (JOD)'],
    ['value' => 'kazakhstan(KZT)', 'label' => 'Kazakhstan (KZT)'],
    
    ['value' => 'kenya(KES)', 'label' => 'Kenya (KES)'],
    ['value' => 'kiribati(KIR)', 'label' => 'Kiribati (KIR)'],
    ['value' => 'korea(KRW)', 'label' => 'Korea (KRW)'],
    
    ['value' => 'kurdistan(KRG)', 'label' => 'Kurdistan (KRG)'],
    ['value' => 'kyrgyzstan(KGS)', 'label' => 'Kyrgyzstan (KGS)'],
    ['value' => 'laos(LA)','label' => 'Laos (LAO)'],
],

'wait_for' => [
    ['value' => '', 'label' => 'Select an event...'],
    ['value' => 'timedelay', 'label' => 'Time Delay'],
    ['value' => 'event/appointmenttime', 'label' => 'Event/AppointmentTime'],
    ['value' => 'overdue', 'label' => 'Overdue'],
],


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

