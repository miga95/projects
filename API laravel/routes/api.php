<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FileController;
use App\Http\Middleware\Authenticate;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post("/register",[UserController::class,'register']);
Route::post("/auth",[UserController::class,'authenticate']);

Route::middleware('auth:api')->group(function() {

    //Routes User
    Route::delete("/user/{id}",[UserController::class,'deleteUser']);
    Route::put("/user/me",[UserController::class,'updateUser']);
    Route::get("/users",[UserController::class,'getAllUsers']);

    //Routes File
    Route::post("/user/me/upload",[FileController::class,'uploadFile']);
    Route::get("/user/{id}/files",[FileController::class,'getFilesByIdUser']);
    Route::put("/file/{id}",[FileController::class,'updateFile']);
    Route::delete("/file/{id}",[FileController::class,'deleteFile']);
    
});





