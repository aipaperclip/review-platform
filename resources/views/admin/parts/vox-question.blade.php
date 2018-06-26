<div class="form-group clearfix">
    <div class="col-md-12">
        <a href="{{ url('cms/vox/') }}">Surveys</a> &raquo;
        <a href="{{ url('cms/vox/edit/'.$item->id) }}"> {{ $item->title }}</a>  
        @if(!empty($question))
        &raquo;
        {{ $question->question }}
        @endif
    </div>
</div>

{{ Form::open(array('id' => 'question-'.( !empty($question) ? 'edit' : 'add') , 'url' => url('cms/'.$current_page.'/edit/'.$item->id.'/question/'.( !empty($question) ? $question->id : 'add') ), 'class' => 'form-horizontal questions-form', 'method' => 'post')) }}

    <div class="form-group clearfix">
        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.question-type') }}</label>
        <div class="col-md-9">
            {{ Form::select('type', $question_types, !empty($question) ? $question->type : null, array('class' => 'form-control question-type-input')) }}
        </div>
    </div>
    <div class="form-group clearfix">
        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.question-trigger') }}</label>
        <div class="col-md-9">
            {{ Form::text('question_trigger', !empty($question) ? $question->question_trigger : '', array('class' => 'form-control', 'placeholder' => trans('admin.page.'.$current_page.'.question-trigger-placeholder')  )) }}
        </div>
        <label class="col-md-3">
        </label>
        <div class="col-md-9">
            {!! nl2br(trans('admin.page.'.$current_page.'.question-trigger-hint')) !!}
        </div>
    </div>
    <div class="form-group clearfix">
        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.question-scale') }}</label>
        <div class="col-md-9">
            {{ Form::select('question_scale', ['' => '-'] + $scales, !empty($question) ? $question->vox_scale_id : '', array('class' => 'form-control question-scale-input')) }}
        </div>
        <label class="col-md-3">
        </label>
        <div class="col-md-9">
            {!! nl2br(trans('admin.page.'.$current_page.'.question-scale-hint')) !!}
        </div>
    </div>
    <div class="panel panel-inverse panel-with-tabs" data-sortable-id="add-question">
        <div class="panel-heading p-0">
            <!-- begin nav-tabs -->
            <div class="tab-overflow overflow-right">
                <ul class="nav nav-tabs nav-tabs-inverse">
                    <li class="prev-button"><a href="javascript:;" data-click="prev-tab" class="text-success"><i class="fa fa-arrow-left"></i></a></li>
                    @foreach($langs as $code => $lang_info)
                        <li class="{{ $loop->first ? 'active' : '' }}"><a href="#nav-tab-add-{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a></li>
                    @endforeach

                    <li class="next-button"><a href="javascript:;" data-click="next-tab" class="text-success"><i class="fa fa-arrow-right"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            @foreach($langs as $code => $lang_info)
                <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }}" id="nav-tab-add-{{ $code }}" data-code="{{ $code }}">
                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.question-question') }}</label>
                        <div class="col-md-9">
                            {{ Form::text('question-'.$code, !empty($question) ? $question->{'question:'.$code} : '', array('maxlength' => 256, 'class' => 'form-control input-title')) }}
                        </div>
                    </div>
                    <div class="form-group answers-group">
                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.question-answers') }}</label>
                        <div class="col-md-9 answers-list">
                            @if(!empty($question) && !empty($question->{'answers:'.$code}) )
                                @foreach(json_decode($question->{'answers:'.$code}, true) as $ans)
                                    <div class="input-group">
                                        {{ Form::text('answers-'.$code.'[]', $ans, array('maxlength' => 256, 'class' => 'form-control', 'placeholder' => 'Answer or name of the scale:weak,medium,strong')) }}
                                        <div class="input-group-btn">
                                            <button class="btn btn-default btn-remove-answer" type="button">
                                                <i class="glyphicon glyphicon-remove"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="input-group">
                                    {{ Form::text('answers-'.$code.'[]', '', array('maxlength' => 256, 'class' => 'form-control', 'placeholder' => 'Answer or name of the scale:weak,medium,strong')) }}
                                    <div class="input-group-btn">
                                        <button class="btn btn-default btn-remove-answer" type="button">
                                            <i class="glyphicon glyphicon-remove"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-group answers-group-add">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            {{ trans('admin.page.'.$current_page.'.answers-add-hint') }}<br/>
                            <br/>
                            <a href="javascript:;" class="btn btn-success btn-block btn-add-answer">{{ trans('admin.page.'.$current_page.'.answers-add') }}</a>
                        </div>
                    </div>

                </div>
            @endforeach
            <div class="form-group">
                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.question-order') }}</label>
                <div class="col-md-9">
                    {{ Form::text('order', !empty($question) ? $question->order : (!empty($next) ? $next : ''), array('maxlength' => 256, 'class' => 'form-control input-title')) }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.question-control') }}</label>
                <div class="col-md-9">
                    {{ Form::text('is_control', !empty($question) ? $question->is_control : '', array('maxlength' => 256, 'class' => 'form-control input-title')) }}
                    {{ trans('admin.page.'.$current_page.'.question-control-hint') }}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.question-go-back') }}</label>
                <div class="col-md-9">
                    {{ trans('admin.page.'.$current_page.'.question-go-back-hint') }}<br/>
                    {{ Form::text('go_back', !empty($question) ? $question->go_back : '', array('maxlength' => 256, 'class' => 'form-control input-title')) }}
                </div>
            </div>


            <div class="form-group">
                <label class="col-md-10 control-label"></label>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-block btn-sm btn-success">{{ trans('admin.page.'.$current_page.'.question-add') }}</button>
                </div>
            </div>
        </div>
    </div>

{{ Form::close() }}

<div style="display: none;">
    <div class="input-group" id="input-group-template" >
        {{ Form::text('something', '', array('maxlength' => 256, 'class' => 'form-control', 'placeholder' => 'Answer or name of the scale:weak,medium,strong')) }}
        <div class="input-group-btn">
            <button class="btn btn-default btn-remove-answer" type="button">
                <i class="glyphicon glyphicon-remove"></i>
            </button>
        </div>
    </div>
</div>
