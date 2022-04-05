<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BorrowRequestController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FileStorageController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ITSPPFOController;
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PCComponentController;
use App\Http\Controllers\PCComponentInstanceController;
use App\Http\Controllers\POController;
use App\Http\Controllers\RepairRequestController;
use App\Http\Controllers\RfidController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SeedController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TransferRequestController;
use App\Http\Controllers\UserController;
use App\Models\InventoryParentItem;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::get('/test', function (Request $request) {
    return response('Hello World', 200)
        ->header('Content-Type', 'text/plain');
});



Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);


Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::post('users/{id}/change-password', [UserController::class, 'changePassword']);
    Route::post('users', [UserController::class, 'create']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::get('/users', [UserController::class, 'list']);
    Route::get('/users/{id}', [UserController::class, 'find']);

    Route::resource('buildings', BuildingController::class);

    Route::get('/rooms/{room_id}/item-parents', [RoomController::class, 'getItemParents'])
        ->where(['room_id' => '[0-9]+']);

    Route::get('/rooms/{room_id}/parents/{inventory_parent_item_id}/items', [RoomController::class, 'getItems'])
        ->where(['room_id' => '[0-9]+', 'inventory_parent_item_id' => '[0-9]+']);
    Route::get('/rooms/{id}/items', [RoomController::class, 'getAllItems'])->where(['id' => '[0-9]+']);
    Route::resource('rooms', RoomController::class);

    Route::get('/inventory/parents', [InventoryController::class, 'getItemParents']);
    Route::get('/inventory/parents-available', [InventoryController::class, 'getAvailableItemParents']);
    Route::post('/inventory/parents', [InventoryController::class, 'createItemParent']);
    Route::get('/inventory/parents/{id}', [InventoryController::class, 'getItems']);
    Route::get('/inventory/parents/{id}/instance', [InventoryController::class, 'findItemParent']);
    Route::get('/inventory/parents/{id}/items-available', [InventoryController::class, 'getAvailableItems']);
    Route::put('/inventory/parents/{id}', [InventoryController::class, 'updateItemParent']);
    Route::delete('/inventory/parents/{id}', [InventoryController::class, 'deleteItemParent']);

    Route::get('/inventory/items/disposed', [InventoryController::class, 'getDisposedItems']);
    Route::get('/inventory/items/{id}/components', [InventoryController::class, 'inventoryItemShowComponents'])->where(['id' => '[0-9]+']);
    Route::post('/inventory/items', [InventoryController::class, 'createItem']);
    Route::get('/inventory/items/{id}', [InventoryController::class, 'findItem'])->where(['id' => '[0-9]+']);
    Route::put('/inventory/items/{id}', [InventoryController::class, 'updateItem'])->where(['id' => '[0-9]+']);
    Route::delete('/inventory/items/{id}', [InventoryController::class, 'deleteItem'])->where(['id' => '[0-9]+']);
    Route::post('/inventory/items/{id}', [InventoryController::class, 'disposeItem'])->where(['id' => '[0-9]+']);
    Route::get('/inventory/items/available', [InventoryController::class, 'allAvailableItems']);
    Route::get('/inventory/items/unavailable', [InventoryController::class, 'unavailableItems']);
    Route::post('/inventory/items/{id}/set-room', [InventoryController::class, 'setRoom']);

    Route::resource('pc-components', PCComponentController::class);

    Route::post('/pc-component-instances/{id}/set-item', [PCComponentInstanceController::class, 'setItem']);
    Route::get('/pc-component-instances/available', [PCComponentInstanceController::class, 'availableItems']);
    Route::resource('pc-component-instances', PCComponentInstanceController::class);

    Route::post('/departments/{user_id}/set-buildings', [DepartmentController::class, 'setBuildings']);
    Route::get('/departments/{user_id}', [DepartmentController::class, 'getBuildings'])
        ->where(['user_id' => '[0-9]+']);
    Route::get('/departments', [DepartmentController::class, 'getRooms']);
    Route::get('/departments/rooms', [DepartmentController::class, 'getRooms2']);

    Route::post('/transfers', [TransferRequestController::class, 'requestTransfer']);
    Route::get('/transfers', [TransferRequestController::class, 'getRequests']);

    Route::post('/repairs', [RepairRequestController::class, 'requestRepair']);
    Route::get('/repairs', [RepairRequestController::class, 'listRepairRequests']);

    Route::get('/file-storages/{id}', [FileStorageController::class, 'show']);

    Route::get('/workers/repair-requests', [ITSPPFOController::class, 'listRepairRequests']);
    Route::post('/workers/reject-repair-request', [ITSPPFOController::class, 'rejectRepairRequest']);
    Route::post('/workers/dispose-repair-request', [ITSPPFOController::class, 'disposeRepairRequest']);
    Route::post('/workers/repairs/job-order', [ITSPPFOController::class, 'createJobOrder']);

    Route::get('/workers/transfer-requests', [ITSPPFOController::class, 'listTransferRequests']);
    Route::post('/workers/reject-transfer-request', [ITSPPFOController::class, 'rejectRequest']);
    Route::post('/workers/workon-transfer-request', [ITSPPFOController::class, 'workOnRequest']);
    Route::post('/workers/complete-transfer-request', [ITSPPFOController::class, 'finishRequest']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);

    Route::get('/job-orders', [JobOrderController::class, 'listPendingJobOrders']);
    Route::post('/job-orders/{id}/repair', [JobOrderController::class, 'markAsRepaired']);
    Route::post('/job-orders/{id}/replace', [JobOrderController::class, 'replaceItem']);
    Route::post('/job-orders/{id}/create-po', [JobOrderController::class, 'createPO']);

    Route::get('/purchase-orders', [POController::class, 'index']);

    Route::get('/borrows', [BorrowRequestController::class, 'getRequests']);
    Route::post('/borrows', [BorrowRequestController::class, 'requestBorrow']);
});


Route::get('/seeds', [SeedController::class, 'seed']);
