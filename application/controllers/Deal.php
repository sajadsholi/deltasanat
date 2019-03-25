<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Deal extends CI_Controller {

    
    public function __construct(){
        parent::__construct();
        $this->load->library('Convertdate');
        $this->load->library('pagination');
    }
//-----    start archive -----//
   public function archive(){
    if(isset($_POST['sub'])){
        $data['m'] = $_POST['money_id']; $data['t'] = $_POST['deal_type'];
        if($_POST['start_date'] != $_POST['end_date']){
        $persian_num = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $latin_num = range(0, 9);
        $slash = '/';
        $dash = '-';
        $start = str_replace($persian_num, $latin_num, $_POST['start_date']);
        $start = str_replace($slash, $dash, $start);
        $end = str_replace($persian_num, $latin_num, $_POST['end_date']);
        $end = str_replace($slash, $dash, $end); 
        $date_start = substr($start , 0 , 10);
        $date_end = substr($end , 0 , 10);
        $between = "deal.date_deal BETWEEN '$date_start' AND '$date_end'";
        if($_POST['deal_type'] == 0 and $_POST['money_id'] == 0){
            $where = NULL;
            $total_rows = $this->base_model->get_count_between("deal" , NULL , $between);
        }else if($_POST['deal_type'] == 0){
           $where = array('deal.money_id' => $_POST['money_id']);
           $total_rows = $this->base_model->get_count_between("deal" , array('money_id'=> $_POST['money_id']) , $between);
        }else if($_POST['money_id'] == 0){
            $where = array('deal.type_deal' => $_POST['deal_type']);
            $total_rows = $this->base_model->get_count_between("deal" , array('type_deal'=> $_POST['deal_type']) , $between);
        }else{
            $where = array('deal.type_deal' => $_POST['deal_type'] , 'deal.money_id' => $_POST['money_id']);
            $total_rows = $this->base_model->get_count_between("deal" , array('type_deal' => $_POST['deal_type'] , 'money_id' => $_POST['money_id']) , $between);
        }
        }else{
            $between = NULL;
            if($_POST['deal_type'] == 0 and $_POST['money_id'] == 0){
                $where = NULL;
                $total_rows = $this->base_model->get_count("deal" ,'ALL');
            }else if($_POST['deal_type'] == 0){
               $where = array('deal.money_id' => $_POST['money_id']);
               $total_rows = $this->base_model->get_count("deal" , array('money_id'=> $_POST['money_id']));
            }else if($_POST['money_id'] == 0){
                $where = array('deal.type_deal' => $_POST['deal_type']);
                $total_rows = $this->base_model->get_count("deal" , array('type_deal'=> $_POST['deal_type']));
            }else{
                $where = array('deal.type_deal' => $_POST['deal_type'] , 'deal.money_id' => $_POST['money_id']);
                $total_rows = $this->base_model->get_count("deal" , array('type_deal' => $_POST['deal_type'] , 'money_id' => $_POST['money_id']));
            }
        }
    }else{
        $data['m'] = 0; $data['t'] = 0;
        $between = NULL;
        $where = NULL;
        $total_rows = $this->base_model->get_count("deal" , 'ALL');
    }
    $config['base_url'] = base_url('deal/archive');
    $config['total_rows'] = $total_rows;
    $config['per_page'] = '10';
    $config["uri_segment"] = '3';
    $config['num_links'] = '5';
    $config['next_link'] = '<i class="icon-arrow-left5"></i>';
    $config['last_link'] = '<i class="icon-backward2"></i>';
    $config['prev_link'] = '<i class="icon-arrow-right5"></i>';
    $config['first_link'] = '<i class="icon-forward3"></i>';
    $config['full_tag_open'] = '<nav><ul class="pagination pagination-sm">';
    $config['full_tag_close'] = '</ul></nav>';
    $config['cur_tag_open'] = '<li class="active"><a href="javascript:void(0)">';
    $config['cur_tag_close'] = '</a></li>';
    $config['num_tag_open'] = '<li>';
    $config['num_tag_close'] = '</li>';
    $config['next_tag_open'] = '<li>';
    $config['next_tag_close'] = '</li>';
    $config['last_tag_open'] = '<li>';
    $config['last_tag_close'] = '</li>';
    $config['first_tag_open'] = '<li>';
    $config['first_tag_close'] = '</li>';
    $config['prev_tag_open'] = '<li>';
    $config['prev_tag_close'] = '</li>';
    $config['suffix'] = "";
$this->pagination->initialize($config);
$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;      
$data['deal'] = $this->base_model->get_data_join('deal' ,'customer', 'deal.* , customer.fullname , customer.id as cust_id ,currency_unit.name' , 'deal.customer_id = customer.id' ,'result'  , $where , $config['per_page'] , $page , array('deal.id' , 'DESC') , array('currency_unit','deal.money_id = currency_unit.id') , NULL , $between);
$data['page'] = $this->pagination->create_links();
$data['count'] = $config['total_rows'];
$date = $this->convertdate->convert(time());
$data['date'] = $date['year']."/".$date['month_num']."/".$date['day'] . " ".$date['hour'].":".$date['minute'].":".$date['second'];
        $header['title'] = 'آرشیو معاملات';
        $header['active'] = 'deal';
        $header['active_sub'] = 'deal_archive';
        $this->load->view('header' , $header);
        $this->load->view('deal/archive',$data);
        $this->load->view('footer');
        
    }
    //----- archive -----//

    //----- search customer -----// 
    public function search(){
        if(isset($_POST['text_search'])){
            $title = $this->input->post('text_search');
            $data = $this->base_model->search_data('deal' , 'customer' , 'deal.* , customer.fullname , customer.id as cust_id ,currency_unit.name' ,'deal.customer_id = customer.id' , 'inner'  , array('customer.fullname'=>$title) , NULL , NULL , NULL , array('currency_unit','deal.money_id = currency_unit.id'));
            echo json_encode($data);
        }else{
            show_404();
        }
    }
    //----- search customer -----//

    //----- buy and sell -----//
    public function buy(){
        if(isset($_POST['sub'])){
            $customer['fullname'] = $this->input->post('customer[0]');
            $check = $this->base_model->get_data('customer' , 'id' , 'row' , array('fullname'=>$customer['fullname']));
            if(sizeof($check) == 0){
                $customer['address'] = '';
                $customer['email'] = '';
                $customer['customer_tel'] = '';
                $id = $this->base_model->insert_data('customer' , $customer);
            }else{
                $id = $check->id;
            }
            $date = $this->convertdate->convert(time());
           $deal['count_money'] = $this->input->post('count_money');
           $deal['wage'] = $this->input->post('wage');
           $deal['convert_money'] = $this->input->post('convert_money');
           $deal['volume_deal'] = ($deal['count_money'] + $deal['wage']) * $deal['convert_money'];
           $deal['volume_pay'] = 0;
           $deal['volume_rest'] = $deal['volume_deal'];
           $deal['explain'] = $this->input->post('explain');
           $deal['date_deal'] = $date['year']."-".$date['month_num']."-".$date['day'];
           $deal['time_deal'] = $date['hour'].":".$date['minute'].":".$date['second'];
           $deal['date_modified'] = '';
           $deal['type_deal'] = $this->input->post('deal_type');
           $deal['customer_id'] = $id;
           $deal['money_id'] = $this->input->post('money_id');
           if($deal['type_deal'] == 1){$page = 'buy'; $act = 9;}else {$page = "sell"; $act = 10;}
           $deal_id = $this->base_model->insert_data('deal' , $deal);
           if($deal_id == FALSE){
               $message['msg'][0] = 'مشکلی در ثبت اطلاعات رخ داده است. لطفا دوباره سعی کنید';
               $message['msg'][1] = 'danger';
               $this->session->set_flashdata($message);
               redirect("deal/$page");
           } 

           $img = array();

           if($_FILES['deal_pic']['name'][0] != ''){
            $count = count($_FILES['deal_pic']['name']);
            $files['name'] = $_FILES['deal_pic']['name'];
            $files['type'] = $_FILES['deal_pic']['type'];
            $files['tmp_name'] = $_FILES['deal_pic']['tmp_name'];
            $files['error'] = $_FILES['deal_pic']['error'];
            $files['size'] = $_FILES['deal_pic']['size'];

            for($j = 0 ; $j < $count ; $j++){
            
                $_FILES['deal_pic']['name'] = $files['name'][$j];
                $_FILES['deal_pic']['type'] = $files['type'][$j];
                $_FILES['deal_pic']['tmp_name'] = $files['tmp_name'][$j];
                $_FILES['deal_pic']['error'] = $files['error'][$j];
                $_FILES['deal_pic']['size'] = $files['size'][$j];
    
                $config['upload_path'] = './uploads/deal';
                $config['allowed_types']        = 'gif|jpg|png|jpeg';
                $config['max_size']             = 1000000000;
    
                $this->load->library('upload', $config);  
                $this->upload->initialize($config);
    
                if($this->upload->do_upload('deal_pic')){
                    $img[] = array(
                        'deal_id'=> $deal_id,
                        'pic_name' => $files['name'][$j],
                        'date_upload'=>$deal['date_deal']."</br>".$deal['time_deal']
                    );
                }else{
                    $message['msg'][0] = 'مشکلی در ارسال عکس ها پیش آمده لطفا دوباره سعی کنید';
                    $message['msg'][1] = 'danger';
                    $this->session->set_flashdata($message);
                    redirect("deal/$page");
                }
               }
           $this->base_model->insert_batch('deal_pic' , $img);
           }
          if($_POST['number_shaba'][0] != ''){
            for($i = 0 ; $i < sizeof($_POST['number_shaba']) ; $i++){
                $bank[] = array(
                    'explain'=> htmlspecialchars($_POST['bank_explain'][$i]),
                    'number_shaba'=>htmlspecialchars($_POST['number_shaba'][$i]),
                    'name_bank'=> htmlspecialchars($_POST['name_bank'][$i]),
                    'amount'=> htmlspecialchars($_POST['amount_bank'][$i]),
                    'pay'=>0,
                    'active'=> 1,
                    'deal_id'=> $deal_id
                );
            }
            $res_bank = $this->base_model->insert_batch('deal_bank' , $bank);
            if($res_bank == FALSE){
        $message['msg'][0] = 'مشکلی در ثبت اطلاعات رخ داده است. لطفا دوباره سعی کنید';
        $message['msg'][1] = 'danger';
        $this->session->set_flashdata($message);
        redirect("deal/$page");
            }
          }
          if($deal['money_id'] == 1){$money = 'دلار';}else if($deal['money_id'] == 2){$money = 'یورو';}else if($deal['money_id'] == 3){$money = 'یوان';}else{$money = 'درهم';}
          $aa = $deal_id + 100;
          $log['user_id'] = $this->session->userdata('id');
          $log['date_log'] = $deal['date_deal'];
          $log['time_log'] = $deal['time_deal'];
          $log['activity_id'] = $act;
          $log['explain'] = " نام مشتری :  ".$customer['fullname']." | شناسه معامله : ".$aa . " | ارز معامله : ". $money . " | تعداد ارز : ".number_format($deal['count_money']).$money ." | کارمزد : ".number_format($deal['wage']).$money . " | نرخ تبدیل : ".number_format($deal['convert_money'])." ریال "." حجم معامله :  ".number_format($deal['volume_deal'])." ریال ";
          $this->base_model->insert_data('log' , $log);
         $message['msg'][0] = 'اطلاعات معامله با موفقیت ثبت شد';
         $message['msg'][1] = 'success';
         $this->session->set_flashdata($message);
         redirect("deal/$page");

        }else{
            $header['title'] = 'افزودن معامله';
            $header['active'] = 'deal';
            $header['active_sub'] = 'deal_buy';
            $data['customer'] = $this->base_model->get_data('customer' ,'fullname' , 'result');
            $this->load->view('header' , $header);
            $this->load->view('deal/buy' , $data);
            $this->load->view('footer');
        }

        
    }
    public function sell(){
            $header['title'] = 'افزودن معامله';
            $header['active'] = 'deal';
            $header['active_sub'] = 'deal_sell';
            $data['customer'] = $this->base_model->get_data('customer' ,'fullname' , 'result');
            $this->load->view('header' , $header);
            $this->load->view('deal/sell', $data);
            $this->load->view('footer');
    }
      //----- buy and sell -----//

     //----- edit -----//
    public function edit(){
        $id = $this->uri->segment(3);
        if(isset($id) and is_numeric($id)){
            if(isset($_POST['sub'])){
              $customer['fullname'] = $this->input->post('customer');
              $cust_id = $this->input->post('cust_id');
              $this->base_model->update_data('customer' , $customer , array('id'=> $cust_id));
             $date = $this->convertdate->convert(time());
             $deal['count_money'] = $this->input->post('count_money');
             $deal['wage'] = $this->input->post('wage');
             $deal['convert_money'] = $this->input->post('convert_money');
             $deal['volume_deal'] = ($deal['count_money'] + $deal['wage']) * $deal['convert_money'];
             $deal['explain'] = $this->input->post('explain');
             $deal['date_modified'] = $date['year']."-".$date['month_num']."-".$date['day']."</br>".$date['hour'].":".$date['minute'].":".$date['second'];
             $deal['volume_rest'] = $deal['volume_deal'] - $this->input->post('volume_pay');
             $deal['customer_id'] = $cust_id;
             $deal['money_id'] = $this->input->post('money_id');
             $res = $this->base_model->update_data('deal' , $deal , array('id'=> $id));
             if($res == FALSE){
                 $message['msg'][0] = 'مشکلی در ثبت اطلاعات رخ داده است. لطفا دوباره سعی کنید';
                 $message['msg'][1] = 'danger';
                 $this->session->set_flashdata($message);
                 redirect("deal/edit/$id");
             } 
  
             $img = array();
  
             if($_FILES['deal_pic']['name'][0] != ''){
              $count = count($_FILES['deal_pic']['name']);
              $files['name'] = $_FILES['deal_pic']['name'];
              $files['type'] = $_FILES['deal_pic']['type'];
              $files['tmp_name'] = $_FILES['deal_pic']['tmp_name'];
              $files['error'] = $_FILES['deal_pic']['error'];
              $files['size'] = $_FILES['deal_pic']['size'];
  
              for($j = 0 ; $j < $count ; $j++){
              
                  $_FILES['deal_pic']['name'] = $files['name'][$j];
                  $_FILES['deal_pic']['type'] = $files['type'][$j];
                  $_FILES['deal_pic']['tmp_name'] = $files['tmp_name'][$j];
                  $_FILES['deal_pic']['error'] = $files['error'][$j];
                  $_FILES['deal_pic']['size'] = $files['size'][$j];
                  $config['upload_path'] = './uploads/deal';
                  $config['allowed_types']        = 'gif|jpg|png|jpeg';
                  $config['max_size']             = 1000000000;
      
                  $this->load->library('upload', $config);  
                  $this->upload->initialize($config);
      
                  if($this->upload->do_upload('deal_pic')){
                      $img[] = array(
                          'deal_id'=> $id,
                          'pic_name' => $files['name'][$j],
                          'date_upload'=>$deal['date_modified']
                      );
                  }else{
                      $message['msg'][0] = 'مشکلی در ارسال عکس ها پیش آمده لطفا دوباره سعی کنید';
                      $message['msg'][1] = 'danger';
                      $this->session->set_flashdata($message);
                      redirect("deal/edit/$id");
                  }
                 }
             $this->base_model->insert_batch('deal_pic' , $img);
             }
            if($_POST['number_shaba'][0] != ''){
              for($i = 0 ; $i < sizeof($_POST['number_shaba']) ; $i++){
                  $bank[] = array(
                      'explain'=> htmlspecialchars($_POST['bank_explain'][$i]),
                      'number_shaba'=>htmlspecialchars($_POST['number_shaba'][$i]),
                      'name_bank'=> htmlspecialchars($_POST['name_bank'][$i]),
                      'amount'=> htmlspecialchars($_POST['amount_bank'][$i]),
                      'pay'=>htmlspecialchars($_POST['pay'][$i]),
                      'active'=> htmlspecialchars($_POST['active'][$i]),
                      'deal_id'=> $id
                  );
              }
              $this->base_model->delete_data('deal_bank' , array('deal_id'=> $id));
              $res_bank = $this->base_model->insert_batch('deal_bank' , $bank);
              if($res_bank == FALSE){
          $message['msg'][0] = 'مشکلی در ثبت اطلاعات رخ داده است. لطفا دوباره سعی کنید';
          $message['msg'][1] = 'danger';
          $this->session->set_flashdata($message);
          redirect("deal/edit/$id");
              }
            }
            if($deal['money_id'] == 1){$money = 'دلار';}else if($deal['money_id'] == 2){$money = 'یورو';}else if($deal['money_id'] == 3){$money = 'یوان';}else{$money = 'درهم';}
            $log['user_id'] = $this->session->userdata('id');
            $log['date_log'] = $date['year']."-".$date['month_num']."-".$date['day'];
            $log['time_log'] = $date['hour'].":".$date['minute'].":".$date['second'];
            $log['activity_id'] = 11;
             $aa = $id + 100;
            $log['explain'] = " شناسه معامله : ".$aa." | نام مشتری :  ".$customer['fullname'] . " | ارز معامله : ". $money . " | تعداد ارز : ".number_format($deal['count_money']).$money ." | کارمزد : ".number_format($deal['wage']).$money . " | نرخ تبدیل : ".number_format($deal['convert_money'])." ریال "." حجم معامله :  ".number_format($deal['volume_deal'])." ریال ";
            $this->base_model->insert_data('log' , $log);
           $message['msg'][0] = 'اطلاعات معامله با موفقیت ثبت شد';
           $message['msg'][1] = 'success';
           $this->session->set_flashdata($message);
           redirect("deal/edit/$id");
            }else{
                $data['deal'] = $this->base_model->get_data_join('deal' , 'customer' , 'deal.* , customer.fullname , customer.id as cust_id ,currency_unit.name' , 'deal.customer_id = customer.id'  ,'row' , array('deal.id' => $id) , NULL , NULL , NULL , array('currency_unit','deal.money_id = currency_unit.id'));
                
                if(sizeof($data['deal']) == 0){
                    show_404();
                }else{
                    $data['bank'] = $this->base_model->get_data('deal_bank' , '*' , 'result' , array('deal_id'=> $id));
                    $header['title'] = ' ویرایش معامله ';
                    $header['active'] = 'deal';
                    $header['active_sub'] = 'deal_archive';
                    $this->load->view('header' , $header);
                    $this->load->view('deal/edit' , $data);
                    $this->load->view('footer');
                }
            }
        }else{
            show_404();
        }
    }
      //----- edit -----//

      //----- photo -----//
      public function photo(){
          $id = $this->uri->segment(3);
          if(isset($id) and is_numeric($id)){
            $header['title'] = 'عکس ها';
            $header['active'] = 'deal';
            $header['active_sub'] = 'deal_archive';
            $data['photo'] = $this->base_model->get_data('deal_pic' , '*' , 'result' , array('deal_id' => $id));
            $this->load->view('header' , $header);
            $this->load->view('deal/photo' , $data);
            $this->load->view('footer');
          }else{
              show_404();
          }

     }
     //----- photo -----//

     //----- handle -----//
    public function handle(){
        $id = $this->uri->segment(3);
        if(isset($id) and is_numeric($id)){
            if(isset($_POST['sub'])){
                $date = $this->convertdate->convert(time());
                $d = $date['year']."-".$date['month_num']."-".$date['day'];
                $t = $date['hour'].":".$date['minute'].":".$date['second'];
                $exp = '';
                for($i = 0 ; $i < sizeof($_POST['customer']) ; $i++){
                    if($_POST['bank_id'][$i] == 0){
                        $message['msg'][0] = 'لطفا شماره حساب را از لیست مربوطه انتخاب کنید . در صورت موجود نبودن شماره حساب لطفا اقدام به افزودن شماره حساب کنید' ;
                        $message['msg'][1] = 'danger';
                        $this->session->set_flashdata($message);
                        redirect("deal/handle/$id");
                     }else{
                    $check = $this->base_model->get_data('customer' , 'id' , 'row' , array('fullname'=> $_POST['customer'][$i]));
                    if($check == FALSE){
                        $customer = array(
                            'fullname'=>$_POST['customer'][$i],
                            'address'=>'',
                            'email'=>'',
                            'customer_tel'=> '',
                        );
                        $customer_id = $this->base_model->insert_data('customer' , $customer);
                    }else{
                       $customer_id = $check->id;
                    }
                    $handle[] = array(
                     'volume_handle'=> htmlspecialchars($_POST['volume_handle'][$i]),
                      'handle_pay' => 0 ,
                      'handle_rest'=> htmlspecialchars($_POST['volume_handle'][$i]),
                      'date_handle' => $d , 
                      'time_handle' => $t , 
                      'date_modified' => '',
                      'customer_id' => $customer_id,
                      'deal_id'=> $id,
                      'bank_id' => htmlspecialchars($_POST['bank_id'][$i])
                    );
                }
                $k = $i + 1;
                $s = $id +100;
                $str .= $k." - نام هماهنگ کننده : ".$_POST['customer'][$i]." | مبلغ هماهنگی : ".$_POST['volume_handle'][$i]." | شناسه معامله : ".$s."</br>"; 
                }
                $res = $this->base_model->insert_batch('deal_handle' , $handle);
                $log['user_id'] = $this->session->userdata('id');
                $log['date_log'] = $d;
                $log['time_log'] = $t;
                $log['activity_id'] = 12;
                $log['explain'] = $str;
                $this->base_model->insert_data('log' , $log);
                if($res == FALSE){
                    $message['msg'][0] = 'مشکلی در ثبت اطلاعات رخ داده است . لطفا دوباره سعی کنید';
                    $message['msg'][1] = 'danger';
                    $this->session->set_flashdata($message);
                    redirect("deal/handle/$id");
                }else{
                    $message['msg'][0] = 'اطلاعات هماهنگی با موفقیت ثبت شد';
                    $message['msg'][1] = 'success';
                    $this->session->set_flashdata($message);
                    redirect("deal/handle/$id");
                }
            }else{
                $header['title'] = 'هماهنگی ها';
                $header['active'] = 'deal';
                $header['active_sub'] = 'deal_archive';
                $data['customer'] = $this->base_model->get_data('customer' ,'fullname' , 'result');
                $data['deal'] = $this->base_model->get_data_join('deal' ,'customer', 'deal.* , customer.fullname ,customer.id as cust_id, currency_unit.name , sum(deal_handle.volume_handle) as vh , sum(deal_handle.handle_rest) as vr' , 'deal.customer_id = customer.id' ,'row'  , array('deal.id'=>$id) , NULL , NULL , NULL , array('currency_unit','deal.money_id = currency_unit.id') , array('deal_handle','deal_handle.deal_id = deal.id'));
                if(sizeof($data['deal']) == 0){
                show_404();
                }else{
                    $data['bank'] = $this->base_model->get_data('deal_bank' , '*' , 'result' , array('deal_id' => $id) , NULL , NULL , array('id' , 'DESC'));
                    $data['select'] = $this->base_model->get_data('deal_bank' , 'id , number_shaba , name_bank' , 'result' , array('deal_id' => $id , 'active' => 1) , NULL , NULL , array('id' , 'DESC'));
                    $data['handle'] = $this->base_model->get_data_join('deal_handle','customer' , 'deal_handle.* , customer.fullname , deal_bank.number_shaba , deal_bank.name_bank','deal_handle.customer_id = customer.id', 'result' , array('deal_handle.deal_id' => $id) , NULL , NULL , array('deal_handle.id' , 'DESC') , array('deal_bank' , 'deal_handle.bank_id = deal_bank.id'));
                    $this->load->view('header' , $header);
                    $this->load->view('deal/handle' , $data);
                    $this->load->view('footer');
                }
     
            }
           
        }else{
            show_404();
        }
    }
    //----- handle -----//

    //----- add_bank -----//
    public function add_bank(){
        $id = $this->uri->segment(3);
        if(isset($id) and is_numeric($id)){
            if(isset($_POST['sub'])){
                $data['explain'] = $this->input->post('bank_explain');
                $data['name_bank'] = $this->input->post('name_bank');
                $data['number_shaba'] = $this->input->post('number_shaba');
                $data['amount'] = $this->input->post('amount_bank');
                $data['pay'] = 0;
                $data['deal_id'] = $id;
                $data['active'] = 1;
                $res = $this->base_model->insert_data('deal_bank' , $data);
                $date = $this->convertdate->convert(time());
                $log['user_id'] = $this->session->userdata('id');
                $log['date_log'] = $date['year']."-".$date['month_num']."-".$date['day'];
                $log['time_log'] = $date['hour'].":".$date['minute'].":".$date['second'];
                $log['activity_id'] = 17;
                $log['explain'] = 'حساب جدید با مشخصات  '.$data['number_shaba']." | ".$data['name_bank']." | مقدار واریزی :  ".$data['amount']." | توضحیات :".$data['explain']." افزوده شد ";
                $this->base_model->insert_data('log' , $log);
                if($res == FALSE){
                    $message['msg'][0] = 'مشکلی در ثبت اطلاعات رخ داده است . لطفا دوباره سعی کنید';
                    $message['msg'][1] = 'danger';
                    $this->session->set_flashdata($message);
                    redirect("deal/handle/$id");
                }else{
                  $message['msg'][0] = 'اطلاعات حساب بانکی با موفقیت ثبت شد';
                  $message['msg'][1] = 'success';
                  $this->session->set_flashdata($message);
                  redirect("deal/handle/$id");
                }
            }else{
                show_404();
            }
        }else{
            show_404();
        }
      }
//----- add_bank -----//

//----- edit_bank -----//
public function show_bank(){
 if(isset($_POST['bank_id'])){
$id = $this->input->post('bank_id');
$bank = $this->base_model->get_data('deal_bank' , '*' , 'row' , array('id'=> $id));
echo json_encode($bank);

 }else{
     show_404();
 }   
}
public function edit_bank(){
    $red_id = $this->uri->segment(3);
    $id = $this->uri->segment(4);
    if(isset($red_id) and isset($id) and is_numeric($red_id) and is_numeric($id)){
        if(isset($_POST['sub'])){
      $data['number_shaba'] = $this->input->post('number_shaba');
      $data['name_bank'] = $this->input->post('name_bank');
      $data['amount'] = $this->input->post('amount_bank');
      $data['explain'] = $this->input->post('bank_explain');
      $res = $this->base_model->update_data('deal_bank' , $data , array('id'=>$id));
      $date = $this->convertdate->convert(time());
      $log['user_id'] = $this->session->userdata('id');
      $log['date_log'] = $date['year']."-".$date['month_num']."-".$date['day'];
      $log['time_log'] = $date['hour'].":".$date['minute'].":".$date['second'];
      $log['activity_id'] = 18;
      $log['explain'] = 'حساب  با مشخصات  '.$data['number_shaba']." | ".$data['name_bank']." | مقدار واریزی :  ".$data['amount']." | توضحیات :".$data['explain']." ویرایش کرد ";
      $this->base_model->insert_data('log' , $log);
      $message['msg'][0] = 'اطلاعات حساب بانکی با موفقیت ویرایش شد';
      $message['msg'][1] = 'success';
      $this->session->set_flashdata($message);
      if($this->uri->segment(5) == 'group'){
          $red = 'handle_profile';
      }else{
          $red = 'handle';
      }
      redirect("deal/$red/$red_id");
        }else{
            show_404();
        }

    }else{
        show_404();
    }
}
//----- edit_bank -----//

//----- active ------//
public function active(){
    $red_id = $this->uri->segment(3);
    $id = $this->uri->segment(4);
    if(isset($red_id) and isset($id) and is_numeric($red_id) and is_numeric($id)){
        $data['active'] = $this->uri->segment(5);
        $this->base_model->update_data('deal_bank' , $data , array('id' => $id));
        $bank = $this->base_model->get_data('deal_bank' , 'name_bank , number_shaba' , 'row' , array('id'=> $id));
        if($data['active'] == 1){$txt = " را فعال کرد ";}else{$txt = ' را غیر فعال کرد ';}
        $date = $this->convertdate->convert(time());
        $log['user_id'] = $this->session->userdata('id');
        $log['date_log'] = $date['year']."-".$date['month_num']."-".$date['day'];
        $log['time_log'] = $date['hour'].":".$date['minute'].":".$date['second'];
        $log['activity_id'] = 19;
        $log['explain'] = "حساب بانکی با مشخصات ".$bank->number_shaba." | ".$bank->name_bank. $txt;
        $this->base_model->insert_data('log' , $log);
        $seg = $this->uri->segment(6);
        if($seg == 'group'){
            $red = 'handle_profile';
        }else{
            $red = 'handle';
        }
        redirect("deal/$red/$red_id");
    }else{
        show_404();
    }
}
//-----active ------//

// -----pay all-----//
public function pay_all(){
    $deal_id = $this->uri->segment(3);
    $id = $this->uri->segment(4);
    if(isset($deal_id) and isset($id) and is_numeric($deal_id) and is_numeric($id)){

        $handle = $this->base_model->get_data_join('deal_handle' , 'deal' , 'deal_handle.handle_pay , deal_handle.handle_rest , deal_handle.bank_id , deal.volume_pay , deal.volume_rest , deal_bank.pay , deal.money_id, currency_unit.amount_unit' , 'deal_handle.deal_id = deal.id' , 'row' , array('deal_handle.id'=> $id) , NULL , NULL , NULL , array('deal_bank','deal_bank.id = deal_handle.bank_id') , array('currency_unit' , 'deal.money_id = currency_unit.id'));
        $date = $this->convertdate->convert(time());
        $history['date_pay'] = $date['year']."-".$date['month_num']."-".$date['day']." ".$date['hour'].":".$date['minute'].":".$date['second'];
        $history['active'] = 1;
        $history['volume'] = $handle->handle_rest;
        $history['handle_id'] = $id;
        $deal['volume_pay'] = $handle->volume_pay + $handle->handle_rest;
        $deal['volume_rest'] = $handle->volume_rest - $handle->handle_rest;
        $deal_handle['handle_rest'] = 0;
        $deal_handle['handle_pay'] = $handle->handle_pay + $handle->handle_rest;
        $deal_bank['pay'] = $handle->pay + $handle->handle_rest;
        $unit['amount_unit'] = $handle->amount_unit + $handle->handle_rest;
        $log['user_id'] = $this->session->userdata('id');
        $log['date_log'] = $date['year']."-".$date['month_num']."-".$date['day'];
        $log['time_log'] = $date['hour'].":".$date['minute'].":".$date['second'];
        $log['activity_id'] = 13;
        $aa = $deal_id +100;
        $log['explain'] = "شناسه معامله : ".$aa . " | حجم پرداخت : ". number_format($handle->handle_rest)." به صورت کامل پرداخت کرد ";
        
        $this->base_model->update_data('deal' , $deal , array('id'=>$deal_id));
        $this->base_model->update_data('deal_handle' , $deal_handle , array('id' => $id));
        $this->base_model->update_data('deal_bank' , $deal_bank , array('id' => $handle->bank_id));
        $this->base_model->update_data('currency_unit' , $unit , array('id'=> $handle->money_id));
        $this->base_model->insert_data('log' , $log);
        $res = $this->base_model->insert_data('handle_history' , $history);
        if($this->uri->segment(5) == 'group'){
        $red = 'handle_profile';
        $red_id = $this->uri->segment(6);
        }else{
          $red = 'handle';
          $red_id = $deal_id;
        }
        if($res == FALSE){
            $message['msg'][0] = 'مشکلی در ثبت اطلاعات رخ داده است . لطفا دوباره سعی کنید';
            $message['msg'][1] = 'danger';
            $this->session->set_flashdata($message);
            redirect("deal/$red/$red_id");
        }else{
          $message['msg'][0] = 'پرداخت به صورت کامل انجام شد';
          $message['msg'][1] = 'success';
          $this->session->set_flashdata($message);
          redirect("deal/$red/$red_id");
        }

    }else{
     show_404();
    }
}
//----pay all -----//
//----pay slice ----//
public function pay_slice(){
    $deal_id = $this->uri->segment(3);
    $id = $this->uri->segment(4);
    if(isset($deal_id) and isset($id) and is_numeric($deal_id) and is_numeric($id)){
        if(isset($_POST['sub'])){
            $handle = $this->base_model->get_data_join('deal_handle','deal','deal_handle.handle_pay,deal_handle.handle_rest,deal_handle.bank_id,deal.volume_pay , deal.volume_rest ,  deal_bank.pay , deal.money_id, currency_unit.amount_unit' , 'deal_handle.deal_id = deal.id' , 'row' , array('deal_handle.id'=> $id) , NULL , NULL , NULL , array('deal_bank','deal_bank.id = deal_handle.bank_id'), array('currency_unit' , 'deal.money_id = currency_unit.id'));
            $slice = $this->input->post('slice');
            $date = $this->convertdate->convert(time());
            $history['date_pay'] = $date['year']."-".$date['month_num']."-".$date['day']." ".$date['hour'].":".$date['minute'].":".$date['second'];
            $history['active'] = 1;
            $history['volume'] = $slice;
            $history['handle_id'] = $id;
            $deal_handle['handle_rest'] = $handle->handle_rest - $slice;
            $deal_handle['handle_pay'] = $handle->handle_pay + $slice;
            $deal['volume_pay'] = $handle->volume_pay + $slice;
            $deal['volume_rest'] = $handle->volume_rest - $slice;
            $deal_bank['pay'] = $handle->pay + $slice;
            $unit['amount_unit'] = $handle->amount_unit + $slice;
            $log['user_id'] = $this->session->userdata('id');
            $log['date_log'] = $date['year']."-".$date['month_num']."-".$date['day'];
            $log['time_log'] = $date['hour'].":".$date['minute'].":".$date['second'];
            $log['activity_id'] = 14;
            $aa = $deal_id +100;
            $log['explain'] = "شناسه معامله : ".$aa . " | حجم پرداخت : ". number_format($slice)." به صورت جزیی پرداخت کرد ";
            $this->base_model->update_data('deal' , $deal , array('id' => $deal_id));
            $this->base_model->update_data('deal_handle' , $deal_handle , array('id' => $id));
            $this->base_model->update_data('deal_bank' , $deal_bank , array('id' => $handle->bank_id));
            $this->base_model->update_data('currency_unit' , $unit , array('id'=> $handle->money_id));
            $this->base_model->insert_data('log' , $log);
            $res = $this->base_model->insert_data('handle_history' , $history);
            if($this->uri->segment(5) == 'group'){
                $red = 'handle_profile';
                $red_id = $this->uri->segment(6);
            }else{
                $red = 'handle' ;
                $red_id = $deal_id;
            }
            if($res == TRUE){
                $message['msg'][0] = 'پرداخت با موفقیت انجام شد';
                $message['msg'][1] = 'success';
                $this->session->set_flashdata($message);
                redirect("deal/$red/$red_id");
            }else{
                $message['msg'][0] = 'مشکلی در روند عملیات رخ داده است . لطفا دوباره سعی کنید';
                $message['msg'][1] = 'danger';
                $this->session->set_flashdata($message);
                redirect("deal/$red/$red_id");
            }
    
            }else{
                show_404();
            }
    }else{
        show_404();
    }

}
//-----pay slice-----//

// ----history------//
public function get_history(){
    if(isset($_POST['handle_id'])){
       $handle_id = $this->input->post('handle_id');
       $history = $this->base_model->get_data('handle_history' , 'handle_history.*' , 'result' , array('handle_id'=> $handle_id , 'active'=> 1));
       echo json_encode($history);
    }else{
        show_404();
    }
}
public function restore(){
    $id = $this->uri->segment(3);
    $deal_id = $this->uri->segment(4);
    if(isset($id) and isset($deal_id) and is_numeric($id) and is_numeric($deal_id)){
        $res = $this->base_model->get_data('handle_history' , 'handle_id ,volume' , 'row' , array('id' => $id));
        $handle_id = $res->handle_id;
        $restore = $res->volume;
        $store = $this->base_model->get_data_join('deal_handle' , 'deal' , 'deal_handle.handle_pay , deal_handle.handle_rest , deal.volume_pay , deal.volume_rest , deal_bank.pay , deal_handle.bank_id  , deal.money_id, currency_unit.amount_unit' , 'deal_handle.deal_id = deal.id' , 'row' , array('deal_handle.id' => $handle_id) , NULL , NULL , NULL , array('deal_bank' , 'deal_bank.id = deal_handle.bank_id'), array('currency_unit' , 'deal.money_id = currency_unit.id'));
       $bank_id = $store->bank_id;
       $handle['handle_pay'] = $store->handle_pay - $restore;
       $handle['handle_rest'] = $store->handle_rest + $restore;
       $deal['volume_pay'] = $store->volume_pay - $restore;
       $deal['volume_rest'] = $store->volume_rest + $restore;
       $bank['pay'] = $store->pay - $restore;
       $unit['amount_unit'] = $store->amount_unit - $restore;
       $history['active'] = 0;
       $date = $this->convertdate->convert(time());
       $log['user_id'] = $this->session->userdata('id');
       $log['date_log'] = $date['year']."-".$date['month_num']."-".$date['day'];
       $log['time_log'] = $date['hour'].":".$date['minute'].":".$date['second'];
       $log['activity_id'] = 15;
       $aa = $deal_id + 100;
       $log['explain'] = "شناسه معامله : ".$aa." | مبلغ پرداخت : ".number_format($restore)." را باز گردانید ";
       $this->base_model->update_data('handle_history' , $history , array('id' => $id));
       $this->base_model->update_data('deal_handle', $handle , array('id' => $handle_id));
       $this->base_model->update_data('deal' , $deal , array('id' => $deal_id));
       $this->base_model->update_data('currency_unit' , $unit , array('id'=> $store->money_id));
       $this->base_model->insert_data('log' , $log);
       $res = $this->base_model->update_data('deal_bank' , $bank , array('id'=> $bank_id));
       if($this->uri->segment(5) == 'group'){
        $red = 'handle_profile';
        $red_id = $this->uri->segment(6);
    }else{
        $red = 'handle' ;
        $red_id = $deal_id;
    }
    if($res == TRUE){
        $message['msg'][0] = 'مبلغ مورد نظر با موفقیت بازگرداننده شد';
        $message['msg'][1] = 'success';
        $this->session->set_flashdata($message);
        redirect("deal/$red/$red_id");
    }else{
        $message['msg'][0] = 'مشکلی در روند عملیات رخ داده است . لطفا دوباره سعی کنید';
        $message['msg'][1] = 'danger';
        $this->session->set_flashdata($message);
        redirect("deal/$red/$red_id");
    }
       
    }else{
        show_404();
    }
}
// -----history------//
    public function search_deal(){
        if(isset($_POST['deal_id']) and isset($_POST['customer_id']) and is_numeric($_POST['deal_id']) and is_numeric($_POST['customer_id'])){
            $deal_id = $this->input->post('deal_id');
            $customer_id = $this->input->post('customer_id');
            $data = $this->base_model->get_data_join('deal' ,'customer', 'deal.* , customer.fullname ,currency_unit.name' , 'deal.customer_id = customer.id' ,'result'  , array('deal.customer_id'=> $customer_id , 'deal.id'=>$deal_id), NULL , NULL , NULL , array('currency_unit','deal.money_id = currency_unit.id'));
            echo json_encode($data);
        }else{
            show_404();
        }
        } 
    public function handle_profile(){
        $id = $this->uri->segment(3);
        if(isset($id) and is_numeric($id)){
            if(isset($_POST['sub'])){
                $date = $this->convertdate->convert(time());
                $d = $date['year']."-".$date['month_num']."-".$date['day'];
                $t = $date['hour'].":".$date['minute'].":".$date['second'];
                $str = '';
                for($i = 0 ; $i < sizeof($_POST['customer']) ; $i++){
                    if($_POST['bank_id'][$i] == 0){
                        $message['msg'][0] = 'لطفا شماره حساب را از لیست مربوطه انتخاب کنید . در صورت موجود نبودن شماره حساب لطفا اقدام به افزودن شماره حساب کنید' ;
                        $message['msg'][1] = 'danger';
                        $this->session->set_flashdata($message);
                        redirect("deal/handle_profile/$id");
                     }else{
                        $a = $_POST['deal_id'][$i] - 100;
                        $check_deal = $this->base_model->get_data('deal' , 'id' , 'row' , array('id' => $a , 'customer_id'=>$id));
                        if(sizeof($check_deal) == 0){
                            $message['msg'][0] = 'لطفا در انتخاب معامله دقت فرمایید که شناسه معامله باید مربوط به معاملات آن شخص باشد';
                            $message['msg'][1] = 'danger';
                            $this->session->set_flashdata($message);
                            redirect("deal/handle_profile/$id");
                        }
                    $check = $this->base_model->get_data('customer' , 'id' , 'row' , array('fullname'=> $_POST['customer'][$i]));
                    if($check == FALSE){
                        $customer = array(
                            'fullname'=>$_POST['customer'][$i],
                            'address'=>'',
                            'email'=>'',
                            'customer_tel'=> '',
                        );
                        $customer_id = $this->base_model->insert_data('customer' , $customer);
                    }else{
                       $customer_id = $check->id;
                    }
                    $handle[] = array(
                     'volume_handle'=> htmlspecialchars($_POST['volume_handle'][$i]),
                      'handle_pay' => 0 ,
                      'handle_rest'=> htmlspecialchars($_POST['volume_handle'][$i]),
                      'date_handle' => $d , 
                      'time_handle' => $t , 
                      'date_modified' => '',
                      'customer_id' => $customer_id,
                      'deal_id'=> $a,
                      'bank_id' => htmlspecialchars($_POST['bank_id'][$i])
                    );
                }
                $k = $i + 1;
                $s = $_POST['deal_id'][$i];
                $str .= $k." - نام هماهنگ کننده : ".$_POST['customer'][$i]." | مبلغ هماهنگی : ".$_POST['volume_handle'][$i]." | شناسه معامله : ".$s."</br>"; 
                }
                $res = $this->base_model->insert_batch('deal_handle' , $handle);
                $log['user_id'] = $this->session->userdata('id');
                $log['date_log'] = $d;
                $log['time_log'] = $t;
                $log['activity_id'] = 12;
                $log['explain'] = $str;
                $this->base_model->insert_data('log' , $log);
                if($res == FALSE){
                    $message['msg'][0] = 'مشکلی در ثبت اطلاعات رخ داده است . لطفا دوباره سعی کنید';
                    $message['msg'][1] = 'danger';
                    $this->session->set_flashdata($message);
                    redirect("deal/handle_profile/$id");
                }else{
                    $message['msg'][0] = 'اطلاعات هماهنگی با موفقیت ثبت شد';
                    $message['msg'][1] = 'success';
                    $this->session->set_flashdata($message);
                    redirect("deal/handle_profile/$id");
                }
            }else{
                $header['title'] = 'هماهنگی';
                $header['active'] = 'deal';
                $header['active_sub'] = 'deal_archive';
                $total_rows = $this->base_model->get_count("deal" , array('customer_id' => $id));
                $config['base_url'] = base_url("deal/handle_profile/$id");
                $config['total_rows'] = $total_rows;
                $config['per_page'] = '5';
                $config["uri_segment"] = '4';
                $config['num_links'] = '5';
                $config['next_link'] = '<i class="icon-arrow-left5"></i>';
                $config['last_link'] = '<i class="icon-backward2"></i>';
                $config['prev_link'] = '<i class="icon-arrow-right5"></i>';
                $config['first_link'] = '<i class="icon-forward3"></i>';
                $config['full_tag_open'] = '<nav><ul class="pagination pagination-sm">';
                $config['full_tag_close'] = '</ul></nav>';
                $config['cur_tag_open'] = '<li class="active"><a href="javascript:void(0)">';
                $config['cur_tag_close'] = '</a></li>';
                $config['num_tag_open'] = '<li>';
                $config['num_tag_close'] = '</li>';
                $config['next_tag_open'] = '<li>';
                $config['next_tag_close'] = '</li>';
                $config['last_tag_open'] = '<li>';
                $config['last_tag_close'] = '</li>';
                $config['first_tag_open'] = '<li>';
                $config['first_tag_close'] = '</li>';
                $config['prev_tag_open'] = '<li>';
                $config['prev_tag_close'] = '</li>';
                $config['suffix'] = "";
            $this->pagination->initialize($config);
            $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;      
            $data['deal'] = $this->base_model->get_data_join('deal' ,'customer', 'deal.* , customer.fullname ,currency_unit.name' , 'deal.customer_id = customer.id' ,'result'  , array('deal.customer_id'=> $id), $config['per_page'] , $page , array('deal.id' , 'DESC') , array('currency_unit','deal.money_id = currency_unit.id'));
            $data['page'] = $this->pagination->create_links();
            if(sizeof($data['deal']) == 0){
                show_404();
            }else{
            $data['bank'] = $this->base_model->get_data_join('deal' , 'deal_bank'  ,'deal.id as deal_id , deal_bank.*','deal.id = deal_bank.deal_id' ,'result' ,array('deal.customer_id' => $id) , NULL , NULL , array('deal_bank.id', 'DESC'));
            $data['select2'] = $this->base_model->get_data_join('deal' , 'deal_bank' ,'deal_bank.id , deal_bank.number_shaba , deal_bank.name_bank , deal.id as deal_id','deal.id = deal_bank.deal_id' , 'result' , array('deal.customer_id' => $id , 'active' => 1) , NULL , NULL , array('deal_bank.id' , 'DESC'));  
            $data['customer'] = $this->base_model->get_data('customer' ,'fullname' , 'result');           
            $data['handle'] = $this->base_model->get_data_join('deal_handle' , 'deal' , 'deal_handle.* , customer.fullname , deal_bank.name_bank , deal_bank.number_shaba' , 'deal_handle.deal_id = deal.id' , 'result' , array('deal.customer_id'=>$id) , NULL , NULL , array('deal_handle.id' , 'DESC'),array('customer' , 'deal_handle.customer_id = customer.id') , array('deal_bank' , 'deal_handle.bank_id = deal_bank.id'));  
            $this->load->view('header' , $header);
                $this->load->view('deal/handle_profile' , $data);
                $this->load->view('footer');
            }
        }
        }else{
            show_404();
        }

    }
}

/* End of file Controllername.php */

?>