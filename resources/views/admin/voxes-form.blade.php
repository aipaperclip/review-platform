@extends('admin')


@section('content')

<h1 class="page-header">
    {{ empty($item) ? trans('admin.page.'.$current_page.'.new.title') : trans('admin.page.'.$current_page.'.edit.title') }}
</h1>
<!-- end page-header -->

<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-12 ui-sortable">
        {{ Form::open(array('id' => 'page-add', 'class' => 'form-horizontal', 'method' => 'post')) }}


            <!-- begin panel -->
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.title') : trans('admin.page.'.$current_page.'.edit.title') }}</h4>
                </div>
                <div class="panel-body">
                    {!! csrf_field() !!}
                    @if(!empty($item))
                        <div class="form-group">
                            <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.reward_usd') }}</label>
                            <label class="col-md-9  control-label" style="text-align: left;">
                                {{ $item->questions->count() }} x {{ $item->getRewardPerQuestion()->dcn }} = {{ $item->getRewardTotal() }} DCN (${{ $item->getRewardTotal(true) }})
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.duration') }}</label>
                            <label class="col-md-9  control-label" style="text-align: left;">
                                {{ $item->questions->count() }} x 10sec = ~{{ ceil( $item->questions->count()/6 ) }} min
                            </label>
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.type') }}</label>
                        <div class="col-md-9">
                            {{ Form::select('type', $types, !empty($item) ? $item->type : null, array('class' => 'form-control')) }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ trans('admin.page.'.$current_page.'.categories') }}</label>
                        <div class="col-md-9">
                            @foreach($category_list as $cat)
                                <label class="col-md-3" for="cat-{{ $cat->id }}">

                                    <input type="checkbox" name="categories[]" value="{{ $cat->id }}" id="cat-{{ $cat->id }}" {!! !empty($item) && $item->categories->where('vox_category_id', $cat->id)->isNotEmpty() ? 'checked="checked"' : '' !!} >
                                    
                                    {{ $cat->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
            <!-- end panel -->

            <div class="panel panel-inverse panel-with-tabs" data-sortable-id="ui-unlimited-tabs-1">
                <div class="panel-heading p-0">
                    <div class="panel-heading-btn m-r-10 m-t-10">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-expand" data-original-title="" title=""><i class="fa fa-expand"></i></a>
                    </div>
                    <!-- begin nav-tabs -->
                    <div class="tab-overflow overflow-right">
                        <ul class="nav nav-tabs nav-tabs-inverse">
                            <li class="prev-button"><a href="javascript:;" data-click="prev-tab" class="text-success"><i class="fa fa-arrow-left"></i></a></li>
                            @foreach($langs as $code => $lang_info)
                                <li class="{{ $loop->first ? 'active' : '' }}"><a href="#nav-tab-{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a></li>
                            @endforeach

                            <li class="next-button"><a href="javascript:;" data-click="next-tab" class="text-success"><i class="fa fa-arrow-right"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    @foreach($langs as $code => $lang_info)
                        <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }}" id="nav-tab-{{ $code }}">
                            <div class="form-group">
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.lang-slug') }}</label>
                                <div class="col-md-10">
                                    {{ Form::text('slug-'.$code, !empty($item) ? $item->translateOrNew($code)->slug : null, array('maxlength' => 256, 'class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.lang-title') }}</label>
                                <div class="col-md-4">
                                    {{ Form::text('title-'.$code, !empty($item) ? $item->{'title:'.$code} : null, array('maxlength' => 256, 'class' => 'form-control input-title')) }}
                                </div>
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.lang-seo-title') }}</label>
                                <div class="col-md-4">
                                    {{ Form::text('seo_title-'.$code, !empty($item) ? $item->translateOrNew($code)->seo_title : null, array('maxlength' => 256, 'class' => 'form-control input-title')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.lang-description') }}</label>
                                <div class="col-md-4">
                                    {{ Form::textarea('description-'.$code, !empty($item) ? $item->{'description:'.$code} : null, array('maxlength' => 2048, 'class' => 'form-control input-description')) }}
                                </div>
                                <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.lang-seo-description') }}</label>
                                <div class="col-md-4">
                                    {{ Form::textarea('seo_description-'.$code, !empty($item) ? $item->translateOrNew($code)->seo_description : null, array('maxlength' => 2048, 'class' => 'form-control input-description')) }}
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-10 control-label"></label>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-success btn-block">{{ empty($item) ? trans('admin.page.'.$current_page.'.new.submit') : trans('admin.page.'.$current_page.'.edit.submit') }}</button>
                </div>
            </div>


        {{ Form::close() }}


        @if(!empty($item))

            <h3>Add question</h3>
            
            @include('admin.parts.vox-question', [
                'question' => null,
                'next' => $item->questions->count()+1
            ])

            <h3>Import / Export</h3>
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    Import / Export Options
                </div>
                <div class="tab-content">
                    <div class="row">
                        <div class="col-md-4">
                            <h4>{{ trans('admin.page.'.$current_page.'.questions-export') }}</h4>
                            <a class="btn btn-primary btn-block" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/export') }}" target="_blank">
                                {{ trans('admin.page.'.$current_page.'.questions-export') }}
                            </a>
                        </div>
                        <div class="col-md-4">
                            <h4>{{ trans('admin.page.'.$current_page.'.questions-import') }}</h4>
                            <form class="form-horizontal" id="translations-import" method="post" action="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/import') }}" enctype="multipart/form-data">
                                {!! csrf_field() !!}
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="file" class="btn-block form-control" name="table" accept=".xls, .xlsx" />
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-success btn-block">
                                            {{ trans('admin.page.'.$current_page.'.questions-import') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <br/>
                            <i>* Export a translation file and fill the texts in it</i>
                        </div>
                        <div class="col-md-4">
                            <h4>Quick import</h4>
                            <form class="form-horizontal" id="translations-import-quick" method="post" action="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/import-quick') }}" enctype="multipart/form-data">
                                {!! csrf_field() !!}
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="file" class="btn-block form-control" name="table" accept=".xls, .xlsx" />
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-success btn-block">
                                            Quick Import
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <br/>
                            <a href="{{ url('test-excel.xlsx') }}">Download sample</a>
                        </div>
                    </div>
                </div>
            </div>



            @if($item->questions->isNotEmpty())
                <h3>Questions</h3>
                <div class="panel panel-inverse">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.questions') }}</h4>
                    </div>
                    <div class="tab-content">

                        <table class="table table-striped table-question-list">
                            <thead>
                                <tr>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-num') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-title') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-control') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-type') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-trigger') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-edit') }}</th>
                                    <th>{{ trans('admin.page.'.$current_page.'.question-delete') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->questions as $question)
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control question-number" style="width: 60px;" data-qid="{{ $question->id }}" value="{{ $question->order }}" />
                                        </td>
                                        <td>
                                            <textarea style="min-width: 360px;" class="form-control question-question" data-qid="{{ $question->id }}">{{ $question->question }}</textarea>
                                        </td>
                                        <td>
                                            {{ trans( 'admin.common.'.( $question->is_control ? 'yes' : 'no' ) ) }}
                                            @if($question->go_back)
                                                , go back to <br/> &raquo;
                                                {{ App\Models\VoxQuestion::find($question->go_back)->question }}
                                            @endif
                                        </td>
                                        <td>{{ trans('admin.enums.question-type.'.$question->type) }}</td>
                                        <td>{!! $triggers[$question->id] !!}</td>
                                        <td>
                                            <a class="btn btn-sm btn-success" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/question/'.$question->id) }}">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-success" onclick="return confirm('{{ trans('admin.common.sure') }}')" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/question-del/'.$question->id) }}">
                                                <i class="fa fa-remove"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif


    </div>
</div>

@endsection