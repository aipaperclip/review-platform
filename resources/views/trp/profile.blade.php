@extends('trp')

@section('content')

	<div class="blue-background"></div>

	<div class="container flex break-tablet">
		<div class="col">
			@include('trp.parts.profile-menu')
		</div>
		<div class="flex-3">
			<h2 class="page-title">
				<img src="{!! url('new-vox-img/profile-home.png') !!}" />
				{!! trans('trp.page.profile.home.title') !!}
			</h2>

			<div class="form-horizontal">

				<div class="black-line-title">
	                <h4 class="bold">
	                	{!! trans('trp.page.profile.home.balance') !!}
	                </h4>
	            </div>

				<div class="profile-home-content">
					<div class="balance">
						<b><span class="dcn-amount">{!! number_format( $user->getVoxBalance(), 0, '.', ' ') !!}</span> DCN</b>
						<div class="convertor">
							= <span class="convertor-value"></span>
							<span class="convertor-currnecy">
								<span class="active-currency">
									USD
								</span>

								<div class="expander">
									@foreach( config('currencies') as $currency )
										<a currency="{!! $currency !!}" {!! $currency=='USD' ? 'class="active"' : '' !!}>{!! $currency !!}</a>
									@endforeach
								</div>
							</span>
						</div>
					</div>
				</div>
			</div>


			@if($user->loggedFromBadIp())

				<div class="form-horizontal">
					<div class="alert alert-warning">
						{!! trans('trp.page.profile.home.wallet-bad-ip') !!}
					</div>
				</div>

			@else
            	
            	@include('front.errors')

	        	@if($user->dcn_address)
					<div class="form-horizontal">

						<div class="black-line-title">
			                <h4 class="bold">
			                	{!! trans('trp.page.profile.home.withdraw.title') !!}		                	
			                </h4>
			            </div>

			    		@if(!$user->civic_kyc)
				    		<p class="personal-description">
								{!! nl2br(trans('trp.page.profile.home.civic-hint')) !!}
								<br/>
								<br/>
		                	</p>
		                	<p class="personal-description">
		                		{!! nl2br(trans('trp.page.profile.home.civic-buttons')) !!}
								<br/>
								<br/>
		                	</p>
		                	<p  class="personal-description">
		                		<a href="https://play.google.com/store/apps/details?id=com.civic.sip" target="_blank" class="civic-download civic-android"></a>
		                		<a href="https://itunes.apple.com/us/app/civic-secure-identity/id1141956958?mt=8" target="_blank" class="civic-download civic-ios"></a>
								<br/>
								<br/>
		                	</p>
		                	<p class="personal-description">
		                		{!! nl2br(trans('trp.page.profile.home.civic-login')) !!}
								<br/>
								<br/>
		                	</p>

							<button id="signupButton" class="civic-button-a medium" type="button" scope="PROOF_OF_IDENTITY">
								<span style="color: white;">{!! nl2br(trans('trp.page.profile.home.civic-button')) !!}</span>
							</button>

							<div id="civic-cancelled" class="alert alert-info" style="display: none;">
								{!! nl2br(trans('trp.page.profile.home.civic-cancelled')) !!}
							</div>
							<div id="civic-error" class="alert alert-warning" style="display: none;">
								{!! nl2br(trans('trp.page.profile.home.civic-error')) !!}
							</div>
							<div id="civic-weak" class="alert alert-warning" style="display: none;">
								{!! nl2br(trans('trp.page.profile.home.civic-weak')) !!}
							</div>
							<div id="civic-wait" class="alert alert-info" style="display: none;">
								{!! nl2br(trans('trp.page.profile.home.civic-wait')) !!}
							</div>
							<div id="civic-duplicate" class="alert alert-warning" style="display: none;">
								{!! nl2br(trans('trp.page.profile.home.civic-duplicate')) !!}
							</div>
							<input type="hidden" id="jwtAddress" value="{!! getLangUrl('profile/jwt') !!}" />
			    		@else
				    		<p class="personal-description">
				    			{!! trans('trp.page.profile.home.withdraw.hint') !!}
				    		</p>

				    		<form id="withdraw-form" method="post" class="form-horizontal" action="{!! getLangurl('profile/withdraw') !!}">
	                			{!! csrf_field() !!}

					            <div class="form-group">
					  				<label class="control-label top-label">
					  					{!! trans('trp.page.profile.home.withdraw.address') !!}
					  				</label>
					                <input class="form-control style-2" id="vox-address" name="vox-address" type="text" value="{!! $user->dcn_address !!}">
					            </div>

					            <div class="form-group separated">
					  				<label class="control-label top-label">
					  					{!! trans('trp.page.profile.home.withdraw.amount') !!}
					  				</label>
					  				<div class="flex break-tablet">
						                <div class="flex-3">
						                    <input class="form-control style-2" id="wallet-amount" name="wallet-amount" type="number" value="" placeholder="{!! trans('trp.page.profile.wallet-withdraw-amount') !!}">
						                </div>
										<div class="col">
					                        <button type="submit" name="update" class="btn btn-inactive form-control nom style-2" data-loading="{!! trans('trp.common.loading') !!}">
						  						{!! trans('trp.page.profile.home.withdraw.button') !!}
					                        </button>
										</div>
									</div>
					            </div>
					            @if($user->isGasExpensive())
						            <div class="alert alert-warning">
						            	{!! trans('trp.page.profile.wallet-withdraw-gas') !!}
			                        </div>
			                    @endif

		                        <div class="alert alert-success" style="display: none;" id="withdraw-pending">
		                        	{!! trans('trp.page.profile.wallet-withdraw-pending') !!}
		                        </div>
		                        <div class="alert alert-success" style="display: none;" id="withdraw-success">
		                        	{!! trans('trp.page.profile.wallet-withdraw-success') !!}
		                        	<a target="_blank">
		                        	</a>
		                        </div>
		                        <div class="alert alert-warning" style="display: none;" id="withdraw-error">
		                        	{!! trans('trp.page.profile.wallet-withdraw-error') !!}
		                        	<div id="withdraw-reason">
		                        	</div>
		                        </div>
			            	</form>
			    		@endif
				@else


					<div class="form-horizontal">


						<div class="black-line-title">
			                <h4 class="bold">
			                	{!! trans('trp.page.profile.home.address.title') !!}		                	
			                </h4>
			            </div>

	                	<p class="personal-description">
	                		{!! trans('trp.page.profile.home.address.hint') !!}
	                	</p>



						<form class="form-horizontal" method="post" id="balance-form">
			                {!! csrf_field() !!}
				            <div class="form-group">
					  			<div class="flex break-tablet">
					                <div class="flex-5">
					                    <input class="form-control" id="vox-address" name="vox-address" type="text" value="{!! $user->dcn_address !!}">
					                </div>
					                <div class="col">
				                        <button type="submit" name="update" class="btn btn-primary form-control nom">
				                        	{!! trans('trp.page.profile.home.address.button') !!}				                        	
				                        </button>
					                </div>
				                </div>
				            </div>
						</form>
					</div>

				@endif


                @if($history->isNotEmpty())

					<div class="form-horizontal">

						<div class="black-line-title">
			                <h4 class="bold">
			                	{!! trans('trp.page.profile.home.history.title') !!}
			                </h4>
			            </div>
		            	<table class="table">
		            		<thead>
		            			<tr>
			            			<th>
			            				{!! trans('trp.page.profile.history.list-date') !!}
			            			</th>
			            			<th>
			            				{!! trans('trp.page.profile.history.list-amount') !!}
			            			</th>
			            			<th>
			            				{!! trans('trp.page.profile.history.list-address') !!}
			            			</th>
			            			<th>
			            				{!! trans('trp.page.profile.history.list-status') !!}
			            			</th>
		            			</tr>
		            		</thead>
		            		<tbody>
		            			@foreach( $history as $trans )
		            				<tr>
		            					<td>
		            						{!! $trans->created_at->toDateString() !!}
		            					</td>
		            					<td>
		            						{!! $trans->amount !!} DCN
		            					</td>
		            					<td>
		            						<div class="vox-address">{!! $trans->address !!}</div>
		            					</td>
		            					<td>
		            						@if($trans->status=='new')
		            							{!! trans('trp.page.profile.history.status-new') !!}
		            						@elseif($trans->status=='failed')
		            							{!! trans('trp.page.profile.history.status-failed') !!}
		            						@elseif($trans->status=='unconfirmed')
		            							<a class="unconfirmed" href="https://etherscan.io/tx/{!! $trans->tx_hash !!}" target="_blank">
		            								{!! trans('trp.page.profile.history.status-unconfirmed') !!}
		            								<i class="fa fa-share-square-o"></i>
		            							</a>
		            						@elseif($trans->status=='completed')
		            							<a class="completed" href="https://etherscan.io/tx/{!! $trans->tx_hash !!}" target="_blank">
		            								{!! trans('trp.page.profile.history.status-completed') !!}		            								
		            								<i class="fa fa-share-square-o"></i>
		            							</a>
		            						@endif
		            					</td>
		            				</tr>
		            			@endforeach
		            		</tbody>
		            	</table>
			        </div>
	            @endif


			@endif

		</div>
	</div>

	<script type="text/javascript">
		var currency_rates = {!! $currencies !!};
		
	</script>

@endsection