<?php

use Illuminate\Http\Request;

Route::get('/', function () {
	return view ('Admin::CRM.Atendimento.index');
});