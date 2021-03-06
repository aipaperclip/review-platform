var VoxTest = {}
ajax_is_running = false;
var handleScrollbars;
var scrollToActive;
var FB_status;
var fbLogin;
var fb_city_id;
var checkFilledDots;
var simpleCountDown, hoursCountdown;
var simpleCountDownTO, hoursCountdownTO;
var flickityScales;

var mapsLoaded = false;
var mapsWaiting = [];
var modernFieldsUpdate;
var welcome_vox;
var welcome_vox_q_count;
var pollsFunction;
var pollStats;
var pollStatsAnimate;
var tooltipsFunction;
var calendarEvents;
var calendarListEvents;
var showStats;

var preloadImages = function(urls, allImagesLoadedCallback){
    var loadedCounter = 0;
    var toBeLoadedNumber = urls.length;
    var preloadImage = function(url, anImageLoadedCallback){
        var img = new Image();
        img.onload = anImageLoadedCallback;
        img.src = url;
    }
    urls.forEach(function(url){
        preloadImage(url, function(){
            loadedCounter++;
                console.log('Number of loaded images: ' + loadedCounter);
          if(loadedCounter == toBeLoadedNumber){
            allImagesLoadedCallback();
          }
        });
    });
}


//
//Maps stuff
//


var prepareMapFucntion = function( callback ) {
    if(mapsLoaded) {
        callback();
    } else {
        mapsWaiting.push(callback);
    }
}

var initMap = function () {
    mapsLoaded = true;
    for(var i in mapsWaiting) {
        mapsWaiting[i]();
    }

	$('.map').each( function(){
		var address = $(this).attr('data-address') ;

		var geocoder = new google.maps.Geocoder();
		geocoder.geocode( { 'address': address}, (function(results, status) {
			console.log(address);
			console.log(status);
	        if (status == google.maps.GeocoderStatus.OK) {
				if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
					var position = {
						lat: results[0].geometry.location.lat(), 
						lng: results[0].geometry.location.lng()
					};

					map = new google.maps.Map($(this)[0], {
						center: position,
    					scrollwheel: false,
						zoom: 15
					});

					new google.maps.Marker({
						position: position,
						map: map,
						title: results[0].formatted_address
					});

				} else {
					console.log('456');
					$(this).remove();
				}
			} else {
				console.log('123');
				$(this).remove();
			}
		}).bind( $(this) )  );

	});
}






