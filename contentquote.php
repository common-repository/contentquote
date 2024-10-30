<?php
/*
Plugin Name: Content Quote
Plugin URI: http://contentquote.com
Description: Creates easy to tweet buttons of each post's most quotable content.  
Version: 0.5 
Author: Bob Cavezza and Sanjith
Author URI: http://ibuildmvps.com
License: n/a
*/

include('simple_html_dom.php'); 
include('contentquote_menu.php');

register_activation_hook( __FILE__, 'contentquote_activate' );

function contentquote_activate()
{

	$kinvey_url = plugins_url( 'kinvey-js-0.9.6.min.js', __FILE__ );
	$cq_js_url = plugins_url('content_quote.js', __FILE__);
	if(!stripos($kinvey_url, 'contentquote'))
	{
		$kinvey_url = plugins_url( 'contentquote/kinvey-js-0.9.6.min.js', __FILE__ );
		$cq_js_url = plugins_url('contentquote/content_quote.js', __FILE__);
	}


	$plugin_options = array(
		'tweet_checkbox' => '1',
		'blockquote_checkbox' => '1'
	);
	add_option('cq_options',$plugin_options);
	update_option('cq_options',$plugin_options);

	$kinvey_options = get_option('cq_kinvey_options');
	if(empty($kinvey_options))
	{

		$site_user_name = site_url(); 
		$site_user_name = str_replace('-','',str_replace('.', '', str_replace("_","-",str_replace(".","-",str_replace(":","-",str_replace("/","-",$site_user_name))))));

		$daily_check = $site_user_name . '-dailycheck';
		$collection_name = $site_user_name;

		$site_password = md5($site_user_name+time()+'23'); //random salt value. 

		$kinvey_options = array(
			'user_name' => $site_user_name,
			'collection_name' => $collection_name,
			'password' => $site_password,
			'daily_check' => $daily_check
		);
		add_option('cq_kinvey_options', $kinvey_options);
		unset($kinvey_options);

		$kinvey_options = get_option('cq_kinvey_options');


		$create_user_fields_string = '{
		  "username": "' . $kinvey_options['user_name'] . '",
		  "password": "' . $kinvey_options['password'] . '"
		}';

		$kinvey_create_user_url = 'https://baas.kinvey.com/user/kid2146/';
		$kinvey_create_daily_check_url = 'https://baas.kinvey.com/appdata/kid2146/' . $kinvey_options['daily_check'] . '/';
		$kinvey_create_collection_name_url = 'https://baas.kinvey.com/appdata/kid2146/' . $kinvey_options['collection_name'] . '/';

    $create_daily_check_fields_string = '{
      "date": "1"
    }'; 

    $create_collection_name_fields_string = '{
      "page_url": "1",
      "quote": "1",
      "tweet_id": "1",
      "twitter_user_handle": "1",
      "twitter_user_id": "1"
    }'; 

		kinvey_create_user_api_call($kinvey_create_user_url, $create_user_fields_string); 
		kinvey_create_collection($kinvey_create_collection_name_url, $create_collection_name_fields_string);
		kinvey_create_collection($kinvey_create_daily_check_url, $create_daily_check_fields_string );


	}
	else
	{

	}

	$site_location =  $_SERVER['SERVER_NAME'];
	 wp_mail('sanjith@contentquote.com','contentquote activated by ' . $site_location,$site_location . ' activated contentquote just now');
}

add_filter('the_content', 'the_tweetable_content');

