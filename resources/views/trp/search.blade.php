@extends('trp')

@section('content')

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
    	<form class="front-form search-form">
    		<i class="fas fa-search"></i>
    		<input id="search-input" type="text" name="location" value="{{ $query }}" placeholder="{!! nl2br(trans('trp.common.search-placeholder')) !!}" autocomplete="off" />
    		<input type="submit" value="">			    		
			<div class="loader">
				<i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i>
			</div>
			<div class="results" style="display: none;">
				<div class="locations-results results-type">
					<span class="result-title">
						{!! nl2br(trans('trp.common.search-locations')) !!}
					</span>

					<div class="clearfix list">
					</div>
				</div>
				<div class="dentists-results results-type">
					<span class="result-title">
						{!! nl2br(trans('trp.common.search-dentists')) !!}
					</span>

					<div class="clearfix list">
					</div>
				</div>
			</div>
    	</form>	
		
	</div>

	<div class="main-top">
    </div>

    <div class="sort-wrapper">
    	<div class="flex">
    		<div class="col">
    			{!! nl2br(trans('trp.page.search.results-count', [
    				'count' => $items->count()
    			])) !!}
    			
    		</div>
    		<div class="col">
    			<a href="javascript:;" class="sort-button" data-popup="sort-popup" >
    				<img src="{{ url('img-trp/sort.png') }}">
					{!! nl2br(trans('trp.page.search.sort-filter')) !!}
    				
    			</a>
    		</div>
    	</div>
    </div>

    <div class="search-results-wrapper container">

    	@if($items->isNotEmpty() && $staticImageUrl && $mode=='map')
    		<a href="javascript:;" class="results-static-map" data-popup="map-results-popup">
	    		<img src="{{ $staticImageUrl }}" />
	    	</a>    		
    	@endif

    	@if($user && $items->where('id', $user->id)->count())
    		<div class="alert alert-info">
    			{!! nl2br(trans('trp.page.search.no-address-dentist',[
    				'link' => '<a href="{{ $user->getLink() }}?open-edit=1">',
    				'endlink' => '</a>',
    			])) !!}
    		</div>
    		<br/>
    	@endif

    	@foreach($items as $dentist)

			<a href="{{ $dentist->getLink() }}" class="result-container dentist clearfix" full-dentist-id="{{ $dentist->id }}">
				<div class="avatar{!! $dentist->hasimage ? '' : ' default-avatar' !!}"  style="background-image: url('{{ $dentist->getImageUrl(true) }}')"></div>
				<div class="media-right">
					<h4>
						{{ $dentist->getName() }}
					</h4>
					@if($dentist->is_partner)
						<span class="type">
							<div class="img">
								<img src="{{ url('img-trp/mini-logo.png') }}">
							</div>
							<span>{!! nl2br(trans('trp.page.search.partner')) !!}</span> 
							{{ $dentist->is_clinic ? 'Clinic' : 'Dentist' }}
						</span>
					@endif
					<div class="p">
						<div class="img">
							<img src="{{ url('img-trp/map-pin.png') }}">
						</div>
						{{ $dentist->city->name }}, {{ $dentist->country->name }} 
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
			    	@if( $dentist->website )
			    		<div class="p dentist-website" href="{{ $dentist->getWebsiteUrl() }}" target="_blank">
			    			<div class="img">
			    				<img src="{{ url('img-trp/site.png') }}">
			    			</div>
			    			{{ $dentist->website }}
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
					@if(!empty($user) && $user->is_dentist)
						<div href="{{ $dentist->getLink() }}" class="button button-submit">
							{!! nl2br(trans('trp.common.see-profile')) !!}
						</div>
					@else
						<div href="{{ $dentist->getLink() }}?popup-loged=submit-review-popup" class="button button-submit">
							{!! nl2br(trans('trp.common.submit-review')) !!}
						</div>
					@endif
					<div class="share-button" data-popup="popup-share" share-href="{{ $dentist->getLink() }}">
						<img src="{{ url('img-trp/share.png') }}">
					</div>
				</div>
			</a>

		@endforeach

	</div>


	<div class="popup fixed-popup results-popup" id="sort-popup">
		<div class="popup-inner inner-white">
			<div class="popup-pc-buttons">
				<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
			</div>

			<div class="popup-mobile-buttons">
				<a href="javascript:;" class="close-popup">< back</a>
			</div>
			<h2><img src="{{ url('img-trp/sort-gray.png') }}">
				{!! nl2br(trans('trp.page.search.sort-filter')) !!}
			</h2>

			<form method="get">

				<div class="sort-stars">
					<h4 class="popup-title">
						{!! nl2br(trans('trp.page.search.stars')) !!}
					</h4>

					<div class="ratings average">
						<div class="stars">
							<div class="bar" style="width: {{ intval($stars)/5*100 }}%;">
							</div>
							<input type="hidden" name="stars" value="{{ $stars }}" />
						</div>
					</div>
				</div>

				<div class="sort-category">
					<h4 class="popup-title">
						{!! nl2br(trans('trp.page.search.category')) !!}
					</h4>
					@foreach( config('categories') as $cat_id => $cat )
						<label class="checkbox-label{!! !empty($searchCategories) && in_array($cat_id, $searchCategories) ? ' active' : '' !!}" for="checkbox-popup-{{ $cat }}">
							<input type="checkbox" class="special-checkbox" id="checkbox-popup-{{ $cat }}" name="searchCategories[]" value="{{ $cat_id }}" {!! in_array($cat_id, $searchCategories) ? 'checked="checked"' : '' !!}>
							<i class="far fa-square"></i>
							{{ trans('front.categories.'.$cat) }}
						</label>
					@endforeach
				</div>

				<div class="sort-partners">
					<h4 class="popup-title">
						{!! nl2br(trans('trp.page.search.partners')) !!}
						
					</h4>

					<label class="checkbox-label{!! $partner ? ' active' : '' !!}" for="checkbox-partner-popup">
						<input type="checkbox" class="special-checkbox" id="checkbox-partner-popup" name="partner" value="1" {!! $partner ? 'checked="checked"' : '' !!}>
						<i class="far fa-square"></i>
						{!! nl2br(trans('trp.page.search.show-partners')) !!}
						<img src="{{ url('img-trp/mini-logo-black.png') }}">
					</label>
				</div>

				<div class="sort-by">
					<h4 class="popup-title">
						{!! nl2br(trans('trp.page.search.sort-by')) !!}
						
					</h4>

					@foreach($orders as $order)
						<a {!! $sort==$order ? 'class="active"' : '' !!} sort="{{ $order }}">
							<i class="fas fa-sort"></i>
							{{ trans('front.page.'.$current_page.'.order-'.$order) }}
						</a>
					@endforeach
					<input type="hidden" name="sort" value="{{ $sort }}" />
				</div>

				<div class="tac">
					<button type="submit" href="javascript:;" class="button">
						{!! nl2br(trans('trp.page.search.apply')) !!}
					</button>
					
					<a class="clear-filters" href="javascript:;">
						{!! nl2br(trans('trp.page.search.reset')) !!}
					</a>
				</div>
			</form>
		</div>
	</div>


	<div class="popup fixed-popup results-popup" id="map-results-popup">
		<div class="popup-inner inner-white">
			<a href="javascript:;" class="close-popup close-map">
				{!! nl2br(trans('trp.page.search.close-map')) !!}
				
				<i class="fas fa-times"></i>
			</a>

			<div class="flex">
				<div class="flex-2">
					<h2><img src="{{ url('img-trp/sort-gray-small.png') }}">
						{!! nl2br(trans('trp.page.search.filter')) !!}
						
					</h2>
    				
    				<form method="get">

						<div class="sort-stars">
							<h4 class="popup-title">{!! nl2br(trans('trp.page.search.stars')) !!}</h4>

							<div class="ratings">
								<div class="stars">
									<div class="bar" style="width: {{ intval($stars)/5*100 }}%;">
									</div>
									<input type="hidden" name="stars" value="{{ $stars }}" />
								</div>
							</div>
						</div>

						<div class="sort-category">
							<h4 class="popup-title">{!! nl2br(trans('trp.page.search.category')) !!}</h4>

							@foreach( config('categories') as $cat_id => $cat )
								<label class="checkbox-label{!! !empty($searchCategories) && in_array($cat_id, $searchCategories) ? ' active' : '' !!}" for="checkbox-filter-{{ $cat }}">
									<input type="checkbox" class="special-checkbox" id="checkbox-filter-{{ $cat }}" name="searchCategories[]" value="{{ $cat_id }}" {!! in_array($cat_id, $searchCategories) ? 'checked="checked"' : '' !!}>
									<i class="far fa-square"></i>
									{{ trans('front.categories.'.$cat) }}
								</label>
							@endforeach
						</div>

						<div class="sort-partners">
							<h4 class="popup-title">{!! nl2br(trans('trp.page.search.partners')) !!}</h4>

							<label class="checkbox-label{!! $partner ? ' active' : '' !!}" for="checkbox-partner">
								<input type="checkbox" class="special-checkbox" id="checkbox-partner" name="partner" value="1" {!! $partner ? 'checked="checked"' : '' !!}>
								<i class="far fa-square"></i>
								{!! nl2br(trans('trp.page.search.show-partners')) !!}
								<img src="{{ url('img-trp/mini-logo-black.png') }}">
							</label>
						</div>


						<div class="sort-by">
							<h4 class="popup-title">
								{!! nl2br(trans('trp.page.search.sort-by')) !!}
							</h4>

							@foreach($orders as $order)
								<a {!! $sort==$order ? 'class="active"' : '' !!} sort="{{ $order }}">
									<i class="fas fa-sort"></i>
									{{ trans('front.page.'.$current_page.'.order-'.$order) }}
								</a>
							@endforeach
							<input type="hidden" name="sort" value="{{ $sort }}" />
						</div>

						<input type="hidden" name="popup" value="map-results-popup">

						<button type="submit" href="javascript:;" class="button">
							{!! nl2br(trans('trp.page.search.apply')) !!}
						</button>
						
						<a class="clear-filters" href="javascript:;">
							{!! nl2br(trans('trp.page.search.reset')) !!}
						</a>
					</form>
				</div>
				<div class="flex-3">

					@if($items->isNotEmpty())
				    	@foreach($items as $dentist)

				    		@if($dentist->address)

								<a href="{{ $dentist->getLink() }}" class="result-container dentist clearfix" lat="{{ $dentist->lat }}" lon="{{ $dentist->lon }}" dentist-id="{{ $dentist->id }}">
									<div class="avatar{!! $dentist->hasimage ? '' : ' default-avatar' !!}" style="background-image: url('{{ $dentist->getImageUrl(true) }}')">
										@if($dentist->is_partner)
											<img src="{{ url('img-trp/mini-logo.png') }}">
										@endif
									</div>
									<div class="media-right">
										<h4>
											{{ $dentist->getName() }}
										</h4>
										<span class="type">
											{{ $dentist->is_clinic ? 'Clinic' : 'Dentist' }}
										</span>
								    	@if( $time = $dentist->getWorkHoursText() )
								    		<p>
								    			<img src="{{ url('img-trp/open.png') }}">
								    			{!! $time !!}
								    		</p>
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
								</a>

							@endif

						@endforeach

						@if($items->where('address', '')->count())
							<div class="alert alert-info mobile">
								{!! nl2br(trans('trp.page.search.no-address')) !!}
							</div>
						@endif
					@else
						<div class="alert alert-info">
							{!! nl2br(trans('trp.page.search.no-results')) !!}
						</div>
					@endif
				</div>
				<div class="flex-7">
					<div id="search-map" lat="{{ $lat }}" lon="{{ $lon }}">
					</div>
				</div>

				<a id="map-mobile-tooltip" class="result-container">
				</a>
			</div>
		</div>
	</div>

@endsection