<?php

use Illuminate\Support\Facades\Route;

// Index route
Route::get('/', 'AcademyController@index')->name('academy.index');
Route::get('/home', function () {
    return redirect()->route('web.academy.index');
})->name('home');
Route::redirect('/index', '/', 301);
Route::redirect('/index.php', '/', 301)->name('index');
Route::get('/index.php/admin', function () {
    return redirect('/hk');
});
Route::get('/admin', function () {
    return redirect('/hk');
});
Route::get('/habble', function () {
    return redirect()->route('web.pages.show', ['slug' => 'habble']);
});
Route::get('/habbo', function () {
    return redirect()->route('web.pages.show', ['slug' => 'habbo']);
});
Route::get('/contents', function () {
    return redirect()->route('web.pages.show', ['slug' => 'contents']);
});
Route::get('/contenidos', function () {
    return redirect()->route('web.pages.show', ['slug' => 'contenidos']);
});
Route::get('/fancenter', function () {
    return redirect()->route('web.pages.show', ['slug' => 'fancenter']);
});
Route::get('/radio', function () {
    return redirect()->route('web.pages.show', ['slug' => 'radio']);
});
Route::get('/pages/informacion-campana/{campaignSlug?}', 'AcademyController@campaignInfoPage')->name('pages.campaign.info');
Route::get('/pages/{slug}', 'AcademyController@page')->name('pages.show');
Route::post('/radio/dj-application', 'AcademyController@submitDjApplication')->name('radio.dj-application');

// Login
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');

// Register
Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('/register', 'Auth\RegisterController@register');

// User Logout
Route::post('logout', 'Auth\LoginController@logout')->name('logout')->middleware('auth');

// Global routes
Route::get('/topic/{id}/{slug}', 'TopicController@show')->name('topics.show');
Route::get('/article/{id}/{slug}', 'ArticleController@show')->name('articles.show');
Route::post('/article/{id}/{slug}/comment', 'ArticleController@storeComment')->name('articles.comments.store')->middleware('auth');
Route::put('/article/{id}/{slug}/comment/{comment}', 'ArticleController@updateComment')->name('articles.comments.update')->middleware('auth');
Route::delete('/article/{id}/{slug}/comment/{comment}', 'ArticleController@destroyComment')->name('articles.comments.destroy')->middleware('auth');
Route::get('/games/{game:slug}', 'WebGameController@show')->name('games.show');
Route::post('/games/{game:slug}/claim-reward', 'WebGameController@claimReward')->name('games.claim-reward')->middleware('auth');
Route::post('/games/{game:slug}/quiz-complete', 'WebGameController@completeQuiz')->name('games.quiz-complete')->middleware('auth');
Route::post('/daily-missions/{mission}/claim', 'DailyMissionController@claim')->name('daily-missions.claim')->middleware('auth');
Route::post('/campaign/{campaign}/comment', 'AcademyController@submitCampaignComment')->name('campaign.comments.store')->middleware('auth');
Route::put('/campaign/{campaign}/comment/{comment}', 'AcademyController@updateCampaignComment')->name('campaign.comments.update')->middleware('auth');
Route::delete('/campaign/{campaign}/comment/{comment}', 'AcademyController@destroyCampaignComment')->name('campaign.comments.destroy')->middleware('auth');

Route::prefix('user')
    ->middleware('auth')
    ->group(function() {

    Route::get('/habbo-verification', 'User\HabboVerificationController@show')->name('users.habbo-verification.show');
    Route::post('/habbo-verification/check', 'User\HabboVerificationController@check')->name('users.habbo-verification.check');

    // User Update routes
    Route::get('/edit', 'UserController@edit')->name('users.edit');
    Route::put('/update', 'UserController@update')->name('users.update')->middleware('api');
    Route::put('/forumUpdate', 'UserController@forumUpdate')->name('users.forumUpdate')->middleware('api');

    // Forum routes
    Route::get('/topics/me', 'UserController@topics')->name('topics.me');
    Route::get('/topics/create', 'TopicController@create')->name('topics.create');
    Route::post('/topics', 'TopicController@store')->name('topics.store')->middleware('api');
    Route::post('/topic/{id}/{slug}/comment', 'Topic\TopicCommentController@store')->name('topics.comments.store');

    // User notification routes
    Route::get('/notifications', 'User\NotificationController@index')->name('users.notifications.index');
    Route::delete('/notifications/deleteAll', 'User\NotificationController@destroyAll')->name('users.notifications.deleteAll');
    Route::delete('/notifications/{id}/delete', 'User\NotificationController@destroy')->name('users.notifications.delete');
    
});
