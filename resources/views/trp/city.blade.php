@extends('trp')

@section('content')

	<div class="page-dentists page-c">
		<div class="black-overflow" style="display: none;">
		</div>
		<div class="home-search-form">
			<div class="tac" style="display: none;">
		    	<h1>
		    		{!! nl2br(trans('trp.page.search.title')) !!}
		    		
		    	</h1>
		    	<h2>
		    		{!! nl2br(trans('trp.page.search.subtitle')) !!}
		    		
		    	</h2>
		    </div>
		    @include('trp.parts.search-form')
			
		</div>

		<div class="main-top">
	    </div>

	    <div class="sort-wrapper">
	    	<h1 class="white-title">{!! nl2br(trans('trp.page.search.city-title')) !!}</h1>
	    </div>

	    <div class="countries-wrapper container">
		    <div class="countries">
		    	<div class="flex">
		    		<div class="col">
				    	@foreach($cities_name as $key => $city)
				    		@if(mb_strlen($city) == 1)
				    			<span class="letter">{{ $city }}</span>
				    		@else
				    			<a href="{{ getLangUrl(strtolower($city).'-'.$country->slug) }}">{{ $city }}</a>
				    		@endif
				    		@if( $total_rows > 8 && in_array($key, $breakpoints) && !$loop->last)
				    			</div>
				    			<div class="col">
				    		@endif

				    	@endforeach
				    </div>
			    </div>
		    </div>
		</div>
	</div>

@endsection