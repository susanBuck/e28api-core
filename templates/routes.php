Route::get('/resource/', [App\Http\Controllers\GeneratedControllers\ResourceController::class, 'index']);
Route::get('/resource/query', [App\Http\Controllers\GeneratedControllers\ResourceController::class, 'query']);
Route::post('/resource/', [App\Http\Controllers\GeneratedControllers\ResourceController::class, 'store']);
Route::get('/resource/{id}', [App\Http\Controllers\GeneratedControllers\ResourceController::class, 'show']);
Route::put('/resource/{id}', [App\Http\Controllers\GeneratedControllers\ResourceController::class, 'update']);
Route::delete('/resource/{id}', [App\Http\Controllers\GeneratedControllers\ResourceController::class, 'destroy']);