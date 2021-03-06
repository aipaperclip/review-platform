@extends('admin')

@section('content')

    <h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>
    <!-- end page-header -->


    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-filter') }} </h4>
                </div>
                <div class="panel-body">
                    <form method="get" action="{{ url('cms/'.$current_page) }}" >
                        <div class="row custom-row" style="margin-bottom: 10px;">
                            <div class="col-md-1">
                                <input type="text" class="form-control" name="search-user-id" value="{{ $search_user_id }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-user-id') }}">
                            </div>  
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="User email" autocomplete="off">
                            </div>
                            <div class="col-md-1">
                                <select class="form-control" name="search-status">
                                    <option value="">Status</option>
                                    <option value="new" {!! 'new'==$search_status ? 'selected="selected"' : '' !!}>New</option>
                                    <option value="unconfirmed" {!! 'unconfirmed'==$search_status ? 'selected="selected"' : '' !!}>Unconfirmed</option>
                                    <option value="failed" {!! 'failed'==$search_status ? 'selected="selected"' : '' !!}>Failed</option>
                                    <option value="completed" {!! 'completed'==$search_status ? 'selected="selected"' : '' !!}>Completed</option>
                                    <option value="stopped" {!! 'stopped'==$search_status ? 'selected="selected"' : '' !!}>Stopped</option>
                                    <option value="first" {!! 'first'==$search_status ? 'selected="selected"' : '' !!}>First</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search-address" value="{{ $search_address }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-address') }}">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search-tx" value="{{ $search_tx }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-tx') }}">
                            </div>                                                                          
                            <div class="col-md-1">
                                <input type="text" class="form-control datepicker" name="search-from" value="{{ $search_from }}" placeholder="Search from" autocomplete="off">
                            </div>
                            <div class="col-md-1">
                                <input type="text" class="form-control datepicker" name="search-to" value="{{ $search_to }}" placeholder="Search to" autocomplete="off">
                            </div>
                        </div>
                        <div class="row custom-row" style="margin-bottom: 10px;">
                            <input type="submit" class="btn btn-block btn-primary btn-block" name="search" value="{{ trans('admin.page.'.$current_page.'.title-filter-submit') }}">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>




    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
                </div>
                <div class="panel-body">
                    <div>
                        Transactions count: {{ $total_count }}
                    </div>
            		<div class="panel-body">
                        <form method="post" action="{{ url('cms/transactions') }}" original-action="{{ url('cms/transactions') }}">
                            {!! csrf_field() !!}
        					@include('admin.parts.table', [
        						'table_id' => 'transactions',
        						'table_fields' => [
                                    'checkboxes' => array('format' => 'checkboxes'),
                                    'created_at'        => array('format' => 'datetime','order' => true, 'orderKey' => 'created','label' => 'Date'),
                                    'user'              => array('template' => 'admin.parts.table-transactions-user'),
                                    'email'              => array('template' => 'admin.parts.table-transactions-email'),
                                    'amount'              => array(),
                                    'address'              => array(),
                                    'tx_hash'              => array('template' => 'admin.parts.table-transactions-hash'),
                                    'status'              => array(),
                                    'type'              => array(),
                                    'message'              => array(),
                                    'retries'              => array(),
                                    'updated_at'        => array('format' => 'datetime', 'order' => true, 'orderKey' => 'attempt','label' => 'Last attempt'),
                                    'bump'                  =>array('template' => 'admin.parts.table-transactions-bump'),
        						],
                                'table_data' => $transactions,
        						'table_pagination' => false,
                                'pagination_link' => array()
        					])
                            <div style="display: flex">
                                <button type="submit" name="mass-bump" id="mass-bump" class="btn btn-primary" style="flex: 1">Bump transactions</button>
                                <button type="submit" name="mass-stop" id="mass-stop" class="btn btn-danger" style="flex: 1">Stop transactions</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($total_pages > 1)
        <nav aria-label="Page navigation" style="text-align: center;">
            <ul class="pagination">
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link" href="{{ url('cms/transactions/?page=1'.$pagination_link) }}" aria-label="Previous">
                        <span aria-hidden="true"> << </span>
                    </a>
                </li>
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link prev" href="{{ url('cms/transactions/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                        <span aria-hidden="true"> < </span>
                    </a>
                </li>
                @for($i=$start; $i<=$end; $i++)
                    <li class="{{ ($i == $page ?  'active' : '') }}">
                        <a class="page-link" href="{{ url('cms/transactions/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link next" href="{{ url('cms/transactions/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                </li>
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link" href="{{ url('cms/transactions/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                </li>
            </ul>
        </nav>
    @endif

@endsection