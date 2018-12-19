<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Response;
use Request;
use Validator;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use App\Models\City;
use App\Models\Country;
use App\Models\Question;
use App\Models\Secret;
use App\Models\Review;
use App\Models\ReviewUpvote;
use App\Models\ReviewDownvote;
use App\Models\ReviewAnswer;
use App\Models\UserInvite;
use App\Models\UserAsk;
use App\Models\Dcn;
use App\Models\Reward;
use App\Models\TrpReward;
use Carbon\Carbon;
use Auth;
use Cloutier\PhpIpfsApi\IPFS;


class DentistController extends FrontController
{
    public function confirmReview($locale=null, $slug, $secret) {
        $item = User::where('slug', 'LIKE', $slug)->firstOrFail();

        if(empty($item)) {
            return redirect( getLangUrl('dentists') );
        }

        $old_review = $this->user->hasReviewTo($item->id);
        //dd($old_review);
        if($old_review && $old_review->status=='pending' && $old_review->secret->secret==$secret) {
            $old_review->status = 'accepted';
            $old_review->secret->used = true;
            $old_review->secret->save();
            $old_review->save();

                        
            $item->sendTemplate(6, [
                'review_id' => $old_review->id,
            ]);

            if( $old_review->dentist_id ) {
                $old_review->dentist->recalculateRating();                
            }
            if( $old_review->clinic_id ) {
                $old_review->clinic->recalculateRating();                
            }
            
            Request::session()->flash('success-message', trans('trp.page.dentist.review-submitted'));

            return Response::json( [
                'success' => true,
            ] );
        }
        
        return Response::json( [
            'success' => false,
        ] );
    }

    public function fullReview($locale=null, $id) {
        $review = Review::find($id);

        if(empty($review)) {
            return '';
        } else {
            $item = $review->dentist_id ? User::find($review->dentist_id) : User::find($review->clinic_id);

            return $this->ShowView('popups.detailed-review-content', [
                'item' => $item,
                'review' => $review,
                'my_upvotes' => !empty($this->user) ? $this->user->usefulVotesForDenist($item->id) : null,
                'my_downvotes' => !empty($this->user) ? $this->user->unusefulVotesForDenist($item->id) : null,            
            ]);
        }
    }

    public function youtube($locale=null) {

        $fn = microtime(true).'-'.$this->user->id;
        $fileName   = storage_path(). '/app/public/'.$fn.'.webm';
        //echo 'https://reviews.dentacoin.com/storage/qqfile.webm';
        //dd($fileName);

        if ($this->request->hasFile('qqfile')) {
            $image      = $this->request->file('qqfile');
            copy($image, $fileName);
        } else {
            dd('upload a video first');
        }



        // Define an object that will be used to make all API requests.
        $client = $this->getClient();
        $service = new \Google_Service_YouTube($client);

        if (isset($_SESSION['token'])) {
            $client->setAccessToken($_SESSION['token']);
        }

        if (!$client->getAccessToken()) {
            print("no access token");
            exit;
        }


        $url = $this->videosInsert($client,
            $service,
            $fileName,
            array('snippet.categoryId' => '22',
                   'snippet.defaultLanguage' => '',
                   'snippet.description' => $this->user->getName().'\'s video review on ',
                   'snippet.tags[]' => '',
                   'snippet.title' => 'Dentist review by '.$this->user->getName(),
                   'status.embeddable' => '',
                   'status.license' => '',
                   'status.privacyStatus' => 'unlisted',
                   'status.publicStatsViewable' => ''),
            'snippet,status', array());



        return Response::json( [
            'url' => $url
        ] );
    }