$(document).ready(function(){

	$.cookie.json = true;

	checkFilledDots = function( event, index) {
		var goods = new Array;
		var flickity = $('.flickity:visible');
		var missing = false;
		if( flickity.length ) {
			flickity.find('.answer-radios-group').each( function() {
	            if( $(this).find('.answer-radio.active-label').length ) {
	                goods.push(true);
	            } else {
	                goods.push(false);
	                missing = true;
	            }
	        } );
	        var i=0;
	        flickity.find('.flickity-page-dots .dot').each( function() {
	            if(goods[i]) {
	                $(this).addClass('filled');
	            } else {
	                $(this).removeClass('filled');
	            }
	            i++;
	        } );

	        if(!missing) {
				$('.question-group:visible .next-answer').show().trigger('click');
	        }
		}
	}

	VoxTest.handleNextQuestion = function() {
		$('#current-question-bar').css('width', ((vox.current / vox.count)*100)+'%');
		var mins = Math.ceil( (vox.count - vox.current)/6 );
		$('#current-question-num').html( mins<2 ? '<1' : '~'+mins );

		// if (welcome_vox) {
		// 	if( vox.current > (vox.count_real + welcome_vox_q_count) ) {
		// 		$('#dcn-test-reward-bonus').show();

		// 	}
		// } else {
		// 	if( vox.current > vox.count_real ) {
		// 		$('#dcn-test-reward-bonus').show();

		// 	}
		// }

		if(vox.current>1) {

			var answerd_q = 0;
			$('.question-group').each( function() {
				if( $(this).attr('data-answer') && $(this).attr('data-answer')!='0' && !$(this).attr('welcome') ) {
					answerd_q++;
				}
			});

			var welcomeBonus = 0;
			if( welcome_vox ) {
				var doingAsl = vox.current > ( (vox.count_real+1) + welcome_vox_q_count );
				var doingWelcome = vox.current <= welcome_vox_q_count;
				if(!doingWelcome && !doingAsl) {
					welcomeBonus = 100;
				}

			} else {
				var doingAsl = vox.current > (vox.count_real+1);
				var doingWelcome = false;
			}

			if( doingAsl ) {
				// console.log('doingAsl');
				// var old = parseInt( $('#bonus-question-reward').text().trim() );
				// $('#bonus-question-reward').html( old + vox.reward_single );

				// old = parseInt( $('.coins-test').first().text().trim() );
				// $('.coins-test').html( old + parseInt(vox.reward_single) );

			} else if( doingWelcome ) {
                            
                var w_answers = [];
                if($('#welcome_answ').length) {
                    $('.question-group[welcome=1]').each( function() {
                        var d_ans = $(this).attr('data-answer');

                        if (typeof d_ans !== typeof undefined && d_ans !== false) {
                            w_answers.push($(this).attr('data-id')+':'+$(this).attr('data-answer'));
                        }
                        
                    });
                    $('#welcome_answ').val(w_answers.join(';'));
                }
                
				console.log('doingWelcome');
				$('#current-question-reward').html( (vox.current / welcome_vox_q_count) * 100 );
				$('#dcn-test-reward-before').html('DCN: 100');
			} else { //Нормални
				
				console.log('normal');
				var reward = 0;
				if( $('body').hasClass('page-welcome-survey') ) {
					var reward_per_question = ( vox.reward / vox.count_real );
					reward = reward_per_question * answerd_q;
				} else {
					reward = vox.reward_single * answerd_q;					
				}
				reward += welcomeBonus;

				$('#current-question-reward').html( Math.round(reward) );
				$('#dcn-test-reward-before').hide();
				$('#dcn-test-reward-after').show();
				$('.coins-test').html( Math.round(reward) );
			}
		}

		//if (window.innerWidth < 768 && $('.question-group:visible').hasClass('scale')) {
		if ($('.question-group:visible').hasClass('scale')) {

			flickityScales = $('.question-group:visible .flickity').flickity({
				wrapAround: true,
				adaptiveHeight: true,
				draggable: false
			});

			$('.question-group:visible .flickity').on( 'select.flickity', checkFilledDots);
			$('.question-group:visible .next-answer').hide();
		}

		if($('.question-group:visible').hasClass('shuffle')) {
			var parent = $('.question-group:visible .answers');
		    var divs = parent.children().not(".disabler-label");

		    while (divs.length) {
		        parent.prepend(divs.splice(Math.floor(Math.random() * divs.length), 1)[0]);
		    }
		}
	}
	
	if(typeof(vox)!='undefined') {
		VoxTest.handleNextQuestion();		
	}

	if ($('#bot-group').length) {
		$('.question-group').hide();
        $('#bot-group').show();
	} else if ($('.question-group[welcome=1]').length) {
        $('.question-group').hide();
        console.log('welcome-show');
        $('.question-group[welcome]').first().show();
    } else if($('.user-detail-question').length) {
        $('.question-group').hide();
        console.log('demographics-show');
        $('.user-detail-question').first().show();
    }


    $('.country-select').change( function() {
    	$(this).closest('form').find('input[name="address"]').val('');
    	$(this).closest('form').find('.suggester-map-div').hide();
    	$(this).closest('form').find('.geoip-confirmation').hide();
        $(this).closest('form').find('.geoip-hint').hide();
        $(this).closest('form').find('.different-country-hint').hide();

    	var city_select = $(this).closest('form').find('.city-select').first();
    	city_select.attr('disabled', 'disabled');
		$.ajax( {
			url: '/cities/' + $(this).val(),
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				// console.log(data);
    			city_select.attr('disabled', false)
			    .find('option')
			    .remove();
			    for(var i in data.cities) {
			    	// console.log( fb_city_id, data.cities[i] );
    				city_select.append('<option value="'+i+'" '+(fb_city_id && fb_city_id==data.cities[i] ? 'selected="selected"' : '' )+'>'+data.cities[i]+'</option>');
			    }
				//city_select
                $('.phone-code-holder').html(data.code);
				//$('#modal-message .modal-body').html(data);
			}
		});
    } );

    $('.second-absolute').click( function(e) {
		$('html, body').animate({
        	scrollTop: $('.section-recent-surveys').offset().top
        }, 500);
	});

	function checkbox_active() {
		$('.input-checkbox').click( function(e) {
			if ($(this).closest('label').hasClass('active')) {
				$(this).closest('label').removeClass('active');
			} else {
				$(this).closest('label').addClass('active');
			}
		});
	}
	checkbox_active();

	$('.user-type-mobile a').click( function() {
		$('.user-type-mobile a').removeClass('active');
		$(this).addClass('active');
		$('.reg-wrapper .col').removeClass('active');
		$('.'+$(this).attr('type')).addClass('active');

		if(Cookies.get('functionality_cookies')) {
			Cookies.set('user-type', $(this).attr('type'), { expires: 365, secure: true });
		}
		
	} );

	if( Cookies.get('user-type') ) {
		$('.user-type-mobile a[type="'+Cookies.get('user-type')+'"]').trigger('click');
	} else {
		$('.user-type-mobile a[type="reg-patients"]').trigger('click');
	}


	$('input').focus( function() {
		$(this).removeClass('has-error');
	});

	$('.type-radio').change( function(e) {
		$(this).closest('.modern-radios').find('label').removeClass('active');
		$(this).closest('label').addClass('active');
	});

	$('input[name="mode"]').change( function() {
        $(this).closest('.modern-radios').removeClass('has-error');
		$('.ajax-alert[error="'+$(this).attr('name')+'"]').remove();
		
        var val = $('#mode-clinic:checked').length;
        if(val) {
            $('.title-wrap').hide();
        } else {
            $('.title-wrap').show();
        }
    } );

    $('#register-form input').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            e.preventDefault();
            return false;
        }
    });

    modernFieldsUpdate = function() {
	    $('.modern-input').focus( function() {
	    	$(this).closest('.modern-field').addClass('active');
	    	$(this).removeClass('has-error');
	    	$('.ajax-alert[error="'+$(this).attr('name')+'"]').remove();
	    });

	    $('.modern-input').focusout( function() {
	    	if (!$(this).val()) {
	    		$(this).closest('.modern-field').removeClass('active');
	    	}
	    });

	    if ($('.modern-input').length) {
	    	setTimeout( function() {

		    	$('.modern-input').each( function() {
		    		if ($(this).val() || $(this).is(":-webkit-autofill")) {
		    			$(this).closest('.modern-field').addClass('active');
		    		}
		    	});
	    	} , 0)
	    }
    }
    modernFieldsUpdate();

    $('#register-form .address-suggester').focus(function(e) {
        $('.go-to-next[step-number="3"]').addClass('disabled');
    });

	$('.go-to-next').click( function(e) {
        e.preventDefault();

        if (!$(this).hasClass('disabled')) {

	        if(ajax_is_running) {
	            return;
	        }
	        ajax_is_running = true;

	        $('.form-group').removeClass('has-error');
	        $('.ip-country').hide();

	        var that = $(this);

	        $.post( 
	            $(this).attr('data-validator'), 
	            $('#register-form').serialize(), 
	            function( data ) {
	                if(data.success) {
	                    $('.ajax-alert').remove();

	                    if (that.attr('step-number') == 4) {

	                    	fbq('track', 'DVDentistRegistrationInitiate');
	                    	gtag('event', 'ClickNext', {
			                    'event_category': 'DentistRegistration',
			                    'event_label': 'DentistRegistrationComplete',
			                });

	                    	$('#register-form').submit();
	                    } else {

		                    var a = $('.sign-in-step.active');
		                    a.removeClass('active');
		                    a.next().addClass('active');

		                    gtag('event', 'ClickNext', {
								'event_category': 'DentistRegistration',
								'event_label': 'DentistRegistrationStep'+ that.attr('step-number'),
							});
	                    }

	                } else {

	                	$('.ajax-alert').remove();

	                	if (that.attr('step-number') == 4) {
		                    $('#step-4 .alert-after').after('<div class="alert alert-warning ajax-alert"></div>');
		                    for(var i in data.messages) {
		                        $('#'+i+'-error').html(data.messages[i]).show();
								$('input[name="'+i+'"]').closest('.form-group').addClass('has-error');

		                        $('#step-4 .ajax-alert').append(data.messages[i] + '<br/>');
		                        $('[name="'+i+'"]').addClass('has-error');
		                    }

	                	} else {

		                    for(var i in data.messages) {
		                        $('[name="'+i+'"]').addClass('has-error');
		                        $('[name="'+i+'"]').closest('.form-group').addClass('has-error');
		                        $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>');

		                        if ($('[name="'+i+'"]').closest('.modern-radios').length) {
		                            $('[name="'+i+'"]').closest('.modern-radios').addClass('has-error');
		                        }

		                        if ($('[name="'+i+'"]').closest('.agree-label').length) {
		                            $('[name="'+i+'"]').closest('.agree-label').addClass('has-error');
		                        }                        
		                    }
	                	}
	                    grecaptcha.reset();
	                }
	                ajax_is_running = false;
	            }, 
	            "json"
	        );
	    }

    } );

	// $('#go-to-2').click( function(e) {

	// 	$(this).blur();

	// 	e.preventDefault();

	// 	$('#register-form').find('.error-message').hide();
	// 	$('#register-form').find('.has-error').removeClass('has-error');

 //        $.post( 
 //            $(this).attr('data-validator'), 
 //            $('#register-form').serialize() , 
 //            function( data ) {
 //                if(data.success) {
 //                	$('#step-1').hide();
 //                	$('#step-2').show();
 //                	$(window).scrollTop(0);

	// 				var request = {
	// 					type: ['dentist'],
	// 					query: $('#dentist-name').val()
	// 				};

	// 				fbq('track', 'DVDentistRegistrationInitiate');
	// 				gtag('event', 'ClickSubmit', {
	// 					'event_category': 'DentistRegistration',
	// 					'event_label': 'DentistRegistrationInitiate',
	// 				});

 //                } else {
	// 				for(var i in data.messages) {
	// 					$('#'+i+'-error').html(data.messages[i]).show();
	// 					$('input[name="'+i+'"]').closest('.form-group').addClass('has-error');
	// 				}

	//                 $('html, body').animate({
	//                 	scrollTop: $('#register-form').offset().top - 60
	//                 }, 500);
	//                 grecaptcha.reset();
 //                }
 //                ajax_is_running = false;
 //            }, 
 //            "json"
 //        );

	// } );

	$('#register-form .back').click( function() {
        var prev_step = $(this).closest('.sign-in-step');
        prev_step.removeClass('active');
        prev_step.prev().addClass('active');
        if ($('#dentist-address').length && $('#dentist-address').val()) {
            $('#dentist-address').blur();
        }
    });

	// $('#register-form').submit( function(e) {
	// 	e.preventDefault();

	// 	$('#register-form').find('.error-message').hide();
	// 	$('#register-form').find('.has-error').removeClass('has-error');

	// 	if(ajax_is_running) {
	// 		return;
	// 	}
	// 	ajax_is_running = true;

	// 	var that = $(this);

 //        $.post( 
 //            $(this).attr('action'), 
 //            $(this).serialize() , 
 //            (function( data ) {
 //                if(data.success) {

 //               		fbq('track', 'DentistCompleteRegistration');
 //                    gtag('event', 'ClickNext', {
 //                        'event_category': 'DentistRegistration',
 //                        'event_label': 'DentistRegistrationComplete',
 //                    });
                    
 //               		window.location.href = data.url;
 //                } else if (data.popup) {
 //                	$('#'+data.popup).addClass('active');
 //                } else {

 //                	$('.ajax-alert').remove();
 //                    $('#step-4 .alert-after').after('<div class="alert alert-warning ajax-alert"></div>');
 //                    for(var i in data.messages) {
 //                        $('#'+i+'-error').html(data.messages[i]).show();
	// 					$('input[name="'+i+'"]').closest('.form-group').addClass('has-error');

 //                        $('#step-4 .ajax-alert').append(data.messages[i] + '<br/>');
 //                        $('[name="'+i+'"]').addClass('has-error');
 //                    }

	//                 grecaptcha.reset();
 //                }
 //                ajax_is_running = false;
 //            }).bind(that), "json"
 //        );			

 //        return false;

 //    } );

    $('#register-form input').on('focus', function(e){
    	console.log($(this).closest('.form-group'));
	    $(this).closest('.form-group').removeClass('has-error');
	});

	$('.verification-form').submit( function(e) {
        e.preventDefault();

        $('.alert').hide();
        $(this).find('.has-error').removeClass('has-error');

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;


        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: {
                short_description: $('[name="short_description"]').val(),
                _token: $(this).find('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: (function(ret) {
                if (ret.success) {
                    $(this).hide();
                    $(this).closest('.right-content').addClass('active');
                } else {
                    $('.alert-warning').html('').show();
                    for(var i in ret.messages) {
                        $('.alert-warning').append(ret.messages[i] + '<br/>');
                        $('[name="'+i+'"]').addClass('has-error');
                    }
                }
                ajax_is_running = false;
            }).bind(this)
        });

    } );

	//Gallery
    $('#add-avatar').change( function() {

		if (typeof($(this)[0].files[0]) != 'undefined' ) {

	        if(ajax_is_running) {
	            return;
	        }
	        ajax_is_running = true;

	        $('.add-photo').addClass('loading');

	        var file = $(this)[0].files[0];
	        var upload = new Upload(file, upload_url, function(data) {
	            $('.add-photo').removeClass('loading');
		        $('.add-photo').css('background-image', "url('"+data.thumb+"')");

	            if( $('body').hasClass('page-profile') ) {
	            	console.log('waaat');
	            	$('img.header-avatar').attr('src', data.thumb);
	            	$('.add-photo').find('.photo-cta').hide();

	            	setTimeout( (function() {
	            		$('.add-photo').find('.photo-cta').show();
	            		$('.add-photo').css('background-image', "radial-gradient( rgba(255,255,255,1), rgba(255,255,255,1), rgba(255,255,255,0) ), url('"+this+"')");
	            	}).bind(data.thumb), 3000 );
	            } else {
	            	
		            if($('.add-photo').find('.photo-cta').length) {
		            	$('.add-photo').find('.photo-cta').remove();
		            }
		            $('#photo-name').val( data.name );
	            }

	            ajax_is_running = false;
	        });

	        upload.doUpload();
	    }

    } );

	$('#language-selector').change( function() {
		var arr = window.location.pathname.split('/');
		arr[1] = $(this).val();
		window.location.href = window.location.origin + arr.join('/');
	});


	if( $('#header_questions').length ) {
		setInterval( function() {
			$.ajax( {
				url: '/question-count',
				type: 'GET',
				dataType: 'json',
				success: function( data ) {
					// console.log(data);
					var my_amount = parseInt($('#header-balance').html()) * data.dcn_price_full

					$('#header_questions').html(data.question_count);
					$('#header-rate').html(data.dcn_price);
					$('#header-change').html('('+data.dcn_change+'%)').css('color', parseFloat(data.dcn_change)>0 ? '#4caf50' : '#e91e63' );
					$('#header-usd').html( '$' + parseFloat(my_amount).toFixed(2) );
				}
			});

	    }, 10000 );
	}


    //Selects
    $(".select-me").on("click focus", function () {
        $(this).select();
    });

    $('.native-share').click( function() {
        navigator.share({
            title: $(this).attr('data-title'),
            url: $(this).attr('data-url')
        }).then( function() {
        	console.log('Successful share')
        })
        .catch(function(error) {
        	console.log('Error sharing:', error)
        });

    });

    function share_buttons() {
		$('.share.google, .share.fb, .share.twt, .share.reddit, .share.messenger').click( function() {
			var post_url = $(this).attr('data-url');
			var post_title = $(this).attr('data-title');
			if ($(this).hasClass('fb')) {
				var url = 'https://www.facebook.com/dialog/share?app_id=1906201509652855&display=popup&href=' + escape(post_url);
			} else if ($(this).hasClass('twt')) {
				var url = 'https://twitter.com/share?url=' + escape(post_url) + '&text=' + escape(post_title);
			} else if ($(this).hasClass('google')) {
				var url = 'https://plus.google.com/share?url=' + escape(post_url);
			}  else if ($(this).hasClass('messenger')) {
				var url = 'fb-messenger://share?link=' + encodeURIComponent(post_url);
			}
			window.open( url , 'ShareWindow', 'height=450, width=550, top=' + (jQuery(window).height() / 2 - 275) + ', left=' + (jQuery(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
		});
	}
	share_buttons();

	$('#provide-form').submit( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}

		ajax_is_running = true;
        $('#provide-form .alert').hide();
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	$('#provide-succcess').show().css({
                		'margin-top': 0,
                		'margin-bottom': 22
                	});
                	$('#provide-succcess').html(data.message);
                	$('#provide-form').hide();
                	$('#invite-wrapper').show();
                } else {
                	$('#provide-invalid').show();
                	$('#provide-invalid').html(data.message);
                }
                ajax_is_running = false;
            }, "json"
        );

	});

	$('#set-email-form').submit( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;
		$('#set-email-error').hide();

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	$('#set-email-form').hide();
                	$('#email-refresh').show();
                	$('#verify-email-span').html( $('#verify-email').val() );
                } else {
                	$('#set-email-error').show().html(data.message);
                }
                ajax_is_running = false;
            }, "json"
        );

	} );

	$('#stop-submit').click( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;
        $('#stop-error').hide();
        
        $.post( 
            '/wait', 
            {
            	email: $('#stop-email').val(),
            	name: $('#stop-name').val(),
            }, 
            function( data ) {
            	console.log(data);
                if(data.success) {
                	$('#stop-submit').after( $('<div style="margin-top: 20px; margin-bottom: 0px;" class="alert alert-success">Thank you. We\'ll let you know as soon as registrations are open again.</div>') );
                	$('#stop-submit').remove();
                } else {
                	if( !$('#stop-error').length ) {
                		$('#stop-submit').after( $('<div style="margin-top: 20px; margin-bottom: 0px;" class="alert alert-warning" id="stop-error"></div>') );
                	}
                	$('#stop-error').html('').show();
                	for(var i in data.messages) {
                		$('#stop-error').append(data.messages[i] + '<br/>');
                	}
                }
                ajax_is_running = false;
            }, "json"
        );

	} );



	$('.popup.close-on-shield').click( function(e) {
		if($(e.target).hasClass('popup')) {
			$(this).removeClass('active');
			$('body').removeClass('popup-visible');

			if ($(this).hasClass('daily-poll') && $('#calendar').length) {
				calendarEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
				calendarListEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
			}
		}
	} );



	$('.popup .closer').click( function() {
		if($(this).hasClass('inactive')) {
			return;
		}
		if($(this).closest('.popup').hasClass('ban')) {
			window.location.reload();
		}
		$(this).closest('.popup').removeClass('active');
		$('body').removeClass('popup-visible');

		if ($(this).closest('.popup').hasClass('daily-poll') && $('#calendar').length) {
			calendarEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
			calendarListEvents($('#calendar').attr('month'), $('#calendar').attr('year'));
		}
	} );


	$('.popcircle .closer').click( function() {
		$(this).closest('.popcircle').removeClass('active');
	} );

	simpleCountDown = function() {
		clearInterval(simpleCountDownTO);

		simpleCountDownTO = setInterval( function() {
			var secs = parseInt($('.simple-countdown span').text());
			--secs;
			if(secs) {
				$('.simple-countdown span').html(secs);
			} else {
				$('.simple-countdown').html( $('.simple-countdown').attr('alt-text') ).removeClass('inactive');
				clearInterval(simpleCountDownTO);
			}
		}, 1000 );
	}

	hoursCountdown = function() {
		clearInterval(hoursCountdownTO);
		
		hoursCountdownTO = setInterval( function() {
			var arr = $('.hours-countdown span').text().split(':');
			var hours = parseInt(arr[0]);
			var mins = parseInt(arr[1]);
			var secs = parseInt(arr[2]);
			if(secs==0) {
				secs=59;
				if(mins==0) {
					mins = 23;
					if(hours==0) {
						clearInterval(hoursCountdownTO);
						window.location.reload();
						return;
					} else {
						--hours;				
					}
				} else {
					--mins;				
				}
			} else {
				--secs;			
			}

			secs = (secs+'').padStart(2, '0');
			mins = (mins+'').padStart(2, '0');
			$('.hours-countdown span').html(hours+':'+mins+':'+secs)
		}, 1000 );
	}


	$('#bad-ip-appeal').click( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}

		ajax_is_running = true;

		var that = $(this);

        $.ajax( {
			url: that.attr('href'),
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				window.location.reload();
				ajax_is_running = false;
			},
			error: function(data) {
				console.log('error');
                ajax_is_running = false;
            }
		});

	} );

	$('.fb-register').click( function(e) {
		e.preventDefault();

		if ($('#read-privacy').prop("checked")) {
			window.location.href = $(this).attr('href');
		} else {
			$('#read-privacy').closest('.form-group').addClass('has-error');
		}
		
	});

	if( $(window).width()<768 ) {
		$('.header-a').click( function(e) {
			
			// if( $('.menu-list a.active').length ) {
			// 	$('.menu-list a.active').trigger('click');
			// }

			e.preventDefault();
			$(this).closest('.header-right').find('.expander-wrapper').addClass('active');
			return false;
		} );
	}

    if(getUrlParameter('suspended-popup')) {
		$('#suspended-popup').addClass('active');
	}

	showPopup = function(id, e) {
		if(id=='poll-popup') {
			$('.poll-bubble').hide();

			if ($('#poll-popup').length && $('#poll-popup .poll-answers').hasClass('shuffle-answers')) {
		        var divs = $('#poll-popup .poll-answers').children().not(".dont-shuffle");

		        while (divs.length) {
		            $('#poll-popup .poll-answers').prepend(divs.splice(Math.floor(Math.random() * divs.length), 1)[0]);
		        }
		    }
		}

		$('.popup').removeClass('active');
		$('#'+id+'.popup').addClass('active');
		handlePopups();
		if ($('.popup.active').length) {
			$('body').addClass('popup-visible');
		}
	}

	closePopup = function() {
		$('.popup').removeClass('active');
	}

	handlePopups = function(id) {
		var dataPopupClick = function(e) {
			showPopup( $(this).attr('data-popup'), e );
		}
		$('[data-popup]').off('click', dataPopupClick).click( dataPopupClick );

	}
	handlePopups();

    if(getUrlParameter('popup')) {
		showPopup( getUrlParameter('popup') );
	}

	tooltipsFunction = function() {

	    var handleTooltip = function(e) {

	    	if( $(this).closest('.no-mobile-tooltips').length ) {
	    		var that = $(this).closest('.no-mobile-tooltips');
	    	} else {
	    		var that = $(this).closest('.tooltip-text');
	    	}

	    	console.log( $(this).closest('.tooltip-text').attr('text') );

	        $('.tooltip-window').text( $(this).closest('.tooltip-text').attr('text') );
	        $('.tooltip-window').css('display', 'block');

	    	var y = that.offset().top + that.outerHeight() + 10;
	    	var x = that.offset().left + that.outerWidth() / 2 - $('.tooltip-window').outerWidth() / 2 ;

	        $('.tooltip-window').css('left', x );
	        $('.tooltip-window').css('top', y );

	        // if ( window.innerWidth < 768 && window.innerWidth - $('.tooltip-window').outerWidth() - 20 < e.pageX ) {
	        //     $('.tooltip-window').css('left', window.innerWidth - $('.tooltip-window').outerWidth() - 20 );
	        // }

	        e.stopPropagation();

	        if ($(this).closest('.tooltip-text').hasClass('info-cookie')) {
	        	$('.tooltip-window').addClass('dark-tooltip');
	        } else {
	        	$('.tooltip-window').removeClass('dark-tooltip');
	        }

	    }

	    if (window.innerWidth < 768) {
	    	$('.no-mobile-tooltips').click( function() {
	    		$('.tooltip-window').hide();
	    	});
	    }
	    

	    if($('.tooltip-text').length) {

	        $('.tooltip-text').on('mouseover mousemove', function(e) {
	            if (window.innerWidth > 768) {
	                handleTooltip.bind(this)(e);
	            }
	        });

	        $('.tooltip-text').on('click', function(e) {
	            if (window.innerWidth < 768 && !$(this).hasClass('no-mobile-tooltips')) {
	                handleTooltip.bind(this)(e);
	            }
	        });

	        $('.tooltip-text').on('mouseout', function(e) {

	            $('.tooltip-window').hide();
	        });
	    }

	    $('.answer-mobile-tooltip').click( function(e) {
	    	e.stopPropagation();
	    	e.preventDefault();

	    	if (window.innerWidth < 768 && !$(this).hasClass('no-mobile-tooltips')) {
	            handleTooltip.bind(this)(e);
	        }
	    });
	}

	tooltipsFunction();

    var handleCoinTooltip = function(e) {
    	var that = $(this);

        $('.doublecoin-tooltip').css('display', 'block');

    	var y = that.offset().top + that.outerHeight() - 100;
    	var x = that.offset().left;

        $('.doublecoin-tooltip').css('left', x );
        $('.doublecoin-tooltip').css('top', y );

        if ( window.innerWidth - $('.doublecoin-tooltip').innerWidth() - 20 < e.pageX ) {

    		$('.doublecoin-tooltip span').css('left', $('.doublecoin-tooltip').offset().left - $(window).innerWidth() + $('.doublecoin-tooltip').innerWidth() + 30 );
        	$('.doublecoin-tooltip').css('left', $(window).innerWidth() - $('.doublecoin-tooltip').innerWidth() - 20 );
    	
        } else {        	
        	$('.doublecoin-tooltip span').css('left', '20px' );
        }

        e.stopPropagation();

    }

    if($('.doublecoin').length) {

        $('.doublecoin').on('mouseover mousemove', function(e) {
            if (window.innerWidth > 768) {
                handleCoinTooltip.bind(this)(e);
            }
        });

        $('.doublecoin').on('click', function(e) {
        	e.preventDefault();
            if (window.innerWidth < 768) {
                handleCoinTooltip.bind(this)(e);
            }
        });

        $('.doublecoin').on('mouseout', function(e) {
            $('.doublecoin-tooltip').hide();
        });
    }


    $('.close-explander').click( function() {
    	$(this).closest('.expander-wrapper').removeClass('active');
    });
    

    if ($('.strength-flickity').length) {
	    $('.strength-flickity').flickity({
	    	wrapAround: true,
			draggable: true,
			pageDots: false,
		});
	}

	$('.strength-button').click( function() {
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			$('.strength-parent').removeClass('active');
		} else {
			$(this).addClass('active');
			$('.strength-parent').addClass('active');
		}
	});
	
	$('[event_category]').click( function() {
		gtag('event', $(this).attr('event_action'), {
            'event_category': $(this).attr('event_category'),
            'event_label': $(this).attr('event_label'),
        });
	});

	if ($('body').hasClass('page-profile-redirect')) {

		var cnt = $('img.hide').length;
		var loaded = 0;
		$('img.hide').on('load error', function() {
	        loaded++;
			if(loaded==cnt) {
				$('.loader').fadeOut();
	        	$('.loader-mask').delay(350).fadeOut('slow');

				window.location.href = 'https://account.dentacoin.com/dentavox?platform=dentavox';
			}
	    });
	}

	//Daily Polls 

	pollStats = function(chart) {
		
        var max = 0;
        var total = 0;
        for(var i in chart) {
        	total+=parseInt(chart[i]);
            if(chart[i] > max) {
                max = parseInt(chart[i]);
            }
        }

        var container = $('#chart-poll');
        container.html('');

        var u = 0;
        for(var i in chart) {
        	u++;
            $(container).append('<div class="custombar-parent" sta="'+u+'" votes="'+(parseInt(chart[i])/total*100).toFixed(1)+'"><div class="group-heading">'+i+'</div></div>');
            var pl = 90*parseInt(chart[i])/max + 1;
            $(container).find('.custombar-parent[sta="'+u+'"]').append('<div class="custombar"><span stat-width="'+parseInt(pl)+'%"></span> '+(parseInt(chart[i])/total*100).toFixed(1)+'%</div>');
        }

        var list = container.children();

        list.sort(function(a, b) {
            if( parseInt($(a).attr('votes')) > parseInt($(b).attr('votes')) ) {
                return -1;
            } else if( parseInt($(a).attr('votes')) < parseInt($(b).attr('votes')) ) {
                return 1;
            }
        });

        list.each(function() {
            container.append(this);
        });
    }

    pollStatsAnimate = function() {
    	$('.custombar').each( function() {
    		$(this).find('span').css('width', $(this).find('span').attr('stat-width') );
    	});
    }

	showStats = function(poll_id) {
		var p_id = poll_id;

		$.ajax({
            type: "POST",
            url: window.location.origin+'/en/get-poll-stats/'+p_id,
            data: {
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(ret) {
                if(ret.success) {
                	
                	pollStats(ret.chart);
                	$('#poll-popup').find('.poll-stats-wrapper h3').html($('#poll-popup').find('.poll-stats-wrapper h3').attr('alternative-title'));
                    $('#poll-popup').find('.poll-question').html(ret.title);
                    $('#poll-popup').find('.poll-stats-wrapper p').remove();
                	$('#poll-popup').find('.content').hide();
                	$('#poll-popup').find('.poll-stats-wrapper').show();
                    pollStatsAnimate();
                    $('#poll-popup').find('.poll-stats-wrapper p').remove();
                    if (ret.closed) {
                        $('<p>This poll is closed.</p>').insertAfter('.poll-stats-wrapper h3');

                        if (!ret.has_user) {
                            $('.get-reward-buttons').show();
                            $('.sign').hide();
                        }
                    }
                    $('.next-poll').removeClass('taken-all');
                	if (!ret.next_poll) {
                		$('.next-poll').addClass('taken-all');
                	} else {
                		$('.next-poll').attr('poll-id', ret.next_poll);
                	}
                	if ($('.next-stat').length) {
                		$('.next-stat').attr('stat-id', ret.next_stat);
                	}
                	$('#poll-popup').addClass('active');
                } else {
    				console.log('error');
                }
            },
            error: function(ret) {
                console.log('error');
            }
        });
	}


	pollsFunction = function() {
		

		$('.answer').change( function(e) {
			$(this).closest('form').submit();
		});


		$('.poll-form').submit( function(e) {
			e.preventDefault();

			if(ajax_is_running) {
	            return;
	        }

	        ajax_is_running = true;

	        var that = $(this);

	        $.post( 
	            $(this).attr('action'), 
	            $(this).serialize() , 
	            function( data ) {
	                if(data.success) {
	                	pollStats(data.chart);
	                	$('#poll-popup').find('.content').hide();
	                	that.closest('#poll-popup').find('.poll-stats-wrapper').show();
	                	pollStatsAnimate();
	                	$('.next-poll').removeClass('taken-all');
	                	if (!data.next_poll) {
	                		$('.next-poll').addClass('taken-all');
	                	} else {
	                		$('.next-poll').attr('poll-id', data.next_poll);
	                	}

						$('#poll-popup').find('.poll-stats-wrapper p').remove();
	                	$('<p>'+data.respondents+'</p>').insertAfter('.poll-stats-wrapper h3');
	                	if (!data.has_user) {
                            $('.get-reward-buttons').hide();
                            $('.sign').show();
                        }
	                } else {
	                	if (data.closed_poll) {
	                		$('#poll-popup').find('.content').hide();
	                		that.closest('.popup-inner').find('.poll-closed-wrapper').show();
	                		that.closest('.popup-inner').find('.poll-closed-wrapper .see-stats').attr('poll-id', data.closed_poll);
	                	} else if (data.chart) {
	                		pollStats(data.chart);
		                	$('#poll-popup').find('.content').hide();
		                	that.closest('#poll-popup').find('.poll-stats-wrapper').show();
		                	pollStatsAnimate();
		                	$('#poll-popup').find('.poll-stats-wrapper p').remove();
		                	$('<p>'+data.respondents+'</p>').insertAfter('.poll-stats-wrapper h3');
		                	if (!data.has_user) {
	                            $('.get-reward-buttons').hide();
	                            $('.sign').show();
	                        }
	                	} else {
	                		console.log('false'); 
	                	}
	                                    
	                }
	                ajax_is_running = false;

	                var q = $('#poll-popup .poll-form-wrapper .poll-question').html();

	                fbq('track', 'DV_DailyPoll');
	                gtag('event', 'Answer', {
		                'event_category': 'DailyPollAnswer',
		                'event_label': q+'-DailyPollQuestion',
		            });

	            }, "json"
	        );
		});

		$('.next-poll').click( function() {
			if ($(this).hasClass('taken-all')) {
        		$('#poll-popup .content').hide();
				$('#poll-popup .poll-taken-all-wrapper').show();
				
			} else {

				var poll_id = $(this).attr('poll-id');

				$.ajax({
		            type: "POST",
		            url: window.location.origin+'/en/get-poll-content/'+poll_id,
		            data: {
		                _token: $('input[name="_token"]').val(),
		            },
		            dataType: 'json',
		            success: function(ret) {
		                if(ret.success) {

		                	$('#poll-popup .poll-answers').html('');
		                	$('#poll-popup').find('.poll-question').html(ret.title);
		                	$('#poll-popup').find('form').attr('action', ret.url);
		                	for (var i in ret.answers ) {
		                		$('#poll-popup .poll-answers').append('<label class="poll-answer" for="ans-'+(parseInt(i) + 1)+'"><input type="radio" name="answer" class="answer" value="'+(parseInt(i) + 1)+'" id="ans-'+(parseInt(i) + 1)+'">'+ret.answers[i]+'</label>');
		                	}

		                	$('#poll-popup').find('.content').hide();
		                	$('#poll-popup').find('.poll-form-wrapper').show();
		                	tooltipsFunction();
		                	pollStats();
		                	pollsFunction();
		                } else {
		    				if (ret.all_polls_taken) {
		    					console.log('taken');
		    				}
		                }
		            },
		            error: function(ret) {
		                console.log('error');
		            }
		        });
			}
		});

		$('.answer-poll').click( function() {
			var q = $(this).attr('q');

        	gtag('event', 'Click', {
                'event_category': 'DailyPollPopup',
                'event_label': q+'-DailyPollQuestion',
            });
		});

		$('.next-stat').click( function() {
			showStats($(this).attr('stat-id'));
		});

		$('.see-stats').click( function() {
			showStats($(this).attr('poll-id'));
		});
	}
	pollsFunction();

	$('.close-bubble').click( function() {
		if (window.innerWidth <= 768 && Cookies.get('functionality_cookies')) {
			Cookies.set('small-poll-bubble', true, { expires: 1, secure: true });
		}
		$('.poll-bubble').hide();
	});

	if (window.innerWidth <= 768) {
		if(Cookies.get('small-poll-bubble')) {
			$('.poll-bubble').addClass('small-bubble');
		}

		$('.small-bubble').click( function() {
			$(this).removeClass('small-bubble');
			Cookies.remove('small-poll-bubble');
		});
	}

	if ($('.poll-bubble').length && window.innerWidth >= 768) {
		$(window).on("scroll", function() {
			var scrollHeight = $(document).height();
			var scrollPosition = $(window).height() + $(window).scrollTop();
			if ((scrollHeight - scrollPosition) / scrollHeight === 0) {
			    $('.poll-bubble').fadeOut();
			} else {
				$('.poll-bubble').fadeIn();
			}
		});
	}

	

    const fb_config = {
        //app_id: '299398824049604',
        app_id: '1906201509652855',
        platform: 'fb'
    };

    //application init
    window.fbAsyncInit = function () {
        FB.init({
            appId: fb_config.app_id,
            cookie: true,
            xfbml: true,
            version: 'v2.8'
        });
        FB.AppEvents.logPageView();
    };

    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = '//connect.facebook.net/bg_BG/sdk.js';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    $('.fb-login-button-new').click( function(rerequest){

    	if (!Cookies.get('strictly_necessary_policy')) {
    		return;
    	}

        var that = $(this);

        FB.login(function (response) {

            if(response.authResponse && response.status == "connected") {
                $('#new-login-form input[name="access-token"]').val(response.authResponse.accessToken);
                $('#new-login-form').submit();
            }
        });
    });

    


	$('.country-dropdown').change( function() {

		if ($(this).attr('real-country') != '') {
			if ($(this).val() != $(this).attr('real-country')) {
				$(this).parent().parent().find('.ip-country').show();
			} else {
				$(this).parent().parent().find('.ip-country').hide();
			}
		}
	});

	if ($('.video-stats').length) {
		if (!$('.video-stats').hasClass('inited-video')) {
			$('.video-stats')[0].play();
		}
    	
	}

    $('#request-survey-form').submit( function(e) {
        e.preventDefault();

        if (!$(this).find('.button').hasClass('disabled')) {

	        $(this).find('.ajax-alert').remove();
	        $(this).find('.alert').hide();
	        $(this).find('.has-error').removeClass('has-error');

	        if(ajax_is_running) {
	            return;
	        }
	        ajax_is_running = true;

	        var that = $(this);

	        // var countries = [];
	        // for (var i in $('[name="target-countries"]').select2('data')) {
	        // 	countries.push($('[name="target-countries"]').select2('data')[i]['text']);
	        // }

	        // $('[name="target-countries"]').val(countries.toString());
	        // console.log(countries.toString(), $('[name="target-countries"]').val());

	        //console.log($('[name="target-countries"]').val());
	        $.post( 
	            $(this).attr('action'), 
	            $(this).serialize() , 
	            (function( data ) {
	                if(data.success) {
	                	that[0].reset();
	                	if ($('.select2').length) {
	                		$('.select2').val(null).trigger('change').select2();
	                	}
	                	
	                   	$(this).find('.alert-success').show();
	                } else {
	                    for(var i in data.messages) {
	                        $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>');

	                        $('[name="'+i+'"]').addClass('has-error');

	                        if ($('[name="'+i+'"]').closest('.modern-radios').length) {
	                            $('[name="'+i+'"]').closest('.modern-radios').addClass('has-error');
	                        }
	                    }

	                    $('html, body').animate({
			                scrollTop: $('.ajax-alert:visible').first().offset().top - $('header').height() - 150
			            }, 500);
	                }
	                ajax_is_running = false;
	            }).bind(that), "json"
	        );          


	        return false;
	    }
	} );


	$('.recommend-radio').change( function(e) {
		$('.recommend-radio').not(this).attr('disabled', 'disabled');
		//$(this).closest('.recommend-icons').find('label').removeClass('active');
		$(this).closest('label').addClass('active');
		$('#recommend-form').submit();
	});

	$('#recommend-button').click( function(e) {
		e.preventDefault();

		if ($('#recommend-description').val()) {
			$('#recommend-form').submit();
		} else {
			$('#recommend-description').addClass('has-error');
			$('#recommend-form').find('.alert-warning').show();
		}
	});

    $('#recommend-form').submit( function(e) {
        e.preventDefault();

        $(this).find('.ajax-alert').remove();
        $(this).find('.alert').hide();
        $(this).find('.has-error').removeClass('has-error');

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);
        
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            (function( data ) {
                if(data.success) {

                	if (data.recommend) {
                		$(this).next().show();
                		$(this).hide();
                		$('#myVideoRecommend')[0].play();
                	} else {
                		if (data.description) {
                			$(this).find('.hide-on-success').hide();	                	
	                   		$(this).find('.alert-success').show();
                		} else {
                			$(this).find('.hide-happy').show();
                		}	                	
                	}
                }
                ajax_is_running = false;
            }).bind(that), "json"
        );  
	} );

	if( Cookies.get('first-login-recommendation') ) {
		showPopup('recommend-popup');
	}

    if ($('#christmasBanner').length) {
    	$('#christmasBanner')[0].removeAttribute("controls");

    	if ($(window).outerWidth() <= 768) {
    		$('#christmasBanner').attr('src', $('#christmasBanner').attr('mobile-src'));
    	}
    }
    
    if ($('#myVideo').length) {
    	$('#myVideo')[0].removeAttribute("controls");
    }

    $(".close-banner").click( function(e) {
		e.preventDefault();

		$('.christmas-banner').hide();
	});

	if ($('.daily-limit-reached').length) {
		hoursCountdown();
	}

});




