<?php
class Messages extends Kiel_Controller{

	/**
	* == Login for users ==
	* 
	* @param 		username
	* @param 		password
	* @author 		Ninz Eclarin |  nreclarin@gmail.com
	* @return 		user_id
	* @version 		Version 1.0
	* 
	*/
	public function feed_get()
	{
		$this->load_model('feed_model');
		$res = $this->feed_model->get_messages();
		$this->response(array('status'=>'Success','data'=>$res),200);
	}

	public function feed_item_get()
	{
		if($this->get_args['message_id'] && isset($this->get_args['message_id']) && $this->get_args['message_id'] !== NULL){
			$this->load_model('feed_model');
			$res = $this->feed_model->single_item($this->get_args['message_id']);
			$this->response(array('status'=>'Success','data'=>$res),200);
		}
	}

	public function feed_post()
	{
		$this->load_model('feed_model');
		$user_no = $this->post_args['user_number'];
		$addr = $this->post_args['address'];
		$name = $this->post_args['name'];
		$message = $this->post_args['message'];
		$this->feed_model->add_messages($user_no,$addr,$name,$message);	
	}


	public function search_get()
	{
		if($this->get_args['q'] && isset($this->get_args['q']) && $this->get_args['q'] != ""){
			$this->load_model('feed_model');
			$res = $this->feed_model->search($this->get_args['q']);
			$this->response(array('status'=>'Success','data'=>$res),200);

		} else {

		}
	}
	public function feed_callback_post()
	{
		$this->load_model('feed_model');

		/*********MESSAGE PART******************/
		$xml = simplexml_load_file('php://input');
		$sms = array();
		$nodes = $xml->xpath('/message/param');

		foreach($nodes as $node) {
		   $param = (array) $node;
		   $sms[$param['name']] = $param['value'];
		}

		if($sms['messageType'] == 'SMS') {
		   $user_no = $sms['source'];
		   $smsMsg = $sms['msg'];
		} else{
		   die("Invalid message type");
		}


		/*********MESSAGE PART******************/
		
		$msg_arr = explode('/',$smsMsg);
		if(count($msg_arr) === 3){
			$addr = $msg_arr[0];
			$name = $msg_arr[1];
			$message = $msg_arr[2];

			$res = $this->feed_model->add_messages($user_no,$addr,$name,$message);
		} else if(count($msg_arr) === 2){
			$addr = $msg_arr[0];
			$message = $msg_arr[1];

			$res = $this->feed_model->add_messages($user_no,$addr,null,$message);
		} else {
			if(trim($smsMsg) !== ""){
				$message = $msg_arr[1];
				$res = $this->feed_model->add_messages($user_no,null,null,$message);
			}
		}
		if($res){
			$this->sns_crosspost($message);
		}
	}
	
	private function sns_crosspost($message)
	{
		$params['facebook_access_token'] = 'CAADDaNqhbVgBAKfxPjjLkCcmmCWaOVRZAkY5JSpLIZBziZCRxix9QgNCR1IZCu0JgWyZCPqbwdwHjlZC1U6ovSg6oGIcB0BK8KUlrT9GdtA3IDAocZCZC7ko3sK6rfN1ZAXSe6sZBIxqS27Fkch2WO788yvxIY0VsFw2V8gEwiCZCBCrKD5JmXV90bpaxE1ZB8IuHQgZD';
		$params['twitter_access_token'] = '2190619520-lmj8aeP0mjXFWOH8feFGA144qaBPJMLjlbAy7kF';
		$params['twitter_access_secret'] = '2SO03jgYn31wJEZyXkaQI48MfX56Ktbo8fM7G2URiFfUB';
		$params['place'] = '454373604683875';
		$params['message'] = $message;
		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, 'http://api.buzzboarddev.stratpoint.com/posts/v1/fb_post');
		curl_setopt($ch,CURLOPT_POST, 3);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $params);

		//execute post
		$result = curl_exec($ch);
		//close connection
		curl_close($ch);
		$this->response(array('status'=>'Success'),200);
	}
}

?>

