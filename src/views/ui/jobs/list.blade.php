@extends('layouts.admin')

@section('content')

    <div class='row header'>
        <div class="col-sm-7">
            <h3>Manage your jobs</h3>
        </div>
        <div class="col-sm-5 text-right">
            <a href='{{ URL::to('api/admin/jobs/add') }}' class='btn btn-primary margin-left'
                data-step='1'
                data-intro='Add a new job to the system.'
                data-position="left">
                <i class='fa fa-plus'></i> Add
            </a>
        </div>
    </div>
@stop

@section('navigation')

    <div class="search pull-right hidden-xs">
        <input id='dataset-filter' type="text" placeholder='Search for datasets' spellcheck='false'>
        <i class='fa fa-search'></i>
    </div>

@stop