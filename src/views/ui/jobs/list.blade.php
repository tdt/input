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

    <div class="col-sm-12">

        <br/>

        @foreach($jobs as $job)

            <div class="panel dataset dataset-link button-row panel-default">
                <div class="panel-body">
                    <div class='icon'>
                        <i class='fa fa-lg fa-file-code-o'></i>
                    </div>
                    <div>
                        <div class='row'>
                            <div class='col-sm-4'>
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
                                    @if(Tdt\Core\Auth\Auth::hasAccess('tdt.input.delete'))
                                        <a href='{{ URL::to('api/admin/jobs/delete/'. $job->id) }}' class='btn delete' title='Delete this dataset'>
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
                    This datatank has no configured jobs yet.
                @else
                    No job(s) found with the filter <strong>'<span class='dataset-filter'></span>'</strong>
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