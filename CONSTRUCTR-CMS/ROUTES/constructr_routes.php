<?php

	$APP->route('GET  /constructr/admin [sync]', 'ConstructrCMS->admin_init');

	$APP->route('GET  /constructr/no-rights [sync]', 'ConstructrBase->no_rights');
    $APP->route('GET  /constructr/404 [sync]', 'ConstructrCMS->admin_404');
    $APP->route('GET  /constructr/error [sync]', 'ConstructrCMS->admin_error');
	$APP->route('GET  /constructr [sync]', 'ConstructrBase->init');
    $APP->route('POST /constructr/login [sync]', 'ConstructrBase->login_step_1');

	$APP->route('GET /constructr/login-step-2 [sync]', 'ConstructrBase->login_step_2');
	$APP->route('POST /constructr/login-step-2 [sync]', 'ConstructrBase->login_step_2_verify');

    $APP->route('GET  /constructr/login-error [sync]', 'ConstructrBase->login_error');
    $APP->route('GET  /constructr/logout [sync]', 'ConstructrBase->logout');
    $APP->route('GET  /constructr/retrieve-password/@username [sync]', 'ConstructrBase->retrieve_password');
    $APP->route('GET  /constructr/updated-user-credentials [sync]', 'ConstructrBase->updated_user_credentials');

    $APP->route('GET  /constructr/uploads [sync]', 'ConstructrUploads->uploads_init');
	$APP->route('GET  /constructr/uploads/@offset [sync]', 'ConstructrUploads->uploads_init');
    $APP->route('GET  /constructr/uploads/new [sync]', 'ConstructrUploads->uploads_new');
    $APP->route('POST /constructr/uploads/new [sync]', 'ConstructrUploads->uploads_new_verify');
    $APP->route('GET  /constructr/uploads/delete/@file [sync]', 'ConstructrUploads->uploads_delete_file');

    $APP->route('GET  /constructr/pagemanagement [sync]', 'ConstructrCMS->page_management');
    $APP->route('GET  /constructr/pagemanagement/new [sync]', 'ConstructrCMS->page_management_new');
    $APP->route('POST /constructr/pagemanagement/new [sync]', 'ConstructrCMS->page_management_new_verify');
    $APP->route('GET  /constructr/pagemanagement/edit/@page_id [sync]', 'ConstructrCMS->page_management_edit');
    $APP->route('POST /constructr/pagemanagement/edit [sync]', 'ConstructrCMS->page_management_edit_verify');
    $APP->route('GET  /constructr/pagemanagement/delete/@page_id [sync]', 'ConstructrCMS->page_management_delete');
    $APP->route('GET  /constructr/pagemanagement/move-up/@page_id [sync]', 'ConstructrCMS->page_management_move_up');
    $APP->route('GET  /constructr/pagemanagement/move-down/@page_id [sync]', 'ConstructrCMS->page_management_move_down');
    $APP->route('GET  /constructr/pagemanagement/content/@page_id [sync]', 'ConstructrCMS->page_management_move_down');
	$APP->route('GET  /constructr/pagemanagement/visibility/@what/@page_id [sync]', 'ConstructrCMS->page_management_change_visibility');
	$APP->route('POST /constructr/pagemanagement/slug [ajax]', 'ConstructrCMS->page_management_make_slug');

    $APP->route('GET  /constructr/content/@page_id [sync]', 'ConstructrContent->content_init');
    $APP->route('GET  /constructr/content/@page_id/new [sync]', 'ConstructrContent->content_new');
    $APP->route('POST /constructr/content/@page_id/new [sync]', 'ConstructrContent->content_new_verify');
    $APP->route('GET  /constructr/content/@page_id/edit/@content_id [sync]', 'ConstructrContent->content_edit');
    $APP->route('POST /constructr/content/@page_id/edit/@content_id [sync]', 'ConstructrContent->content_edit_verify');
    $APP->route('GET  /constructr/content/@page_id/delete/@content_id [sync]', 'ConstructrContent->content_delete');
    $APP->route('GET  /constructr/content/@page_id/reorder/@method/@content_id [sync]', 'ConstructrContent->content_reorder');
	$APP->route('POST /constructr/content/live-preview/ [ajax]', 'ConstructrContent->preparse_content_live_preview');
	$APP->route('GET  /constructr/content/@page_id/visibility/@what/@content_id [sync]', 'ConstructrContent->content_change_visibility');

    $APP->route('GET  /constructr/usermanagement [sync]', 'ConstructrUser->user_management');
    $APP->route('GET  /constructr/usermanagement/new [sync]', 'ConstructrUser->user_management_new');
    $APP->route('POST /constructr/usermanagement/new [sync]', 'ConstructrUser->user_management_new_verify');
    $APP->route('GET  /constructr/usermanagement/delete/@user_id [sync]', 'ConstructrUser->user_management_delete');
    $APP->route('GET  /constructr/usermanagement/edit/@user_id [sync]', 'ConstructrUser->user_management_edit');
    $APP->route('POST /constructr/usermanagement/edit [sync]', 'ConstructrUser->user_management_edit_verify');
	$APP->route('GET  /constructr/usermanagement/edit-rights/@user_id [sync]', 'ConstructrUser->user_management_edit_rights');
	$APP->route('POST /constructr/usermanagement/update-rights [ajax]', 'ConstructrUser->user_management_update_rights');