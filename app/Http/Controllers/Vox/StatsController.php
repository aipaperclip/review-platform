<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use Validator;
use Response;
use Request;
use Route;
use Hash;
use Mail;
use DB;
use Carbon\Carbon;
use App\Models\Dcn;
use App\Models\Country;
use App\Models\User;
use App\Models\Vox;
use App\Models\VoxReward;
use App\Models\VoxAnswer;
use App\Models\VoxQuestion;
use App\Models\VoxCategory;


class StatsController extends FrontController
{
	public function home($locale=null) {

        $this->current_page = 'stats';

		$sorts = [
            // 'featured' => trans('vox.page.stats.sort-featured'),
            'all' => trans('vox.page.stats.sort-all'),
			'newest' => trans('vox.page.stats.sort-newest'),
			// 'popular' => trans('vox.page.stats.sort-popular'),
		];


		return $this->ShowVoxView('stats', array(
            'taken' => $this->user ? $this->user->filledVoxes() : [],
            'canonical' => getLangUrl('dental-survey-stats'),
			'voxes' => Vox::with('stats_main_question')->get(),
			'cats' => VoxCategory::with('voxes.vox')->get(),
			'sorts' => $sorts,

            'seo_title' => trans('vox.seo.stats-all.title'),
            'seo_description' => trans('vox.seo.stats-all.description'),
            'social_title' => trans('vox.social.stats-all.title'),
            'social_description' => trans('vox.social.stats-all.description'),

			'js' => [
				'stats.js'
			]
		));
	}

