@extends('layouts.admin')

@section('content')
    <div class='row header'>
        <div class="col-sm-10">
            <h3>
                <a href='{{ URL::to('api/admin/jobs') }}' class='back'>
                    <i class='fa fa-angle-left'></i>
                </a>
                Add a job
            </h3>
        </div>
        <div class='col-sm-2 text-right'>
            <button type='submit' class='btn btn-cta btn-add-job margin-left'><i class='fa fa-plus'></i> Add</button>
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
        <form class='form form-horizontal add-job'>

        <div class="form-group">
            <label for="input_identifier" class="col-sm-2 control-label">
                Identifier
            </label>
            <div class="col-sm-10">

                <div class="input-group">
                    <span class="input-group-addon">{{ URL::to('api/input') }}/</span>
                    <input type="text" class="form-control" id="input_identifier" name="collection" placeholder="">
                </div>

                <div class='help-block'>
                </div>
            </div>
        </div>

        <hr/>

        @foreach($configuration as $part => $part_options)

            <h4>{{ ucfirst($part) }}</h4>
            <ul class="nav nav-tabs">
                <?php $i = 0 ?>
                @foreach($part_options as $type => $type_options)
                    <li @if($i == 0) class='active' @endif><a href="#{{ $part . '-' . $type }}" data-toggle="tab">{{ strtoupper($type) }}</a></li>
                    <?php $i++ ?>
                @endforeach
            </ul>

            <div class='panel'>
                <div class='panel-body'>
                    <div class="tab-content">
                        <?php $i = 0 ?>
                        @foreach($part_options as $type => $type_options)
                            <div class="tab-pane fade in @if($i == 0){{ 'active' }}@endif" id="{{ $part . '-' . $type }}" data-type='{{ $type }}' data-part='{{ $part }}'>
                                @foreach($type_options as $param => $param_options)
                                    <div class='row'>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">

                                                {{ $param_options->name }}
                                            </label>
                                            <div class="col-sm-10">
                                                @if($param_options->type == 'string')
                                                    <input type="text" class="form-control" id="{{ $param }}" name="{{ $param }}" placeholder="" @if(isset($param_options->default_value)) value='{{ $param_options->default_value }}' @endif>
                                                @elseif($param_options->type == 'text')
                                                    <textarea class="form-control" id="{{ $param }}" name="{{ $param }}"> @if (isset($param_options->default_value)) {{ $param_options->default_value }}@endif</textarea>
                                                @elseif($param_options->type == 'integer')
                                                    <input type="number" class="form-control" id="{{ $param }}" name="{{ $param }}" placeholder="" @if(isset($param_options->default_value)) value='{{ $param_options->default_value }}' @endif>
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
                            <?php $i++ ?>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
        </form>
    </div>
    <script type="text/javascript" src="{{ URL::to('packages/input/jobs.min.js') }}"></script>
@stop