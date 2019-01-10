<div class="popup fixed-popup" id="submit-review-popup">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>



		@if($item->id == $user->id)
			<div class="alert alert-info">
				{{ trans('trp.popup.submit-review-popup.self') }}
			</div>
		@elseif(!$user->civic_id)
			<h2>
				{!! nl2br(trans('trp.popup.submit-review-popup.title')) !!}
				
			</h2>
			<div class="question">
				<h4 class="popup-title">
					{!! nl2br(trans('trp.popup.submit-review-popup.civic-title')) !!}
					
				</h4>
				<div class="review-answers">
					<p>
						{!! nl2br(trans('trp.popup.submit-review-popup.civic-hint')) !!}
						
						<br/>
						<br/>
					</p>
					<p>
						{!! nl2br(trans('trp.popup.submit-review-popup.civic-1')) !!}
						
						<br/>
						<br/>
					</p>
                	<p>
                		<a href="https://play.google.com/store/apps/details?id=com.civic.sip" target="_blank" class="civic-download civic-android"></a>
                		<a href="https://itunes.apple.com/us/app/civic-secure-identity/id1141956958?mt=8" target="_blank" class="civic-download civic-ios"></a>
						<br/>
						<br/>
                	</p>
					<p>
						{!! nl2br(trans('trp.popup.submit-review-popup.civic-2')) !!}
						
						<br/>
						<br/>
					</p>

					<button id="signupButton" class="civic-button-a medium" type="button" scope="BASIC_SIGNUP">
						<span style="color: white;">
							{!! nl2br(trans('vox.page.profile.home.civic-button')) !!}
						</span>
					</button>

					<div id="civic-cancelled" class="alert alert-info" style="display: none;">
						{!! nl2br(trans('vox.page.profile.home.civic-cancelled')) !!}
					</div>
					<div id="civic-error" class="alert alert-warning" style="display: none;">
						{!! nl2br(trans('vox.page.profile.home.civic-error')) !!}
					</div>
					<div id="civic-weak" class="alert alert-warning" style="display: none;">
						{!! nl2br(trans('vox.page.profile.home.civic-weak')) !!}
					</div>
					<div id="civic-wait" class="alert alert-info" style="display: none;">
						{!! nl2br(trans('vox.page.profile.home.civic-wait')) !!}
					</div>
					<div id="civic-duplicate" class="alert alert-warning" style="display: none;">
						{!! nl2br(trans('vox.page.profile.home.civic-duplicate')) !!}
					</div>
					<input type="hidden" id="jwtAddress" value="{!! getLangUrl('profile/jwt') !!}" />

				</div>
			</div>

		@elseif($dentist_limit_reached)
			<div class="alert alert-info">
				@if($has_asked_dentist)
					@if($has_asked_dentist->status=='no')
						{!! nl2br(trans('trp.popup.submit-review-popup.limit-denied', [ 'name' => $item->getName() ])) !!}
					@else
						{!! nl2br(trans('trp.popup.submit-review-popup.limit-waiting', [ 'name' => $item->getName() ])) !!}
					@endif
				@else
					{!! nl2br(trans('trp.popup.submit-review-popup.limit-hint', [ 'name' => $item->getName() ])) !!}
					<br/>
					<br/>
					<a href="{{ $item->getLink().'/ask' }}" class="button ask-dentist">
						{!! nl2br(trans('trp.popup.submit-review-popup.limit-send')) !!}
					</a>
				@endif
			</div>
			<div class="alert alert-success ask-success" style="display: none;">
				{!! nl2br(trans('trp.popup.submit-review-popup.limit-success', [ 'name' => $item->getName() ])) !!}
			</div>
		@elseif($user->loggedFromBadIp())
			<div class="alert alert-info">
				{!! nl2br(trans('trp.popup.submit-review-popup.bad-ip')) !!}
			</div>
		@elseif($review_limit_reached)
			<div class="alert alert-info">
				{!! nl2br(trans('trp.popup.submit-review-popup.limit-reached-'.$review_limit_reached, [ 'name' => $item->getName() ])) !!}
			</div>
		@elseif(!empty($my_review))
			<div class="alert alert-info">
				{!! nl2br(trans('trp.popup.submit-review-popup.already-left')) !!}
				
			</div>
		@elseif($user->is_dentist)
			<div class="alert alert-info">
				{!! nl2br(trans('trp.popup.submit-review-popup.is-dentist')) !!}
				
			</div>
		@elseif(!empty($user))
			<div class="dcn-review-reward" {!! $is_trusted ? '' : 'style="display: none;"' !!}>
				<img src="{{ url('img-trp/mini-logo-blue.png') }}">
				<span class="reward-info">
					DCN 
					<span id="review-reward-so-far">0</span> / 
					<span id="review-reward-total" standard="{{ $review_reward }}" video="{{ $review_reward_video }}">{{ $review_reward }}</span>
				</span>
			</div>
			
			<h2>
				{!! nl2br(trans('trp.popup.submit-review-popup.title')) !!}
			</h2>

			{!! Form::open(array('url' => $item->getLink(), 'id' => 'write-review-form', 'method' => 'post')) !!}
				<div class="questions-wrapper">

					@if($item->is_dentist && !$item->is_clinic && $item->my_workplace_approved->isNotEmpty())	
						<div class="question skippable">
							<h4 class="popup-title">
								{!! nl2br(trans('trp.popup.submit-review-popup.dentist-visit', ['name' => $item->getName() ])) !!}
							</h4>
							<div class="review-answers">
								<div class="clearfix subquestion">
								   <select name="dentist_clinics" class="input">
										<option value="">{{ trans('trp.popup.submit-review-popup.dentist-cabinet') }}</option>
										@foreach($item->my_workplace_approved as $workplace)
											<option value="{{ $workplace->clinic->id }}">{{ $workplace->clinic->getName() }}</option>
										@endforeach
									</select>
						        </div>
						    </div>
						</div>
					@endif

					@foreach($questions as $qid => $question)
						@if($item->is_clinic && $item->teamApproved->isNotEmpty() && $loop->iteration == 4 )
							<div class="question skippable">
								<h4 class="popup-title">
									{{ trans('trp.popup.submit-review-popup.dentist-treat') }}
								</h4>
								<div class="review-answers">
									<div class="clearfix subquestion">
							            <select name="clinic_dentists" class="input" id="clinic_dentists">
											<option value="">
												{!! nl2br(trans('trp.popup.submit-review-popup.dentist-dont-remember')) !!}
											</option>
											@foreach($item->teamApproved as $team)
												<option value="{{ $team->clinicTeam->id }}">{{ $team->clinicTeam->getName() }}</option>
											@endforeach
										</select>
							        </div>
							    </div>
							</div>
						@endif


						<div class="question {{ $item->is_clinic && $item->team->isNotEmpty() && $item->team->count() > 1 && $loop->iteration == 4 ? 'hidden-review-question' : '' }}" {{ $item->is_clinic && $item->team->isNotEmpty() && $item->team->count() > 1 && $loop->iteration == 4 ? 'style=display:none;' : '' }}>
							<h4 class="popup-title">
								{{ str_replace('{name}', $item->name, $question->question) }}
							</h4>
					
						    <div class="review-answers">
					    	@foreach(json_decode($question['options'], true) as $i => $option)
								<div class="clearfix subquestion">
									<div class="answer">
										{{ $option[0] }}
									</div>
									<div class="answer">
										<div class="ratings average tac">
											<div class="stars">
												<div class="bar" style="width: {{ $my_review ? json_decode($my_review->answers[$qid]->options, true)[$i]*5/100 : 0 }};%">
												</div>
												<input type="hidden" name="option[{{ $question['id'] }}][]" value="{{ $my_review ? json_decode($my_review->answers[$qid]->options, true)[$i] : '' }}" />
											</div>
										</div>
									</div>
									<div class="answer tar">
										{{ $option[1] }}
									</div>
								</div>
					    	@endforeach
							</div>
							<div class="rating-error" style="display: none;">
								{!! nl2br(trans('trp.popup.submit-review-popup.answer-all')) !!}
							</div>
						</div>
					@endforeach

					<div class="question">

						<h4 class="popup-title">
							<span class="blue">
								{!! nl2br(trans('trp.popup.submit-review-popup.last-question')) !!}
							</span>
							{!! nl2br(trans('trp.popup.submit-review-popup.last-question-text', ['name' => $item->getName()])) !!}
						</h4>
						

						<div class="reviews-wrapper">

							<div class="review-tabs flex-tablet">
								<a class="active" href="javascript:;" data-type="text">
									{!! nl2br(trans('trp.popup.submit-review-popup.text-review')) !!}
									
								</a>
								<span>or</span>
								<a class="video-button" href="javascript:;" data-type="video">
									{!! nl2br(trans('trp.popup.submit-review-popup.video-review')) !!}
									<div class="video-dcn"  {!! $is_trusted ? '' : 'style="display: none;"' !!}>
										+{{ $review_reward_video - $review_reward }} DCN
									</div>
								</a>
							</div>

							<div class="review-box">

								<input type="text" class="input" id="review-title" name="title" value="{{ $my_review ? $my_review->title : '' }}" placeholder="{!! nl2br(trans('trp.popup.submit-review-popup.title-placeholder')) !!}">

								<div id="review-option-text" class="review-type-content" style="">
									{{ Form::textarea( 'answer', $my_review ? $my_review->answer : '', array( 'id' => 'review-answer', 'class' => 'input', 'placeholder' => trans('trp.popup.submit-review-popup.last-question-placeholder') )) }}
								</div>

								<div id="review-option-video" class="review-type-content" style="display: none;">
									@if($my_review && $my_review->youtube_id)
										<div class="alert alert-info">
											{!! nl2br(trans('trp.popup.submit-review-popup.video-already-shot')) !!}
										</div>
										<div class="videoWrapper">
											<iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $my_review->youtube_id }}" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
										</div>
									@else
										<p>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-title')) !!}
											
										</p>
										<span class="option-span">
											<b>01</b>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-1')) !!}
											
										</span>
										<span class="option-span">
											<b>02</b>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-2')) !!}
											
										</span>
										<span class="option-span">
											<b>03</b>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-3')) !!}
											
										</span>
										<span class="option-span">
											<b>04</b>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-4')) !!}
											
										</span>
										<span class="option-span">
											<b>05</b>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-5')) !!}
											
										</span>

										<label class="checkbox-label" for="video-agree">
											<input type="checkbox" class="special-checkbox" id="video-agree" name="video-agree" value="video-agree">
											<i class="far fa-square"></i>
											{!! nl2br(trans('trp.popup.submit-review-popup.video-widget-terms', [
												'link' => '<a class="read-privacy" target="_blank" href="https://dentacoin.com/privacy-policy">',
												'endlink' => '</a>',												
											])) !!}
											
										</label>

										<div class="alert alert-warning" style="display: none;" id="video-not-agree">
											{!! nl2br(trans('trp.popup.submit-review-popup.video-agree')) !!}
										</div>

										<video id="myVideo" class="video-js vjs-default-skin"></video>

										<div class="tac custom-controls" style="margin-top: 20px;">
											<div class="alert alert-warning" style="display: none;" id="video-error">
												{{ trans('trp.popup.submit-review-popup.video-error') }}
											</div>
											<div class="alert alert-warning" style="display: none;" id="video-denied">
												{{ trans('trp.popup.submit-review-popup.video-denied') }}
											</div>
											<div class="alert alert-warning" style="display: none;" id="video-short">
												{{ trans('trp.popup.submit-review-popup.video-short') }}
											</div>


											<a href="javascript:;" id="init-video" class="button">
												<i class="fas fa-video" style="color: white; margin-right: 5px;"></i>
												{{ trans('trp.popup.submit-review-popup.video-allow') }}
											</a>
											
											<a href="javascript:;" id="start-video" class="button" style="display: none;">
												<i class="fas fa-film"></i>
												{{ trans('trp.popup.submit-review-popup.video-start') }}
											</a>

											<a href="javascript:;" id="stop-video" class="button" style="display: none;">
												<i class="fas fa-stop-circle"></i>
												{{ trans('trp.popup.submit-review-popup.video-stop') }}
											</a>
											
											<div id="video-progress" style="display: none;">
												{!! trans('trp.popup.submit-review-popup.video-processing',[
													'percent' => '<span id="video-progress-percent"></span>'
												]) !!}
											</div>
											
											<div id="video-youtube" style="display: none;">
												{{ trans('trp.popup.submit-review-popup.video-youtube') }}
											</div>
											
											<div class="alert alert-success" style="display: none;" id="video-uploaded">
												{{ trans('trp.popup.submit-review-popup.video-uploaded') }}
											</div>
										</div>
									@endif
									<input type="hidden" id="youtube_id" name="youtube_id" value="{{ $my_review ? $my_review->youtube_id : '' }}" />

								</div>
							</div>

						</div>
						
						<div class="tac">
							<button type="submit" class="button"  id="review-submit-button" data-loading="{{ trans('trp.popup.submit-review-popup.loading') }}" >
								{{ trans('trp.popup.submit-review-popup.submit') }}
							</button>
						</div>


						<div class="alert alert-warning" id="review-answer-error" style="display: none;">
							{{ trans( 'trp.popup.submit-review-popup.last-question-invalid' ) }}
						</div>

						<div class="alert alert-warning" id="review-error" style="display: none;">
							{!! nl2br(trans('trp.popup.submit-review-popup.answer-all')) !!}
						</div>
						<div class="alert alert-warning" id="review-short-text" style="display: none;">
							{{ trans('trp.popup.submit-review-popup.text-short') }}
						</div>

		                <div class="alert alert-warning" id="review-crypto-error" style="display: none;">
		                	{{ trans('trp.popup.submit-review-popup.crypto-error') }}
			            	<span class="error-info" style="display: block; margin: 10px 0px;">
			            	</span>
		                </div>
			            <div class="alert alert-info" id="review-confirmed" style="display: none;">
			            	@if($is_trusted)
				            	{!! trans('trp.popup.submit-review-popup.done',[
				            		'link' => '<a href="'.getLangUrl('profile').'">',
				            		'endlink' => '</a>',
				            	]) !!}
				            	<br/>
				            	<br/>
				            	<a class="button" href="{{ $item->getLink() }}">
				            		{{ trans('trp.popup.submit-review-popup.my-review') }}
				            	</a>
				            @else
				            	{{ trans('trp.popup.submit-review-popup.done-non-trusted') }}
				            	<br/>
				            	<br/>
				            	<a class="button" data-popup-logged="popup-ask-dentist">
				            		{{ trans('trp.popup.submit-review-popup.done-non-trusted-invite') }}
				            	</a>
				            @endif
			            </div>

					</div>


		        </div>
			{!! Form::close() !!}
		@endif

	</div>
</div>