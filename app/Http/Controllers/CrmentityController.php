<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Crmentity;
use Illuminate\Support\Facades\Log;


class CrmentityController extends Controller
{
    public function createCrmentity($setype, $name)
    {
        DB::beginTransaction();
    
        try {
            // Ensure 'setype' is valid and exists in the jo_tabs table
            $tab = DB::table('jo_tabs')->where('name', $setype)->first();
    
            if (!$tab) {
                throw new Exception('Invalid setype specified');
            }
    
            // Create a new Crmentity record with a new crmid
            $newCrmentity = new Crmentity();
            $newCrmentity->crmid = Crmentity::max('crmid') + 1; // Generate new crmid
            $newCrmentity->smcreatorid = 0; // Example value; adjust if needed
            $newCrmentity->smownerid = 0; // Example value; adjust if needed
            $newCrmentity->setype = $setype;
            $newCrmentity->description = ''; // Adjust as needed
            $newCrmentity->createdtime = now();
            $newCrmentity->modifiedtime = now();
            $newCrmentity->viewedtime = now();
            $newCrmentity->status = 'Active'; // Example value; adjust if needed
            $newCrmentity->version = 1; // Example value; adjust if needed
            $newCrmentity->presence = 1; // Example value; adjust if needed
            $newCrmentity->deleted = 0; // Example value; adjust if needed
            $newCrmentity->smgroupid = 1; // Example value; adjust if needed
            $newCrmentity->source = ''; // Adjust as needed
            $newCrmentity->label = $name;
            $newCrmentity->save();
    
            DB::commit();
    
            // Return the new crmid
            return $newCrmentity->crmid;
    
        } catch (Exception $e) {
            DB::rollBack();
            throw $e; // Re-throw exception to be handled by the caller
        }
    }
    
    
    public function updateCrmentity($crmid, $data)
    {
        DB::beginTransaction();
    
        try {
            // Find the Crmentity record
            $crmentity = Crmentity::find($crmid);
    
            if (!$crmentity) {
                throw new Exception('Crmentity not found for the provided crmid');
            }
    
            // Update Crmentity record with provided data
            $crmentity->label = $data['label'];
            $crmentity->description = $data['description'] ?? $crmentity->description;
            $crmentity->modifiedtime = now();
            // Update other fields if necessary
    
            $crmentity->save();
            DB::commit();
    
            return true;
    
        } catch (Exception $e) {
            DB::rollBack();
            throw $e; // Re-throw exception to be handled by the caller
        }
    }
    
}
