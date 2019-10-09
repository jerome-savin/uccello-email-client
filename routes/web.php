<?php

Route::middleware('web', 'auth')
->namespace('JeromeSavin\UccelloEmailClient\Http\Controllers')
->name('uccello.mail.')
->group(function () {
    // Adapt params if we use or not multi domains
    if (!uccello()->useMultiDomains()) {
        $domainParam = '';
    } else {
        $domainParam = '{domain}';
    }

    Route::get($domainParam.'/o365/login', 'MailClientController@signin')
        ->defaults('module', 'mail-client')    
        ->name('signin');

    Route::get($domainParam.'/o365/account/remove', 'MailClientController@remove')
        ->defaults('module', 'mail-client')
        ->name('remove');

    Route::get($domainParam.'/o365/redirect', 'MailClientController@gettoken')
        ->defaults('module', 'mail-client')
        ->name('redirect');

    Route::get($domainParam.'/o365/mails', 'MailClientController@mails')
        ->defaults('module', 'mail-client')
        ->name('list');

    Route::get($domainParam.'/o365/manage', 'MailClientController@manage')
        ->defaults('module', 'mail-client')
        ->name('manage');

    Route::post($domainParam.'/o365/mails/{account}/{folder}', 'MailClientController@folderMails')
        ->defaults('module', 'mail-client')
        ->name('folder.mails');
});