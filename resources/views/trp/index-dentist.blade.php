@extends('trp')

@section('content')

	<div class="welcome-dentist-section">
		<div class="signin-top">
	    	<h1>
	    		{{ trans('trp.page.index-dentist.title') }}
	    	</h1>

	    	<p>
	    		{!! nl2br(trans('trp.page.index-dentist.subtitle')) !!}
	    	</p>

			<div class="ratings biggest">
				<div class="stars">
					<div class="bar" style="width: 100%;">
					</div>
				</div>
			</div>

			<div class="tac button-wrap">
				<a href="javascript:;" class="button button-sign-up-dentist" data-popup="popup-register">
	    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
	    		</a>
	    	</div>

			@if($unsubscribed)
				<div class="alert alert-info">
					{{ trans('trp.page.index-dentist.unsubscribed') }}
				</div>
			@endif

	    </div>

	    <div class="signin-form-wrapper">
	    	<img src="{{ url('img-trp/dentacoin-trusted-reviews-dentist-front-page.png') }}" alt="Dentacoin trusted reviews dentist front page">
	    	<div class="container clearfix">
	    		<form class="signin-form tablet-fixes">

					<div class="form-inner">
						<div class="modern-field">
							<input type="email" name="email" id="dentist-mail" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
							<label for="dentist-mail">
								<span>{{ trans('trp.page.index-dentist.email') }}</span>
							</label>
						</div>
						
						<div class="modern-field">
							<input type="password" name="password" id="dentist-pass" class="modern-input" autocomplete="off">
							<label for="dentist-pass">
								<span>{{ trans('trp.page.index-dentist.password') }}</span>
							</label>
						</div>
						
						<div class="modern-field">
							<input type="password" name="password-repeat" id="dentist-pass-repeat" class="modern-input" autocomplete="off">
							<label for="dentist-pass-repeat">
								<span>{{ trans('trp.page.index-dentist.repeat-password') }}</span>
							</label>
						</div>

						<div class="tac">
							<input type="submit" value="{{ trans('trp.page.index-dentist.signup') }}" class="button button-sign-up-dentist">
						</div>
					</div>

					<p class="have-account">
						{!! nl2br(trans('trp.page.index-dentist.have-account', [
							'link' => '<a href="javascript:;" data-popup="popup-login">',
							'endlink' => '</a>',
						])) !!}					
					</p>

	    		</form>
	    	</div>
	    </div>
	</div>


    <div class="container section-dentist-info">
    	<h2 class="tac">
    		{!! nl2br(trans('trp.page.index-dentist.usp-title')) !!}
    	</h2>

    	<div class="flex">
    		<div class="col tac">
    			<img src="{{ url('img-trp/dentacoin-attract-new-patients-icon.png') }}" alt="Dentacoin attract new patients icon">
    			<div class="info-padding">
	    			<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-1-title')) !!}</h3>
	    			<p>{!! nl2br(trans('trp.page.index-dentist.usp.step-1-description')) !!}</p>
	    		</div>
    		</div>
    		<div class="col tac">
    			<img src="{{ url('img-trp/dentacoin-get-more-reviews-icon.png') }}" alt="Dentacoin get more reviews icon">   
    			<div class="info-padding"> 			
	    			<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-2-title')) !!}</h3>
	    			<p>{!! nl2br(trans('trp.page.index-dentist.usp.step-2-description')) !!}</p>
	    		</div>
    		</div>
    	</div>

    	<div class="flex">
    		<div class="col tac">
    			<img src="{{ url('img-trp/dentacoin-better-google-ranking-icon.png') }}" alt="Dentacoin better google ranking icon">
    			<div class="info-padding">
	    			<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-3-title')) !!}</h3>
	    			<p>{!! nl2br(trans('trp.page.index-dentist.usp.step-3-description')) !!}</p>
	    		</div>
    		</div>
    		<div class="col tac">
    			<img src="{{ url('img-trp/dentacoin-better-online-reputation-icon.png') }}" alt="Dentacoin better online reputation icon">
    			<div class="info-padding">
	    			<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-4-title')) !!}</h3>
	    			<p>{!! nl2br(trans('trp.page.index-dentist.usp.step-4-description')) !!}</p>
	    		</div>
    		</div>
    	</div>

    	<div class="tac button-wrap">
			<a href="javascript:;" class="button button-sign-up-dentist" data-popup="popup-register">
    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
    		</a>
    	</div>

		<div class="tac">
			<a href="javascript:;" class="button button-yellow magnet-popup" id="open-magnet" data-url="{{ getLangUrl('lead-magnet-session') }}">{{ trans('trp.page.index-dentist.button-lead-magnet') }}</a>
		</div>
    </div>

    <div class="testimonials-section">
    	<div class="container tac">
    		<h2>{!! nl2br(trans('trp.page.index-dentist.testimonial.title')) !!}</h2>
    		<span>{!! nl2br(trans('trp.page.index-dentist.testimonial.subtitle')) !!}</span>
    	</div>

    	@if($testimonials->isNotEmpty())
	    	<div class="container">
		    	<div class="flickity-testimonial">
		    		@foreach($testimonials as $testim)
			    		<div class="testimonial">
			    			<div class="testimonial-inner">
				    			<img src="{{ $testim->getImageUrl() }}">
				    			<h4>{!! nl2br($testim->description) !!}</h4>
				    			<p class="name">{!! nl2br($testim->name) !!}</p>
				    			<p>{!! nl2br($testim->job) !!}</p>
				    		</div>
			    		</div>
			    	@endforeach
		    	</div>
			</div>
		@endif

    </div>

    <div class="container section-how">

    	<h2 class="tac">
    		{!! nl2br(trans('trp.page.index-dentist.how-works-title')) !!}
    	</h2>

    	<div class="clearfix mobile-flickity">
    		<div class="left">
    			<div class="how-block flex flex-center">
	    			<span class="how-number">01</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-1', [
							'link' => '<a href="javascript:;" data-popup="popup-register">',
							'endlink' => '</a>',
						])) !!}
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="how-number">02</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-2')) !!}
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="how-number">03</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-3')) !!}
	    			</p>
	    		</div>
    		</div>
    		<div class="right">		    			
    			<div class="how-block flex flex-center">
	    			<span class="how-number">04</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-4', [
							'link' => '<a href="https://wallet.dentacoin.com/" target="_blank">',
							'endlink' => '</a>',
						])) !!}
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="how-number">05</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-5')) !!}
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="how-number">06</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-6')) !!}
	    			</p>
	    		</div>
    		</div>
    	</div>

    	<div class="tac">
    		<a href="javascript::" class="button button-sign-up-dentist" data-popup="popup-register">{!! nl2br(trans('trp.page.index-dentist.create-listing')) !!}</a>
    	</div>
    </div>

    <div class="section-learn">
    	<div class="container flex">
    		<div class="col">
    			<img src="{{ url('img-trp/dentacoin-patients-rely-on-only-reviews.png') }}" alt="Dentacoin patients rely on only reviews">
    		</div>
    		<div class="col">
	    		<h2>
	    			{!! nl2br(trans('trp.page.index-dentist.cta')) !!}
	    		</h2>
	    		<a href="javascript:;" class="button button-yellow button-sign-up-dentist" data-popup="popup-register">
	    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
	    		</a>
	    	</div>
    	</div>
    </div>


	@include('trp.popups.lead-magnet')
	
@endsection