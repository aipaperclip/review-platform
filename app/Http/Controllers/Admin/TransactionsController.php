<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\DcnTransaction;

use Carbon\Carbon;

use Request;
use Route;
use Auth;

class TransactionsController extends AdminController
{
    public function list() {
        $transactions = DcnTransaction::orderBy('id', 'DESC');

        if(!empty($this->request->input('search-address'))) {
            $transactions = $transactions->where('address', 'LIKE', '%'.trim($this->request->input('search-address')).'%');
        }
        if(!empty($this->request->input('search-tx'))) {
            $transactions = $transactions->where('tx_hash', 'LIKE', '%'.trim($this->request->input('search-tx')).'%');
        }
        if(!empty($this->request->input('search-user-id'))) {
            $transactions = $transactions->where('user_id', $this->request->input('search-user-id'));
        }
        if(!empty($this->request->input('search-status'))) {
            $transactions = $transactions->where('status', $this->request->input('search-status'));
        }        
        if(!empty($this->request->input('search-from'))) {
            $firstday = new Carbon($this->request->input('search-from'));
            $transactions = $transactions->where('created_at', '>=', $firstday);
        }
        if(!empty($this->request->input('search-to'))) {
            $firstday = new Carbon($this->request->input('search-to'));
            $transactions = $transactions->where('created_at', '<=', $firstday);
        }
        if(!empty($this->request->input('search-email'))) {
            $mail = $this->request->input('search-email');
            $transactions = $transactions->whereHas('user', function ($query) use ($mail) {
                $query->where('email', 'like', '%'.trim($mail).'%');
            });
        } 

        if(!empty($this->request->input('created'))) {
            $order = request()->input( 'created' );
            $transactions->getQuery()->orders = null;
            $transactions = $transactions
            ->orderBy('created_at', $order);
        }

        if (!empty(request()->input( 'attempt' ))) {
            $order = request()->input( 'attempt' );
            $transactions->getQuery()->orders = null;
            $transactions = $transactions
            ->orderBy('updated_at', $order);
        }

        $total_count = $transactions->count();


        $page = max(1,intval(request('page')));
        
        $ppp = 25;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        //Here we generates the range of the page numbers which will display.
        if($total_pages <= (1+($adjacents * 2))) {
          $start = 1;
          $end   = $total_pages;
        } else {
          if(($page - $adjacents) > 1) { 
            if(($page + $adjacents) < $total_pages) { 
              $start = ($page - $adjacents);            
              $end   = ($page + $adjacents);         
            } else {             
              $start = ($total_pages - (1+($adjacents*2)));  
              $end   = $total_pages;               
            }
          } else {               
            $start = 1;                                
            $end   = (1+($adjacents * 2));             
          }
        }

        $transactions = $transactions->skip( ($page-1)*$ppp )->take($ppp)->get();

        //If you want to display all page links in the pagination then
        //uncomment the following two lines
        //and comment out the whole if condition just above it.
        /*$start = 1;
        $end = $total_pages;*/

        $current_url = url('cms/transactions');

        $pagination_link = (!empty($this->request->input('search-address')) ? '&search-address='.$this->request->input( 'search-address' ) : '').(!empty($this->request->input('search-tx')) ? '&search-tx='.$this->request->input( 'search-tx' ) : '').(!empty($this->request->input('search-user-id')) ? '&search-user-id='.$this->request->input( 'search-user-id' ) : '').(!empty($this->request->input('search-email')) ? '&search-email='.$this->request->input( 'search-email' ) : '').(!empty($this->request->input('search-status')) ? '&search-status='.$this->request->input( 'search-status' ) : '').(!empty($this->request->input('search-from')) ? '&search-from='.$this->request->input( 'search-from' ) : '').(!empty($this->request->input('search-to')) ? '&search-to='.$this->request->input( 'search-to' ) : '');


        return $this->showView('transactions', array(
            'transactions' => $transactions,
            'total_count' => $total_count,
            'search_address' => $this->request->input('search-address'),
            'search_status' => $this->request->input('search-status'),
            'search_tx' => $this->request->input('search-tx'),
            'search_user_id' => $this->request->input('search-user-id'),
            'search_to' => $this->request->input('search-to'),
            'search_from' => $this->request->input('search-from'),
            'search_email' =>  $this->request->input('search-email'),
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
            'current_url' => $current_url,
        ));
    }

    public function bump( $id ) {
        $item = DcnTransaction::find($id);

        $item->status = 'new';
        $item->retries = 0;

        $item->save();

        $this->request->session()->flash('success-message', 'Transaction bumped' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function stop( $id ) {
        $item = DcnTransaction::find($id);

        $item->status = 'stopped';

        $item->save();

        $this->request->session()->flash('success-message', 'Transaction stopped' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function massbump(  ) {
        if( Request::input('ids') ) {
            $bumptrans = DcnTransaction::whereIn('id', Request::input('ids'))->get();
            foreach ($bumptrans as $bt) {
                $bt->status = 'new';
                $bt->retries = 0;
                $bt->save();
            }
        }

        $this->request->session()->flash('success-message', 'All selected transactions are bumped' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function massstop(  ) {
        if( Request::input('ids') ) {
            $stoptrans = DcnTransaction::whereIn('id', Request::input('ids'))->get();
            foreach ($stoptrans as $st) {
                $st->status = 'stopped';
                $st->save();
            }
        }

        $this->request->session()->flash('success-message', 'All selected transactions are stopped' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

}
