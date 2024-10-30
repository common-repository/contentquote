<?php 

add_action( 'admin_menu', 'cq_menu' );

//keep in mind feedback area may not be wordpress standards and may not work w/ future wordpress versions
//need to get a more "wordpress kosher" function for it soon.  

function cq_menu() {
	add_options_page( 'Content Quote Options', 'Content Quote', 'manage_options', 'contentquote', 'cq_options_page' );
}

function cq_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<p>Here is where the form would go if I actually had options.</p>';
	echo '</div>';
}

function cq_options_page() {
?>
<div>
<h2>Content Quote Settings</h2>

<?php 
	$kinvey_url = plugins_url( 'contentquote/feedback.php'); 
?>
<form action="options.php" method="post">
<?php settings_fields('cq_options'); ?>
<?php do_settings_sections('plugin'); ?>

<input name="Submit" type="submit" value="Save Changes" />

</form></div>

<?php 
if(isset($_POST))
{
	if(!empty($_POST['cq-improve']) || !empty($_POST['cq-like']) )
	{
		send_feedback_email($_POST['cq-improve'],$_POST['cq-like'],$_POST['cq-email-follow'],$_POST['cq-site-name']);
	}
}
?>
<form action="<?= $_SERVER['PHP_SELF'] ?>?page=<?= $_GET['page'] ?>" method="post">

<?php 
if(isset($_POST))
{
	if(!empty($_POST['cq-improve']) || !empty($_POST['cq-like']) )
	{
		echo '<h2>Thank you for your feedback!</h2>';
	}
}
?>


<h2>Help us make contentquote better.  </h2>
<p>What can we improve with the next version of contentquote?</p>
<textarea name="cq-improve"></textarea>

<p>What do you like about contentquote?</p>
<textarea name="cq-like"></textarea>

<p>Can you email you with follow up questions?</p>
<input type="textbox" name="cq-email-followup" />
<input type="hidden" name="cq-site-name" value="<?= $_SERVER['SERVER_NAME'] ?>" />
<br />
<input name="feedback-submit" type="submit" value="Sed Feedback" />

<?php
}


add_action('admin_init', 'plugin_admin_init');

function plugin_admin_init(){

register_setting( 'cq_options', 'cq_options', 'cq_options_validate' );

add_settings_section('plugin_main', 'Main Settings', 'plugin_section_text', 'plugin');

add_settings_field('plugin_tweet_checkbox', 'Create Tweet Buttons From Content Shared on Twitter ', 'plugin_setting_string', 'plugin', 'plugin_main');
add_settings_field('plugin_text_string', 'Create Tweet Buttons From Blockquotes In Your Blog', 'plugin_tq_tweet_checkbox', 'plugin', 'plugin_main');

}

function plugin_section_text() {
	echo '<p>When should we create tweet buttons and blockquotes? </p>';
}


function plugin_setting_string() {
	$options = get_option('cq_options');
	// echo "<checkbox name='cq_options[twitter_quotes]'' id='twitter_quotes'></option>";
	echo "<input type='checkbox' name='cq_options[blockquote_checkbox]' value='1'";
	if($options['blockquote_checkbox'] == 1)
	{
		echo "checked";
	}
	echo " /> Create Tweet Buttons from text I put in blockquotes in my blog "; 

} 

function plugin_tq_tweet_checkbox() {
	$options = get_option('cq_options');
	// echo "<checkbox name='cq_options[twitter_quotes]'' id='twitter_quotes'></option>";
	echo "<input type='checkbox' name='cq_options[tweet_checkbox]' value='1'";
	if($options['tweet_checkbox'] == 1)
	{
		echo "checked";
	}
	echo " /> Create Tweet Buttons from Tweets that quote my blog"; 

//	echo "<input id='plugin_tweet_checkbox' name='cq_options[tweet_checkbox]' size='40' type='text' value='{$options['tweet_checkbox']}' />";
} 


function cq_options_validate($input) {

	// $newinput['text_string'] = trim($input['text_string']);
	// if(!preg_match('/^[a-z0-9]{32}$/i', $newinput['text_string'])) {
	// 	$newinput['text_string'] = '';
	// }
	return $input;
}




function send_feedback_email($improve,$like,$email,$site)
{
$message = 'Things To Improve:  ' . $improve;
$message .= '

';
$message .= 'Things The User Likes:  ' . $like;

$message .= '

';

$message .= 'Website:  ' . $site;

$message .= '

';
$message .= 'Contact Information:  ' . $email;

$subject = 'New Feedback on Content Quote';

wp_mail('sanjith@contentquote.com', $subject, $message);
}

?>