    public function list($locale=null, $slug, $review_id=null) {
        $item = User::where('slug', 'LIKE', $slug)->firstOrFail();

        if(empty($item) || !$item->is_dentist) {
            return redirect( getLangUrl('dentists') );
        }

        //$item->recalculateRating();
        $isTrusted = !empty($this->user) ? $this->user->wasInvitedBy($item->id) : false;

        $questions = Question::get();


        if(Request::isMethod('post')) {

            $ret = array(
                'success' => false
            );
            $validator_arr = [
                'answer' => ['required_without:youtube_id'],
                'youtube_id' => ['required_without:answer']
            ];
            foreach ($questions as $question) {
                if($question->id == 4 && $item->is_clinic && empty( Request::input( 'clinic_dentists' ) )  ) {
                    continue;
                }

                    
                $opts = json_decode($question['options'], true);

                foreach ($opts as $i => $nosense) {
                }

                $validator_arr['option.'.$question->id.'.'.$i] = ['required', 'numeric', 'min:1', 'max:5'];
            }

            $validator = Validator::make(Request::all(), $validator_arr);

            if ($validator->fails()) {

                $msg = $validator->getMessageBag()->toArray();
                $ret['messages'] = [];
                foreach ($msg as $field => $errors) {
                    $ret['messages'][$field] = implode(', ', $errors);
                }           

                return Response::json( $ret );
            } else {

                $real_text = strip_tags(Request::input( 'answer' ));
                $real_text_words = explode(' ', $real_text);
                if( empty(Request::input( 'youtube_id' )) && (mb_strlen($real_text)<100 || count($real_text_words)<20) ) {
                    $ret['short_text'] = true;
                    return Response::json( $ret );

                }

                $ret['valid_input'] = true;

                if( !$this->user->is_dentist) {

                    $old_review = $this->user->hasReviewTo($item->id);
                    if($old_review && $old_review->status=='accepted') {
                        ; //dgd
                    } else if( $this->user->loggedFromBadIp() ) {
                        ; //dgd
                    } else if( $this->user->getReviewLimits() ) {
                        ; //dgd
                    } else if( $this->user->cantReviewDentist($item->id) ) {
                        ; //dgd
                    } else {

                        $secret = Secret::getNext();

                        if($old_review && $old_review->status=='pending') {
                            $review = $old_review;
                        } else {
                            $review = new Review;
                            $review->user_id = $this->user->id;
                            if($item->is_clinic) {
                                $review->clinic_id = $item->id;
                                if(!empty(Request::input( 'clinic_dentists' ))) {
                                    $review->dentist_id = Request::input( 'clinic_dentists' );
                                }
                            } else {
                                $review->dentist_id = $item->id;
                                if(!empty(Request::input( 'dentist_clinics' ))) {
                                    $review->clinic_id = Request::input( 'dentist_clinics' );
                                }
                            }
                            
                        }

                        $review->rating = 0;
                        $review->title = strip_tags(Request::input( 'title' ));
                        $review->answer = strip_tags(Request::input( 'answer' ));
                        $review->youtube_id = strip_tags(Request::input( 'youtube_id' ));
                        $review->verified = !empty($isTrusted);
                        $review->status = 'pending';
                        $review->secret_id = $secret->id;
                        $review->save();

                        $total = 0;
                        $answer_rates = [];
                        $crypto_data = [];
                        $crypto_data['answer'] = strip_tags(Request::input( 'answer' ));
                        foreach ($questions as $question) {
                            
                            if($question->id == 4 && $item->is_clinic && empty( Request::input( 'clinic_dentists' ) )  ) {
                                continue;
                            }

                            $crypto_data['question-'.$question->id] = [];
                            $answer_rates[$question->id] = 0;
                            $option_answers = [];
                            $options = json_decode($question['options'], true);
                            foreach ($options as $i => $nosense) {
                                $r = Request::input( 'option.'.$question->id.'.'.$i );;
                                $option_answers[] = $r;
                                $answer_rates[$question->id] += $r;
                            }

                            $answer_rates[$question->id] /= count($options);
                            
                            if($old_review) {
                                $answer = ReviewAnswer::where([
                                    ['review_id', $review->id],
                                    ['question_id', $question->id],
                                ])->first();
                            } else {
                                $answer = new ReviewAnswer;
                            }
                            $answer->review_id = $review->id;
                            $answer->question_id = $question->id;
                            $answer->options = json_encode($option_answers);
                            $crypto_data['question-'.$question->id] = $option_answers;
                            $answer->save();
                        }

                        $review->rating = array_sum($answer_rates) / count($answer_rates);
                        $review->save();

                        $ipfs = new IPFS("127.0.0.1", "8080", "5001");
                        $review->ipfs = $ipfs->add(json_encode($crypto_data));
                        $review->save();

                        
                        //Send & confirm
                        $is_video = $review->youtube_id ? '_video' : '';
                        $amount = $review->verified ? Reward::getReward('review'.$is_video.'_trusted') : Reward::getReward('review'.$is_video);
                        
                        if(!$is_video && $review->verified) {
                            $reward = new TrpReward();
                            $reward->user_id = $this->user->id;
                            $reward->reward = $amount;
                            $reward->type = 'review';
                            $reward->reference_id = $review->id;
                            $reward->save();                            
                        }

                        $review->status = 'accepted';
                        $review->secret->used = true;
                        $review->secret->save();
                        $review->save();

                        if(!$review->youtube_id) {
                            $review->afterSubmitActions();
                        }

                        $ret['success'] = true;

                    }
                }
            }

            return Response::json( $ret );
        }



        $reviews = $item->reviews_in();
        if($review_id) {
            $review = Review::find($review_id);
            if(!empty($review) && !empty($reviews)) {
                $rid = $review->id;
                $reviews = $reviews->reject(function ($value, $key) use ($rid) {
                    return $value->id == $rid;
                });
                $reviews = collect([$review])->merge($reviews);
            }
        }

        $aggregated_rates = [];
        $aggregated_rates_total = [];
        $count = $reviews->count();
        if($count) {
            foreach ($reviews as $review) {
                foreach($review->answers as $answer) {
                    if(empty($aggregated_rates[$answer->question->id])) {
                        $aggregated_rates[$answer->question->id] = [];
                    }
                        
                    $opts = json_decode($answer->options, true);
                    foreach(json_decode($answer->question['options'], true) as $i => $option) {
                        if(empty($aggregated_rates[$answer->question->id][$i])) {
                            $aggregated_rates[$answer->question->id][$i] = 0;
                        }
                        $aggregated_rates[$answer->question->id][$i] += $opts[$i];
                    }
                }
            }

            foreach ($aggregated_rates as $key => $value) {
                foreach ($value as $kk => $vv) {
                    $aggregated_rates[$key][$kk] = $vv/$count;
                }
            }
            foreach ($aggregated_rates as $key => $value) {
                $aggregated_rates_total[$key] = array_sum($value)/count($value);
            }
        }

        $dentist_limit_reached = !empty($this->user) ? $this->user->cantReviewDentist($item->id) : null;
        $has_asked_dentist = $this->user ? $this->user->hasAskedDentist($item->id) : null;

        if( $this->user ) {
            $review_reward = $isTrusted ? Reward::getReward('review_trusted') : Reward::getReward('review');
            $review_reward_video = $isTrusted ? Reward::getReward('review_video_trusted') : Reward::getReward('review_video');
        } else {
            $review_reward = $review_reward_video = 0;
        }


        $social_image = $item->getSocialCover();
        $is_review = false;
        if( request('review_id') && $current_review = $reviews->find(request('review_id')) ) {
            $current_review->generateSocialCover();
            $social_image = $current_review->getSocialCover();
            $is_review = true;
        }

//
//https://dev-reviews.dentacoin.com/en/dentist/teeth-care-centre-dental-hospital?review_id=6505


        $view_params = [
            'item' => $item,
            'is_trusted' => $isTrusted,
            'my_review' => !empty($this->user) ? $this->user->hasReviewTo($item->id) : null,
            'my_upvotes' => !empty($this->user) ? $this->user->usefulVotesForDenist($item->id) : null,
            'my_downvotes' => !empty($this->user) ? $this->user->unusefulVotesForDenist($item->id) : null,
            'questions' => $questions,
            'reviews' => $reviews,
            'review_reward' => $review_reward,
            'review_reward_video' => $review_reward_video,
            'review_limit_reached' => !empty($this->user) ? $this->user->getReviewLimits() : null,
            'dentist_limit_reached' => $dentist_limit_reached,
            'has_asked_dentist' => $has_asked_dentist,
            'aggregated_rates' => $aggregated_rates,
            'aggregated_rates_total' => $aggregated_rates_total ,
            'social_image' => $social_image,
            'canonical' => $item->getLink().($review_id ? '?review_id='.$review_id : ''),
            'js' => [
                'videojs.record.min.js',
                'user.js',
                'search.js',
            ],
            'jscdn' => [],
        ];

        if( $is_review ) {
            $view_params['seo_title'] = trans('trp.seo.review.title', [
                'dentist_name' => $item->getName(),
                'user_name' => $current_review->user->getName(),
            ]);
            $view_params['social_title'] = trans('trp.social.review.title', [
                'dentist_name' => $item->getName(),
                'user_name' => $current_review->user->getName(),
            ]);
            $view_params['seo_description'] = trans('trp.seo.review.description', [
                'review_title' => $current_review->title,
                'review_text' => $current_review->answer,
            ]);
            $view_params['social_description'] = trans('trp.social.review.description', [
                'review_title' => $current_review->title,
                'review_text' => $current_review->answer,
            ]);

        } else {
            $view_params['seo_title'] = trans('trp.seo.dentist.title', [
                'name' => $item->getName(),
                'country' => $item->country ? $item->country->name : '',
                'city' => $item->city ? $item->city->name : '',
            ]);
            $view_params['social_title'] = trans('trp.social.dentist.title', [
                'name' => $item->getName(),
                'country' => $item->country ? $item->country->name : '',
                'city' => $item->city ? $item->city->name : '',
            ]);

            $view_params['seo_description'] = trans('trp.seo.dentist.description', [
                'name' => $item->getName(),
                'country' => $item->country ? $item->country->name : '',
                'city' => $item->city ? $item->city->name : '',
            ]);
            $view_params['social_description'] = trans('trp.social.dentist.description', [
                'name' => $item->getName(),
                'country' => $item->country ? $item->country->name : '',
                'city' => $item->city ? $item->city->name : '',
            ]);   
        }
        

        if(!empty($this->user) && $this->user->id==$item->id) {
            $view_params['js'][] = 'upload.js';
            $view_params['hours'] = [
            ];
            for($i=0;$i<=23;$i++) {
                $h = str_pad($i, 2, "0", STR_PAD_LEFT);
                $view_params['hours'][$h] = $h;
            }

            $view_params['minutes'] = [
                '00' => '00',
                '10' => '10',
                '20' => '20',
                '30' => '30',
                '40' => '40',
                '50' => '50',
            ];
        }

        if($item->lat && $item->lon) {
            $view_params['jscdn'][] = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en';
        }


        if(!empty($this->user) && !$this->user->civic_id) {
            $view_params['js'][] = 'civic.js';
            $view_params['jscdn'][] = 'https://hosted-sip.civic.com/js/civic.sip.min.js';
            $view_params['csscdn'] = [
                'https://hosted-sip.civic.com/css/civic-modal.min.css',
            ];
        }

        return $this->ShowView('user', $view_params);

    }


