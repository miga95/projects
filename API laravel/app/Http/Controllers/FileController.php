<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\File;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function uploadFile(Request $request)
    {
      
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'path' => 'string',
            'private' => 'integer',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->errors()->first(),
                "status" => "ERROR"
            ], 400);
        }

        $result=$request->file('file')->storeAs('apiDocs',$request->get('name'));
        
        $id=Auth::user()->id;
       
        $file=File::create([

            'name'=>$request->get('name'),
            'path'=>$request->get('path'),
            'private'=>$request->get('private'),
            'user_id'=>$id,

        ]);
        return response()->json([
            "data" => $file,
            "message" => "file upload",
            "status" => "SUCCESS"
        ], 201);

    }


    public function getFilesByIdUser($id, Request $request)
    {
        if($id==NULL){
            return response()->json([
                "message" => "Id Error",
                "status" => "Error"
            ], 400);
        }

        $files =  File::all()->where('user_id', $id);

        return response()->json([
            "message" => $files,
            "status" => "SUCCESS"
        ], 201);
        
    }


    public function updateFile($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string', 
            'path'=>'string',
            'private'=>'integer'    
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->errors()->first(),
                "status" => "ERROR"
            ], 400);
        }
        
        $currentdate = date("Y-m-d H:i:s", time());
        $request->merge(['updated_at' => $currentdate]);
      
        $file=File::find($id);
        Storage::copy('apiDocs/'.$file->name,'Backups/'.$currentdate."-".$file->name);
        Storage::move('apiDocs/'.$file->name,'apiDocs/'.$request->get('name'));
        File::find($id)->update($request->all());
        $file=File::find($id);

        
        
        
       //$result=$request->file('file')->storeAs('apiDocs',$request->get('name'));
        
        

        return response()->json([
                "data" => $file,
                "message" => "Fichier updated",
                "status" => "SUCCESS"
            ]
        );
    }

    public function deleteFile($id)
    {
        $tmpFile=File::find($id);
        if ($file = DB::table('files')->where('id', $id)->delete()) {
            
            Storage::delete('apiDocs/'.$tmpFile->name);
            return response()->json([
                "message" => 'fichier supprimé avec succès',
                "status" => "SUCCESS"
            ], 200);
        }
        return response()->json([
                "message" => "Erreur lors de la suppression du fichier",
                "status" => "ERROR"
        ], 500);
    }

}