	public function stats($locale=null, $slug=null, $question_id=null) {
        
        $this->current_page = 'stats';

		$vox = Vox::whereTranslationLike('slug', $slug)->first();

        if(empty($this->user) {
            session([
                'vox-redirect-workaround' => str_replace( getLangUrl('/').App::getLocale().'/', '', $vox->getLink())
                
            ]);
        }

		if(empty($vox)) {
			return redirect( getLangUrl('/') );
		}

        if(!$vox->has_stats) {
            return redirect( getLangUrl('dental-survey-stats') );
        }

        if(!empty($this->user) && $this->user->isBanned('vox')) {
            return redirect(getLangUrl('profile/vox'));
        }

        if(Request::isMethod('post')) {
        	$dates = Request::input('timeframe');
            $answer_id = Request::input('answer_id');
        	$question_id = Request::input('question_id');
        	$question = VoxQuestion::find($question_id);
        	$scale = Request::input('scale');
        	$type = $question->used_for_stats;

            $results = $this->prepareQuery($question_id, $dates);
        	$total = $this->prepareQuery($question_id, $dates)->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;

    		$main_chart = [];
    		$second_chart = [];
    		$third_chart = [];
    		$relation_info = [];
    		$totalf = 0;
    		$totalm = 0;
    		$answers = json_decode($question->answers);
            foreach ($answers as $key => $value) {
                if(mb_strpos($value, '!')===0) {
                    $answers[$key] = mb_substr($value, 1);
                }
                $main_chart[$answers[$key]] = 0;
            }



        	if($type=='dependency') {
                $answer_id = null;

                $results = $results->groupBy('answer')->selectRaw('answer, COUNT(*) as cnt');
                $results = $results->get();

                foreach ($answers as $key => $value) {
                    $second_chart[$value] = 0;
                }

                foreach ($results as $res) {
                    if(!isset( $answers[ $res->answer-1 ] )) {
                        continue;
                    }
                    $second_chart[ $answers[ $res->answer-1 ] ] = $res->cnt;
                }

                $relation_info['answer'] = $question->stats_answer_id-1;
                $relation_info['question'] = $question->related->question;


                $answers_related = json_decode($question->related->answers);
                foreach ($answers_related as $key => $value) {
                    if(mb_strpos($value, '!')===0) {
                        $answers_related[$key] = mb_substr($value, 1);
                    }
                }
                $main_chart = [];
                foreach ($answers_related as $key => $value) {
                    if(mb_strpos($value, '!')===0) {
                        $answers_related[$key] = mb_substr($value, 1);
                    }
                    $main_chart[$answers_related[$key]] = 0;
                }
        		$results = $this->prepareQuery($question->stats_relation_id, $dates);
        		$results = $results->groupBy('answer')->selectRaw('answer, COUNT(*) as cnt');
        		$results = $results->get();
        		foreach ($results as $res) {
        			$main_chart[ $answers_related[ $res->answer-1 ] ] = $res->cnt;
        		}
        		

        	} else if($scale=='gender') {
                $answer_id = null;
        		$results = $results->groupBy('answer', 'gender')->selectRaw('answer, gender, COUNT(*) as cnt');
                $results = $results->get();
                foreach ($answers as $key => $value) {
                    $second_chart[$value] = 0;
                    $third_chart[$value] = 0;
                }
                
        		foreach ($results as $res) {
                    if(!isset( $answers[ $res->answer-1 ] )) {
                        continue;
                    }

        			if(!isset($main_chart[ $answers[ $res->answer-1 ] ])) {
        				$main_chart[ $answers[ $res->answer-1 ] ] = 0;
        				$second_chart[ $answers[ $res->answer-1 ] ] = 0; //m
        				$third_chart[ $answers[ $res->answer-1 ] ] = 0; //f
        			}
        			$main_chart[ $answers[ $res->answer-1 ] ] += $res->cnt;
        			if($res->gender=='f') {
        				$totalf += $res->cnt;
        				$second_chart[ $answers[ $res->answer-1 ] ] += $res->cnt; //m
        			}
        			if($res->gender=='m') {
        				$totalm += $res->cnt;
        				$third_chart[ $answers[ $res->answer-1 ] ] += $res->cnt; //f
        			}
        		}
        	} else if($scale=='country_id') {
        		$countries = Country::get()->keyBy('id');

        		$results = $results->groupBy('answer', 'country_id')->selectRaw('answer, country_id, COUNT(*) as cnt');
        		$results = $results->get();

        		foreach ($results as $res) {
                    if(!isset( $answers[ $res->answer-1 ] )) {
                        continue;
                    }

        			if(!isset($main_chart[ $answers[ $res->answer-1 ] ])) {
        				$main_chart[ $answers[ $res->answer-1 ] ] = 0;
        			}
        			$main_chart[ $answers[ $res->answer-1 ] ] += $res->cnt;

        			if( $res->country_id ) {
                        $country = $countries->get($res->country_id);
                        $country->code = mb_strtoupper($country->code);
        				if(!isset($second_chart[ $country->code ] )) {
        					$second_chart[ $country->code ] = [
                                'name' => $country->name
                            ];
                            foreach ($answers as $a) {
                                $second_chart[ $country->code ][$a] = 0;
                            }
        				}
                        if(empty($answer_id) || $res->answer==$answer_id) {
                            $second_chart[ $country->code ][ $answers[ $res->answer-1 ] ] = $res->cnt; //m
                        }
        			}
        		}
        	} else if($scale=='age') {
        		$results = $results->groupBy('answer', 'age')->selectRaw('answer, age, COUNT(*) as cnt');
        		$results = $results->get();

        		$age_to_group = config('vox.age_groups');
				foreach ($age_to_group as $k => $v) {
					$second_chart[ $v ] = [];
					foreach ($answers as $a) {
						$second_chart[ $v ][$a] = 0;
					}
				}

        		foreach ($results as $res) {
                    if(!isset( $answers[ $res->answer-1 ] )) {
                        continue;
                    }
                    
        			if(!isset($main_chart[ $answers[ $res->answer-1 ] ])) {
        				$main_chart[ $answers[ $res->answer-1 ] ] = 0;
        			}
        			$main_chart[ $answers[ $res->answer-1 ] ] += $res->cnt;


        			if( $res->age ) {
	        			$second_chart[ $age_to_group[$res->age] ][ $answers[ $res->answer-1 ] ] = $res->cnt; //m
        			}
        		}
        	} else {
        		$results = $results->groupBy('answer', $scale)->selectRaw('answer, '.$scale.', COUNT(*) as cnt');
        		$results = $results->get();

        		$age_to_group = config('vox.details_fields.'.$scale.'.values');
				foreach ($age_to_group as $k => $v) {
					$second_chart[ $v ] = [];
					foreach ($answers as $a) {
                        if(empty($answer_id) || $a==$answer_id) {
    						$second_chart[ $v ][$a] = 0;
                        }
					}
				}

        		foreach ($results as $res) {
        			if(!isset($main_chart[ $answers[ $res->answer-1 ] ])) {
        				$main_chart[ $answers[ $res->answer-1 ] ] = 0;
        			}
        			$main_chart[ $answers[ $res->answer-1 ] ] += $res->cnt;


        			if( $res->$scale ) {
    	        		$second_chart[ $age_to_group[$res->$scale] ][ $answers[ $res->answer-1 ] ] = $res->cnt; //m
        			}
        		}
        	}

            $main_chart = $this->processArray($main_chart);
            $second_chart = $this->processArray($second_chart);
            $third_chart = $this->processArray($third_chart);

        	return Response::json( [
        		'main_chart' => $main_chart,
        		'second_chart' => $second_chart,
        		'third_chart' => $third_chart,
        		'total' => $total,
        		'totalm' => $totalm,
        		'totalf' => $totalf,
                'relation_info' => $relation_info,
        		'answer_id' => $answer_id,
        	] );
        }

		$filters = [
			'all' => 'All times',
			'last7' => 'Last 7 days',
			'last30' => 'Last 30 days',
			'last365' => 'Last year',
		];
		$active_filter = Request::input('filter');
		if(!isset($filters[$active_filter])) {
			$active_filter = key($filters);
		}

        $vox->respondentsCountryCount();

        $respondents_count = $vox->respondentsCount();
        $respondents_country_count = $vox->respondentsCountryCount();

		return $this->ShowVoxView('stats-survey', array(
            'taken' => $this->user ? $this->user->filledVoxes() : [],
            'respondents' => $respondents_count,
            'respondents_country' => $respondents_country_count,
			'filters' => $filters,
			'active_filter' => $active_filter,
			'vox' => $vox,
			'scales' => config('vox.stats_scales'),


			// 'cats' => VoxCategory::get(),
			// 'start' => $start,
			// 'end' => $end,
			// 'country' => $country,
			// 'countryobj' => $countryobj,
			// 'prev' => $prev,
			// 'next' => $next,
			// 'question' => $question,
			// 'my_answer' => $my_answer,
			// 'voxes' => $voxes,
			// 'answered' => $answered,
			// 'answer_aggregates' => $answer_aggregates,
			// 'chart_data' => $chart_data,
			// 'colors' => $colors,
			// 'plotly' => true,
			'jscdn' => [
				'https://www.gstatic.com/charts/loader.js',
                '//www.amcharts.com/lib/4/core.js',
                '//www.amcharts.com/lib/4/maps.js',
                '//www.amcharts.com/lib/4/geodata/worldLow.js',
			],
			'js' => [
				'stats.js',
				'daterange/js/datepicker.js',
				'daterange/js/DateRange.js',
				//'daterange/js/DateRangesWidget.js',
			],
			'css' => [
				'../js-vox/daterange/css/datepicker/base.css',
				'../js-vox/daterange/css/datepicker/clean.css',
				//'../js/daterange/css/DateRangesWidget/base.css',
			],

            'canonical' => $vox->getStatsList(),
            'social_image' => $vox->getSocialImageUrl('stats'),
            'seo_title' => trans('vox.seo.stats.title', [
                'title' => $vox->title,
                'description' => $vox->description,
                'stats_description' => $vox->stats_description,
                'respondents' => $respondents_count,
                'respondents_country' => $respondents_country_count,
            ]),
            'seo_description' => trans('vox.seo.stats.description', [
                'title' => $vox->title,
                'description' => $vox->description,
                'stats_description' => $vox->stats_description,
                'respondents' => $respondents_count,
                'respondents_country' => $respondents_country_count,
            ]),
            'social_title' => trans('vox.social.stats.title', [
                'title' => $vox->title,
                'description' => $vox->description,
                'stats_description' => $vox->stats_description,
                'respondents' => $respondents_count,
                'respondents_country' => $respondents_country_count,
            ]),
            'social_description' => trans('vox.social.stats.description', [
                'title' => $vox->title,
                'description' => $vox->description,
                'stats_description' => $vox->stats_description,
                'respondents' => $respondents_count,
                'respondents_country' => $respondents_country_count,
            ]),
        ));
	}

	private function prepareQuery($question_id, $dates) {

    	$results = VoxAnswer::where('question_id', $question_id)
    	->where('is_completed', 1)
    	->where('is_skipped', 0)
        ->has('user');

    	if(is_array($dates)) {
    		$from = Carbon::parse($dates[0]);
    		$to = Carbon::parse($dates[1]);
    		$results = $results->where('created_at', '>=', $from)->where('created_at', '<=', $to);
    	} else if($dates) {
    		$from = Carbon::parse($dates);
    		$results = $results->where('created_at', '>=', $from);
    	}

    	return $results;
	}

    public function processArray($arr) {
        $newarr = [];
        foreach ($arr as $key => $value) {
            if(is_array($value)) {
                $a = $this->processArray($value);
                array_unshift($a, $key);
                $newarr[] = $a;
            } else {
                $newarr[] = [$key, $value];
            }
        }
        return $newarr;
    }
}