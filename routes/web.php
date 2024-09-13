<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::middleware('auth')->group(function () {

    //starts of Chat ROUTES
    Route::get('/', [ChatController::class, 'Viewindex'])->name('chat.index');

    Route::get('/chat', [ChatController::class, 'Viewindex'])->name('chat.index');

    Route::get('/search-users', [ChatController::class, 'searchUsers']);

    Route::get('/chat/{user}', [ChatController::class, 'SelectUserChat'])->name('chat.show');

    Route::get('/chat/messages/{userId}', [ChatController::class, 'fetchChatMessages']);

    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');

    Route::get('/conversation-users', [ChatController::class, 'getConversationUsers']);

    Route::get('/user/details/{id}', [ChatController::class, 'getUserDetails']);

    Route::delete('/chat/{conversationId}', [ChatController::class, 'deleteConversation'])->name('chat.conversation.delete');

    Route::delete('/chat/messages/{message}', [ChatController::class, 'deleteMessage'])->name('chat.delete');

    Route::post('/profile/update', [ChatController::class, 'UpdateUserProfile'])->name('Chatprofile.update');

    Route::post('/send-recording', [ChatController::class, 'uploadRecording'])->name('chat.uploadRecording');

    Route::post('/upload-file', [ChatController::class, 'uploadFile'])->name('chat.uploadFile');

    //Ends of Chat ROUTES
    //Starts of Group ROUTES

    Route::get('/groups/create', [GroupController::class, 'createGroup'])->name('groups.create');

    Route::get('/groups/{group}', [GroupController::class, 'ShowUserGroups'])->name('groups.show');

    Route::post('/groups/store', [GroupController::class, 'StoreUsersToGroup'])->name('groups.store');

    Route::post('/groups/{group}/send', [GroupController::class, 'SendGroupMessage'])->name('groups.sendMessage');

    Route::get('/groups/messages/{group}', [GroupController::class, 'FetchGroupMessages'])->name('groups.fetchMessages');

    Route::post('/groups/{group}/join', [GroupController::class, 'join'])->name('groups.join');

    Route::post('/groups/{group}/leave', [GroupController::class, 'leaveGroup'])->name('groups.leave');

    Route::patch('/groups/{group}/toggle', [GroupController::class, 'ToggleOpenGroup'])->name('groups.toggle');

    Route::delete('/groups/messages/{message}', [GroupController::class, 'DeleteMessageGroup'])->name('messages.destroy');

    Route::patch('/groups/{group}/update-image-covers', [GroupController::class, 'UpdateGroupPic'])->name('groups.updateImage');

    Route::post('/groups/{group}/add-users', [GroupController::class, 'addUsers'])->name('groups.addUsers');

    Route::get('/group/details/{id}', [GroupController::class, 'getGroupDetails']);

    Route::patch('/group/toggle-chat/{id}', [GroupController::class, 'toggleChat']);

    Route::post('/group/messages/upload-recording', [GroupController::class, 'uploadRecording'])->name('groupMessages.uploadRecording');

    Route::post('/groups/{group}/upload', [GroupController::class, 'uploadFile'])->name('groups.uploadFile');

    Route::get('/group/search', [GroupController::class, 'search'])->name('groups.search');
    //Ends of Groups ROUTES
});

require __DIR__.'/auth.php';

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::delete('/account/delete', [ProfileController::class, 'destroy'])->name('account.delete');
