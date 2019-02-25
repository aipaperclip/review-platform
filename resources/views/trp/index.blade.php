@extends('trp')

@section('content')

	<div class="black-overflow" style="display: none;">
	</div>
	<div class="home-search-form">
		<div class="tac">
	    	<h1>
	    		{!! nl2br(trans('trp.page.index.title')) !!}
	    		
	    	</h1>
	    	<h2>
	    		{!! nl2br(trans('trp.page.index.subtitle')) !!}
	    		
	    	</h2>
	    </div>
	    @include('trp.parts.search-form')
		
	</div>

	<div class="main-top">
    </div>

    <div class="flickity-oval">
	    <div class="container">
		    <div class="flickity">
		    	@foreach( $featured as $dentist )
					<a class="slider-wrapper" href="{{ $dentist->getLink() }}">
						<div class="slider-image-wrapper"> 
							<div class="slider-image" style="background-image: url('{{ $dentist->getImageUrl(true) }}')">
								@if($dentist->is_partner)
									<img class="tooltip-text" src="{{ url('img-trp/mini-logo.png') }}" text="{!! nl2br(trans('trp.common.partner')) !!} {{ $dentist->is_clinic ? 'Clinic' : 'Dentist' }}" />
								@endif
							</div>
						</div>
					    <div class="slider-container">
					    	<h4>{{ $dentist->getName() }}</h4>
					    	<div class="p">
					    		<div class="img">
					    			<img src="img-trp/map-pin.png">
					    		</div>
								{{ $dentist->city_name ? $dentist->city_name.', ' : '' }}
								{{ $dentist->state_name ? $dentist->state_name.', ' : '' }} 
								{{ $dentist->country->name }} 
					    		<!-- <span>(2 km away)</span> -->
					    	</div>
					    	@if( $time = $dentist->getWorkHoursText() )
					    		<div class="p">
					    			<div class="img">
						    			<img src="{{ url('img-trp/open.png') }}">
						    		</div>
					    			{!! $time !!}
					    		</div>
					    	@endif
						    <div class="ratings">
								<div class="stars">
									<div class="bar" style="width: {{ $dentist->avg_rating/5*100 }}%;">
									</div>
								</div>
								<span class="rating">
									({{ intval($dentist->ratings) }} reviews)
								</span>
							</div>
					    </div>
				    	<div class="flickity-buttons clearfix">
				    		<div>
				    			{!! nl2br(trans('trp.common.see-profile')) !!}
				    			
				    		</div>
				    		<div href="{{ $dentist->getLink() }}?popup-loged=submit-review-popup">
				    			{!! nl2br(trans('trp.common.submit-review')) !!}
				    			
				    		</div>
				    	</div>
					</a>
		    	@endforeach
			</div>
		</div>
	</div>

	@if(empty($user))

		<div class="gray-background">

			<div class="container">
				<div class="front-info">
					<div class="container-middle">
						<h2 class="tac">
							{!! nl2br(trans('trp.page.index.hint')) !!}
							
						</h2>
					</div>
					<div class="flex first">
						<div class="col">
							<img src="img-trp/front-first.png">
						</div>
						<div class="col fixed-width">
							<h3>
								{!! nl2br(trans('trp.page.index.usp-1-title')) !!}
								
							</h3>
							<p>
								{!! nl2br(trans('trp.page.index.usp-1-content')) !!}
								
							</p>
							<a href="javascript:;" class="button button-sign-up-patient" data-popup="popup-register">
								{!! nl2br(trans('trp.page.index.join-now')) !!}
								
							</a>
						</div>
					</div>
					<div class="flex second">
						<div class="col fixed-width">
							<h3>
								{!! nl2br(trans('trp.page.index.usp-2-title')) !!}
								
							</h3>
							<p>
								{!! nl2br(trans('trp.page.index.usp-2-content')) !!}
								
							</p>
							<a href="{{ getLangUrl('welcome-dentist') }}" class="button">
								{!! nl2br(trans('trp.page.index.join-dentist')) !!}
								
							</a>
						</div>
						<div class="col">
							<img src="img-trp/front-second.png">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="front-info">
			<div class="third">
				<div class="container">
					<div class="fixed-width">
						<h3>
							{!! nl2br(trans('trp.page.index.usp-3-title')) !!}
							
						</h3>
						<p>
							{!! nl2br(trans('trp.page.index.usp-3-content')) !!}
							
						</p>
						<div class="tac">
							<a href="javascript:;" class="button button-sign-up-patient" data-popup="popup-register">
								{!! nl2br(trans('trp.page.index.join-now')) !!}
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	@endif

@endsection