    public function ask($locale=null, $slug) {
        $item = User::where('slug', 'LIKE', $slug)->firstOrFail();

        if(!empty($item)) {


            if(!empty($this->user) && !$this->user->cantReviewDentist($item->id)) {

                $ask = $this->user->hasAskedDentist($item->id);
                if(empty($ask)) {
                    $ask = new UserAsk;
                    $ask->user_id = $this->user->id;
                    $ask->dentist_id = $item->id;
                    $ask->status = 'waiting';
                    $ask->save();

                    $item->sendTemplate( 23 ,[
                        'patient_name' => $this->user->name
                    ] );

                    return Response::json( ['success' => true] );
                }
            }

        }

        return Response::json( ['success' => false] );
    }

    public function useful($locale=null, $review_id) {
        $review = Review::find($review_id);
        if(!empty($review)) {
            $myvotes = $this->user->usefulVotesForDenist($review->dentist_id);
            if(!in_array($review_id, $myvotes)) {
                $review->upvotes++;
                $review->save();
                $uv = new ReviewUpvote;
                $uv->review_id = $review_id;
                $uv->user_id = $this->user->id;
                $uv->save();
            }
        }

        return Response::json( ['success' => true] );
    }


    public function unuseful($locale=null, $review_id) {
        $review = Review::find($review_id);
        if(!empty($review)) {
            $myvotes = $this->user->unusefulVotesForDenist($review->dentist_id);
            if(!in_array($review_id, $myvotes)) {
                $review->downvotes++;
                $review->save();
                $uv = new ReviewDownvote;
                $uv->review_id = $review_id;
                $uv->user_id = $this->user->id;
                $uv->save();
            }
        }

        return Response::json( ['success' => true] );
    }

