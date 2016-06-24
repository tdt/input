@extends('layouts.admin')

@section('content')

    <div class='row header'>
        <div class="col-sm-7">
            <h3>{{ trans('input::admin.manage_jobs') }}</h3>
        </div>
        <div class="col-sm-5 text-right">
            <a href="{{ URL::to('api/admin/jobs/add', [], Config::get('app.ssl_enabled')) }}" class='btn btn-primary margin-left'
                data-step='1'
                data-intro='Add a new job to the system.'
                data-position="left">
                <i class='fa fa-plus'></i> {{ trans('input::admin.add_button') }}
            </a>
        </div>
    </div>

    <div class="col-sm-12">

        <br/>

        @foreach($jobs as $job)

            <div class="panel dataset dataset-link button-row panel-default clickable-row" data-href="{{ URL::to('api/admin/jobs/edit/' . $job->id, [], Config::get('app.ssl_enabled')) }}">
                <div class="panel-body">
                    <div class='icon'>
                        <i class='fa fa-lg fa-file-code-o'></i>
                    </div>
                    <div>
                        <div class='row'>
                            <div class='col-sm-3'>
                                <h4 class='dataset-title'>
                                    {{ $job->collection_uri . '/' . $job->name  }}
                                </h4>
                            </div>
                            <div class='col-sm-2'>
                                {{ $job->extractor_type }}
                            </div>
                            <div class='col-sm-2'>
                                {{ $job->mapper_type }}
                            </div>
                            <div class='col-sm-2'>
                                {{ $job->loader_type }}
                            </div>
                            <div class='col-sm-2 text-right'>
                                <div class='btn-group'>
                                @if(Tdt\Core\Auth\Auth::hasAccess('tdt.input.edit'))
                                    <a href="{{ URL::to('api/input/' . $job->collection_uri . '/' . $job->name, [], Config::get('app.ssl_enabled')) }}" class='btn' title="{{ trans('input::admin.view_json_def') }}"

                                        <i class='fa fa-external-link'></i> {{ trans('input::admin.view_json_def') }}
                                    </a>
                                @endif
                                @if(Tdt\Core\Auth\Auth::hasAccess('tdt.input.delete'))
                                <a href="{{ URL::to('api/admin/jobs/delete/'. $job->id, [], Config::get('app.ssl_enabled')) }}" class='btn delete' title='Delete this dataset'>
                                    <i class='fa fa-times icon-only'></i>
                                </a>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

     <div class='col-sm-12 empty'>
        <div class='panel panel-default @if(count($jobs) > 0) hide @endif'>
            <div class="panel-body note">
                <i class='fa fa-lg fa-warning'></i>&nbsp;&nbsp;
                @if(count($jobs) === 0)
                    {{ trans('input::admin.no_jobs_message') }}
                @else
                    {{ trans('input::admin.no_jobs_filter') }} <strong>'<span class='dataset-filter'></span>'</strong>
                @endif
            </div>
        </div>
    </div>

@stop

@section('navigation')
     @if(count($jobs) > 0)
        <div class="search pull-right hidden-xs">
            <input id='dataset-filter' type="text" placeholder='Search for jobs' spellcheck='false'>
            <i class='fa fa-search'></i>
        </div>
    @endif
@stop