function the_tweetable_content($content)
{
	$kinvey_url = plugins_url( 'kinvey-js-0.9.6.min.js', __FILE__ );
	$cq_js_url = plugins_url('content_quote.js', __FILE__);
	$secure_data_url = plugins_url('get_secure_data.php', __FILE__);
	if(!stripos($kinvey_url, 'contentquote'))
	{
		$kinvey_url = plugins_url( 'contentquote/kinvey-js-0.9.6.min.js', __FILE__ );
		$cq_js_url = plugins_url('contentquote/content_quote.js', __FILE__);
		$secure_data_url = plugins_url('contentquote/get_secure_data.php', __FILE__);
		}



	$options = get_option('cq_options');

	if($options['tweet_checkbox'] == 1 || $options['blockquote_checkbox'] == 1)
	{
		echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>';		
		echo '<script async src="' . $kinvey_url . '"></script>';
	}

	if($options['tweet_checkbox'] == 1)
	{

		?> 

		<script type="text/javascript">
		var site_url = '<?= site_url() ?>';
		var cq_js_url = '<?= $cq_js_url ?>';
		var get_secure_data_url = '<?= $secure_data_url ?>';
		(function()
			{
				var ga=document.createElement('script');
				ga.type='text/javascript';
				ga.async=true;
				ga.src=(cq_js_url);
				var s=document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(ga,s);
			}
		)();
		</script>
		<?php 

	}
	if($options['blockquote_checkbox'] == 1)
	{

		$html = str_get_html($content); 

		if(!is_front_page() && get_post_type() == 'post')
		{
			$quote_array = array(); 
			$quote_position = array(); 

			foreach($html->find('blockquote') AS $quote)
			{
				if(strlen($quote->plaintext) < 120)
				{
					$quote_array[] = $quote->innertext; 
					$quote_position[] = stripos($content,$quote->innertext);
				}
			}

			$replacement_text = array(); 

			global $post; 


			$position_count = 0; //
			foreach($quote_array AS $key => $value)
			{
				$total_position = $quote_position[$key] + strlen($value) + strlen('</blockquote>'); 

				if(substr($content,$total_position,1) == '>')
				{
					$total_position+=1;
				}
				if(substr($content,$total_position,1) == '<')
				{
					$total_position-=1;
				}

				$tweet_url = get_permalink($post->ID); 

				$value = strip_tags($value); 

				$tweet_js = '<a href="https://twitter.com/intent/tweet?button_hashtag=Quote&text=' . $value . '" 
					class="twitter-hashtag-button" data-size="large" data-url="' . $tweet_url . '">Tweet #Quote</a>
						<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];
							if(!d.getElementById(id)){js=d.createElement(s);
							js.id=id;js.src="//platform.twitter.com/widgets.js";
							fjs.parentNode.insertBefore(js,fjs);
							}}(document,"script","twitter-wjs");
						</script>';

				$content = substr_replace($content, $tweet_js, $total_position,0); 

			}

		}
	}

	return $content; 

}

function kinvey_create_user_api_call($kinvey_url, $fields_string,$kinvey_username = 'kid2146',$kinvey_password = 'ebfe51e14ef54982bc2051f5081576ab')
{
  //open connection
  $ch = curl_init();

  //set the url, number of POST vars, POST data
  curl_setopt($ch,CURLOPT_URL,$kinvey_url);
  //curl_setopt($ch,CURLOPT_POST,count($fields));
  curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
  //curl_setopt($ch,CURLOPT_HTTPHEADER,array());
  curl_setopt($ch, CURLOPT_USERPWD, $kinvey_username . ":" . $kinvey_password);  

  curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
      'Content-Type: application/json',      
  		'Host: baas.kinvey.com',
  		'POST /user/:appKey/ HTTP/1.1'
  ));        

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
  curl_setopt($ch, CURLOPT_HEADER, 1);

  //execute post
  $result = curl_exec($ch);

  //close connection
  curl_close($ch);
}



function kinvey_create_collection($kinvey_url, $fields_string,$kinvey_username = 'kid2146',$kinvey_password = 'ebfe51e14ef54982bc2051f5081576ab')
{
  //open connection
  $ch = curl_init();

  //set the url, number of POST vars, POST data
  curl_setopt($ch,CURLOPT_URL,$kinvey_url);
  //curl_setopt($ch,CURLOPT_POST,count($fields));
  curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
  //curl_setopt($ch,CURLOPT_HTTPHEADER,array());
  curl_setopt($ch, CURLOPT_USERPWD, $kinvey_username . ":" . $kinvey_password);  

  curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
      'Content-Type: application/json',      
  		'Host: baas.kinvey.com',
  		'POST /appdata/:appKey/ HTTP/1.1'
  ));        

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_HEADER, 1);

  //execute post
  $result = curl_exec($ch);

  //close connection
  curl_close($ch);

  return TRUE; 
}




?>