    public function reply($locale=null, $slug, $review_id) {
        $review = Review::find($review_id);
        if(!empty($review) && ($this->user->id==$review->clinic_id || $this->user->id==$review->dentist_id ) ) {
            $review->reply = strip_tags(Request::input( 'reply' ));
            $review->save();
            $review->user->sendTemplate(8, [
                'review_id' => $review->id,
            ]);
        }

        return Response::json( ['success' => true, 'reply' => nl2br( $review->reply )] );
    }


    //
    //Youtube boilerplate
    //



    function videosInsert($client, $service, $media_file, $properties, $part, $params) {
        $params = array_filter($params);
        $propertyObject = $this->createResource($properties); // See full sample for function
        $resource = new \Google_Service_YouTube_Video($propertyObject);
        $client->setDefer(true);
        $request = $service->videos->insert($part, $resource, $params);
        $client->setDefer(false);
        $response = $this->uploadMedia($client, $request, $media_file, 'video/*');
        return $response->id;
    }



    function getClient() {
        $client = new \Google_Client();
        $client->setApplicationName('API Samples');
        $client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
        // Set to name/location of your client_secrets.json file.
        $client->setAuthConfig( storage_path() . '/client_secrets.json');
        $client->setAccessType('offline');

        // Load previously authorized credentials from a file.
        $credentialsPath = storage_path() . 'yt-oauth2.json';
        if (file_exists($credentialsPath)) {
            $accessToken = json_decode(file_get_contents($credentialsPath), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';


            if (isset($_GET['code'])) {


                $credentialsPath = storage_path() . 'yt-oauth2.json';
                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

                // Store the credentials to disk.
                if(!file_exists(dirname($credentialsPath))) {
                    mkdir(dirname($credentialsPath), 0700, true);
                }
                file_put_contents($credentialsPath, json_encode($accessToken));
            }

            return;
        }
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    // Add a property to the resource.
    function addPropertyToResource(&$ref, $property, $value) {
        $keys = explode(".", $property);
        $is_array = false;
        foreach ($keys as $key) {
            // For properties that have array values, convert a name like
            // "snippet.tags[]" to snippet.tags, and set a flag to handle
            // the value as an array.
            if (substr($key, -2) == "[]") {
                $key = substr($key, 0, -2);
                $is_array = true;
            }
            $ref = &$ref[$key];
        }

        // Set the property value. Make sure array values are handled properly.
        if ($is_array && $value) {
            $ref = $value;
            $ref = explode(",", $value);
        } elseif ($is_array) {
            $ref = array();
        } else {
            $ref = $value;
        }
    }

    // Build a resource based on a list of properties given as key-value pairs.
    function createResource($properties) {
        $resource = array();
        foreach ($properties as $prop => $value) {
            if ($value) {
                $this->addPropertyToResource($resource, $prop, $value);
            }
        }
        return $resource;
    }

    function uploadMedia($client, $request, $filePath, $mimeType) {
        // Specify the size of each chunk of data, in bytes. Set a higher value for
        // reliable connection as fewer chunks lead to faster uploads. Set a lower
        // value for better recovery on less reliable connections.
        $chunkSizeBytes = 1 * 1024 * 1024;

        // Create a MediaFileUpload object for resumable uploads.
        // Parameters to MediaFileUpload are:
        // client, request, mimeType, data, resumable, chunksize.
        $media = new \Google_Http_MediaFileUpload(
            $client,
            $request,
            $mimeType,
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($filePath));


        // Read the media file and upload it chunk by chunk.
        $status = false;
        $handle = fopen($filePath, "rb");
        while (!$status && !feof($handle)) {
          $chunk = fread($handle, $chunkSizeBytes);
          $status = $media->nextChunk($chunk);
        }

        fclose($handle);
        return $status;
    }

}