/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2006, 2014 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD (Register as an anonymous module)
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// Node/CommonJS
		module.exports = factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			// If we can't parse the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write

		if (arguments.length > 1 && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setMilliseconds(t.getMilliseconds() + days * 864e+5);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {},
			// To prevent the for loop in the first place assign an empty array
			// in case there are no cookies at all. Also prevents odd result when
			// calling $.cookie().
			cookies = document.cookie ? document.cookie.split('; ') : [],
			i = 0,
			l = cookies.length;

		for (; i < l; i++) {
			var parts = cookies[i].split('='),
				name = decode(parts.shift()),
				cookie = parts.join('=');

			if (key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		// Must not alter options, thus extending a fresh object...
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};

}));


var Upload = function (file, url, success) {
    this.file = file;
    this.url = url;
    this.success = success
};

Upload.prototype.getType = function() {
    return this.file.type;
};
Upload.prototype.getSize = function() {
    return this.file.size;
};
Upload.prototype.getName = function() {
	return this.file.name;
};
Upload.prototype.doUpload = function () {
    $('#photo-upload-error').hide();
    var that = this;
    var formData = new FormData();

    // add assoc key values, this will be posts values

    formData.append("image", this.file, this.getName());
    formData.append("upload_file", true);

    $.ajax({
        type: "POST",
        url: this.url,
        xhr: function () {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
                myXhr.upload.addEventListener('progress', that.progressHandling, false);
            }
            return myXhr;
        },
        success: this.success,
        error: function (error) {
    		console.log('Error', error);
            $('.add-photo').removeClass('loading');
            ajax_is_running = false;
            $('#photo-upload-error').show();
        },
        async: true,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        timeout: 60000
    });
};

