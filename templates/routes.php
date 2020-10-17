

Route::# middleware #get('/resource/', [App\Http\Controllers\GeneratedControllers\ResourceController::class, 'index']);
Route::# middleware #get('/resource/query', [App\Http\Controllers\GeneratedControllers\ResourceController::class, 'query']);
Route::# middleware #post('/resource/', [App\Http\Controllers\GeneratedControllers\ResourceController::class, 'store']);
Route::# middleware #get('/resource/{id}', [App\Http\Controllers\GeneratedControllers\ResourceController::class, 'show']);
Route::# middleware #put('/resource/{id}', [App\Http\Controllers\GeneratedControllers\ResourceController::class, 'update']);
Route::# middleware #delete('/resource/{id}', [App\Http\Controllers\GeneratedControllers\ResourceController::class, 'destroy']);
