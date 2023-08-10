<?php

namespace App\Http\Controllers\FileUpload;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    //
    // Upload ID Images
    public function uploadIdImages(Request $request)
    {
        $request->validate(['frontID' => 'required', 'backID' => 'required']);

        // Store all ID images under one folder
        $destination_path = 'public/images/id_cards';

        // Store the IDs in their designated folder
        $one = $request->frontID->store($destination_path);
        $two = $request->backID->store($destination_path);

        if (!$one || !$two) {
            return response(['message' => 'failure', 'error' => 'Failed to upload ID images'], 400);
        }

        // Return only the ID name
        $frontID =  str_replace($destination_path . '/', '', $one);
        $backID =  str_replace($destination_path . '/', '', $two);

        return response(['message' => 'success', 'data' => ['frontID' => $frontID, 'backID' => $backID]], 201);
    }

    public function profileUploads(Request $request){

        $request->validate(['riderPhoto' => 'required', 'motorcyclePhoto' => 'required']);
        // Store all ID images under one folder
        $destination_path = 'public/images/id_cards';
        //store the in a folder
        $one = $request->riderPhoto->store($destination_path);
        $two = $request->motorcyclePhoto->store($destination_path);

        //return the name of the images
        $riderPhoto = str_replace($destination_path . '/', '', $one);
        $motorcyclePhoto = str_replace($destination_path . '/', '', $two);

        return response(['message' => 'success', 'data' => ['riderPhoto' => $riderPhoto, 'motorcyclePhoto' => $motorcyclePhoto]], 201);



    }
}