Upload.prototype.progressHandling = function (event) {
    var percent = 0;
    var position = event.loaded || event.position;
    var total = event.total;
    var progress_bar_id = "#progress-wrp";
    if (event.lengthComputable) {
        percent = Math.ceil(position / total * 100);
    }
    console.log(percent);
};

var getUrlParameter = function(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};

/*!
  * Stickyfill – `position: sticky` polyfill
  * v. 2.0.3 | https://github.com/wilddeer/stickyfill
  * MIT License
  */
!function(a,b){"use strict";function c(a,b){if(!(a instanceof b))throw new TypeError("Cannot call a class as a function")}function d(a,b){for(var c in b)b.hasOwnProperty(c)&&(a[c]=b[c])}function e(a){return parseFloat(a)||0}function f(a){for(var b=0;a;)b+=a.offsetTop,a=a.offsetParent;return b}function g(){function c(){a.pageXOffset!=k.left?(k.top=a.pageYOffset,k.left=a.pageXOffset,n.refreshAll()):a.pageYOffset!=k.top&&(k.top=a.pageYOffset,k.left=a.pageXOffset,l.forEach(function(a){return a._recalcPosition()}))}function d(){f=setInterval(function(){l.forEach(function(a){return a._fastCheck()})},500)}function e(){clearInterval(f)}c(),a.addEventListener("scroll",c),a.addEventListener("resize",n.refreshAll),a.addEventListener("orientationchange",n.refreshAll);var f=void 0,g=void 0,h=void 0;"hidden"in b?(g="hidden",h="visibilitychange"):"webkitHidden"in b&&(g="webkitHidden",h="webkitvisibilitychange"),h?(b[g]||d(),b.addEventListener(h,function(){b[g]?e():d()})):d()}var h=function(){function a(a,b){for(var c=0;c<b.length;c++){var d=b[c];d.enumerable=d.enumerable||!1,d.configurable=!0,"value"in d&&(d.writable=!0),Object.defineProperty(a,d.key,d)}}return function(b,c,d){return c&&a(b.prototype,c),d&&a(b,d),b}}(),i=!1;a.getComputedStyle?!function(){var a=b.createElement("div");["","-webkit-","-moz-","-ms-"].some(function(b){try{a.style.position=b+"sticky"}catch(a){}return""!=a.style.position})&&(i=!0)}():i=!0;var j="undefined"!=typeof ShadowRoot,k={top:null,left:null},l=[],m=function(){function g(a){if(c(this,g),!(a instanceof HTMLElement))throw new Error("First argument must be HTMLElement");if(l.some(function(b){return b._node===a}))throw new Error("Stickyfill is already applied to this node");this._node=a,this._stickyMode=null,this._active=!1,l.push(this),this.refresh()}return h(g,[{key:"refresh",value:function(){if(!i&&!this._removed){this._active&&this._deactivate();var c=this._node,g=getComputedStyle(c),h={top:g.top,display:g.display,marginTop:g.marginTop,marginBottom:g.marginBottom,marginLeft:g.marginLeft,marginRight:g.marginRight,cssFloat:g.cssFloat};if(!isNaN(parseFloat(h.top))&&"table-cell"!=h.display&&"none"!=h.display){this._active=!0;var k=c.parentNode,l=j&&k instanceof ShadowRoot?k.host:k,m=c.getBoundingClientRect(),n=l.getBoundingClientRect(),o=getComputedStyle(l);this._parent={node:l,styles:{position:l.style.position},offsetHeight:l.offsetHeight},this._offsetToWindow={left:m.left,right:b.documentElement.clientWidth-m.right},this._offsetToParent={top:m.top-n.top-e(o.borderTopWidth),left:m.left-n.left-e(o.borderLeftWidth),right:-m.right+n.right-e(o.borderRightWidth)},this._styles={position:c.style.position,top:c.style.top,bottom:c.style.bottom,left:c.style.left,right:c.style.right,width:c.style.width,marginTop:c.style.marginTop,marginLeft:c.style.marginLeft,marginRight:c.style.marginRight};var p=e(h.top);this._limits={start:m.top+a.pageYOffset-p,end:n.top+a.pageYOffset+l.offsetHeight-e(o.borderBottomWidth)-c.offsetHeight-p-e(h.marginBottom)};var q=o.position;"absolute"!=q&&"relative"!=q&&(l.style.position="relative"),this._recalcPosition();var r=this._clone={};r.node=b.createElement("div"),d(r.node.style,{width:m.right-m.left+"px",height:m.bottom-m.top+"px",marginTop:h.marginTop,marginBottom:h.marginBottom,marginLeft:h.marginLeft,marginRight:h.marginRight,cssFloat:h.cssFloat,padding:0,border:0,borderSpacing:0,fontSize:"1em",position:"static"}),k.insertBefore(r.node,c),r.docOffsetTop=f(r.node)}}}},{key:"_recalcPosition",value:function(){if(this._active&&!this._removed){var a=k.top<=this._limits.start?"start":k.top>=this._limits.end?"end":"middle";if(this._stickyMode!=a){switch(a){case"start":d(this._node.style,{position:"absolute",left:this._offsetToParent.left+"px",right:this._offsetToParent.right+"px",top:this._offsetToParent.top+"px",bottom:"auto",width:"auto",marginLeft:0,marginRight:0,marginTop:0});break;case"middle":d(this._node.style,{position:"fixed",left:this._offsetToWindow.left+"px",right:this._offsetToWindow.right+"px",top:this._styles.top,bottom:"auto",width:"auto",marginLeft:0,marginRight:0,marginTop:0});break;case"end":d(this._node.style,{position:"absolute",left:this._offsetToParent.left+"px",right:this._offsetToParent.right+"px",top:"auto",bottom:0,width:"auto",marginLeft:0,marginRight:0})}this._stickyMode=a}}}},{key:"_fastCheck",value:function(){this._active&&!this._removed&&(Math.abs(f(this._clone.node)-this._clone.docOffsetTop)>1||Math.abs(this._parent.node.offsetHeight-this._parent.offsetHeight)>1)&&this.refresh()}},{key:"_deactivate",value:function(){var a=this;this._active&&!this._removed&&(this._clone.node.parentNode.removeChild(this._clone.node),delete this._clone,d(this._node.style,this._styles),delete this._styles,l.some(function(b){return b!==a&&b._parent&&b._parent.node===a._parent.node})||d(this._parent.node.style,this._parent.styles),delete this._parent,this._stickyMode=null,this._active=!1,delete this._offsetToWindow,delete this._offsetToParent,delete this._limits)}},{key:"remove",value:function(){var a=this;this._deactivate(),l.some(function(b,c){if(b._node===a._node)return l.splice(c,1),!0}),this._removed=!0}}]),g}(),n={stickies:l,Sticky:m,addOne:function(a){if(!(a instanceof HTMLElement)){if(!a.length||!a[0])return;a=a[0]}for(var b=0;b<l.length;b++)if(l[b]._node===a)return l[b];return new m(a)},add:function(a){if(a instanceof HTMLElement&&(a=[a]),a.length){for(var b=[],c=function(c){var d=a[c];return d instanceof HTMLElement?l.some(function(a){if(a._node===d)return b.push(a),!0})?"continue":void b.push(new m(d)):(b.push(void 0),"continue")},d=0;d<a.length;d++){c(d)}return b}},refreshAll:function(){l.forEach(function(a){return a.refresh()})},removeOne:function(a){if(!(a instanceof HTMLElement)){if(!a.length||!a[0])return;a=a[0]}l.some(function(b){if(b._node===a)return b.remove(),!0})},remove:function(a){if(a instanceof HTMLElement&&(a=[a]),a.length)for(var b=function(b){var c=a[b];l.some(function(a){if(a._node===c)return a.remove(),!0})},c=0;c<a.length;c++)b(c)},removeAll:function(){for(;l.length;)l[0].remove()}};i||g(),"undefined"!=typeof module&&module.exports?module.exports=n:a.Stickyfill=n}(window,document);