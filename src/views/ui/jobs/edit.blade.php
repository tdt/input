@extends('layouts.admin')

@section('content')
    <div class='row header'>
        <div class="col-sm-10">
            <h3>
                <a href='{{ URL::to('api/admin/jobs') }}' class='back'>
                    <i class='fa fa-angle-left'></i>
                </a>
                {{ trans('input::admin.edit_job') }}
            </h3>
        </div>
        <div class='col-sm-2 text-right'>
            <button type='submit' class='btn btn-cta btn-edit-job margin-left'><i class='fa fa-save'></i> {{ trans('input::admin.edit_button') }}</button>
        </div>
    </div>

    <br/>

    <div class='row'>
        <div class="col-sm-12">
            <div class="alert alert-danger error hide">
                <i class='fa fa-2x fa-exclamation-circle'></i> <span class='text'></span>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <form class='form form-horizontal edit-job'>

        <div class="form-group">
            <label for="input_identifier" class="col-sm-2 control-label">
                {{ trans('input::admin.identifier') }}
            </label>
            <div class="col-sm-10">
                <div class="input-group">
                    <span class="input-group-addon">{{ URL::to('api/input') }}/</span>
                    <input type="text" class="form-control" id="input_identifier" name="collection" value="{{ $job->collection_uri . '/' . $job->name }}" disabled>
                </div>

                <div class='help-block'>
                </div>
            </div>
        </div>

        <hr/>
            <h4>{{ trans('input::admin.extract') }}</h4>
            <ul class="nav nav-tabs">
                <li class='active' id='extract'><a href="#{{ $job->extractor->type }}" data-toggle="tab">{{ strtoupper($job->extractor->type) }}</a></li>
            </ul>

            <div class='panel'>
                <div class='panel-body'>
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="extract" data-type='{{ $job->extractor->type }}'>
                            @foreach($extract_parameters as $param => $param_options)
                            <div class='row'>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">
                                        {{ $param_options->name }}
                                    </label>
                                    <div class="col-sm-10">
                                        @if($param_options->type == 'string')
                                        <input type="text" class="form-control" id="{{ $param }}" name="{{ $param }}" @if(isset($job->extractor->$param)) value='{{ $job->extractor->$param }}' @endif>
                                        @elseif($param_options->type == 'text')
                                        <textarea class="form-control" id="{{ $param }}" name="{{ $param }}"> @if (isset($job->extractor->$param)) {{ $job->extractor->$param }}@endif</textarea>
                                        @elseif($param_options->type == 'integer')
                                        <input type="number" class="form-control" id="{{ $param }}" name="{{ $param }}" @if(isset($job->extractor->$param)) value='{{ $job->extractor->$param }}' @endif>
                                        @elseif($param_options->type == 'boolean')
                                        <input type='checkbox' class="form-control" id="{{ $param }}" name="{{ $param }}" checked='checked'/>
                                        @elseif($param_options->type == 'list')
                                        <select id="{{ $param }}" name="{{ $param }}">

                                            @foreach ($param_options->list as $value)
                                            @if ($value == 'UTF-8')
                                            <option value="{{ $value }}" selected>{{ $value }}</option>
                                            @else
                                            <option value="{{ $value }}">{{ $value }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                        @endif
                                        <div class='help-block'>
                                            {{{ $param_options->description }}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <h4>{{ trans('input::admin.load') }}</h4>
            <ul class="nav nav-tabs">
                <li class='active'><a href="#{{ $job->loader->type }}" data-toggle="tab">{{ strtoupper($job->loader->type) }}</a></li>
            </ul>

            <div class='panel'>
                <div class='panel-body'>
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="load" data-type='{{ $job->loader->type }}'>
                            @foreach($load_parameters as $param => $param_options)
                            <div class='row'>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">
                                        {{ $param_options->name }}
                                    </label>
                                    <div class="col-sm-10">
                                        @if($param_options->type == 'string')
                                        <input type="text" class="form-control" id="{{ $param }}" name="{{ $param }}" @if(isset($job->loader->$param)) value='{{ $job->loader->$param }}' @endif>
                                        @elseif($param_options->type == 'text')
                                        <textarea class="form-control" id="{{ $param }}" name="{{ $param }}"> @if (isset($job->loader->$param)) {{ $job->loader->$param }}@endif</textarea>
                                        @elseif($param_options->type == 'integer')
                                        <input type="number" class="form-control" id="{{ $param }}" name="{{ $param }}" @if(isset($job->loader->$param)) value='{{ $job->loader->$param }}' @endif>
                                        @elseif($param_options->type == 'boolean')
                                        <input type='checkbox' class="form-control" id="{{ $param }}" name="{{ $param }}" checked='checked'/>
                                        @endif
                                        <div class='help-block'>
                                            {{{ $param_options->description }}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script type="text/javascript" src="{{ URL::to('packages/input/jobs.min.js') }}"></script>
@stop