<?php
/*
Plugin Name: BBHide
Plugin URI: https://wordpress.org/plugins/bbhide/
Description: Implement the classic forum bbcode [hide] for WordPress sites.
Version: 1.00
Author: del
Author URI: https://profiles.wordpress.org/flector#content-plugins
Text Domain: bbhide
*/ 

function bbhide_init() {
    $bbhide_options = array(); bbhide_setup();
    $bbhide_options['hide-days'] = __('To view hidden content you must have been registered on the site for at least %days% (you have currently been registered for %your_days%).','bbhide');
    $bbhide_options['hide-comments'] = __('To view hidden content you must have at least %comments% (you currently have %your_comments%).','bbhide');
    $bbhide_options['hide-all'] = __('To view hidden content you must have been registered on the site for at least %days% (you have currently been registered for %your_days%) and have at least %comments% (you currently have %your_comments%).','bbhide');
    add_option('bbhide_options', $bbhide_options);
}
add_action('activate_bbhide/bbhide.php', 'bbhide_init');

function bbhide_setup(){
    load_plugin_textdomain('bbhide');
}
add_action('init', 'bbhide_setup');

function bbhide_actions($links) {
	return array_merge(array('settings' => '<a href="options-general.php?page=bbhide.php">' . __('Settings', 'bbhide') . '</a>'), $links);
}
add_filter('plugin_action_links_' . plugin_basename( __FILE__ ),'bbhide_actions');


function bbhide_files($hook_suffix) {
	$purl = plugins_url('', __FILE__);
	
    if ( is_admin() && $hook_suffix == 'settings_page_bbhide' ) {
    
    wp_register_script('bbhide-lettering', $purl . '/inc/jquery.lettering.js');  
    wp_register_script('bbhide-textillate', $purl . '/inc/jquery.textillate.js');  
	wp_register_style('bbhide-animate', $purl . '/inc/animate.min.css');
	
	if(!wp_script_is('jquery')) {wp_enqueue_script('jquery');}
    wp_enqueue_script('bbhide-lettering');
    wp_enqueue_script('bbhide-textillate');
    wp_enqueue_style('bbhide-animate');
    
    }
}
add_action('admin_enqueue_scripts', 'bbhide_files');

function bbhide_files2() {
	$purl = plugins_url('', __FILE__);
	
	wp_register_style('bbhide', $purl . '/inc/bbhide.css');
	wp_enqueue_style('bbhide');
}
add_action('wp_enqueue_scripts', 'bbhide_files2');

function bbhide_shortcode($atts, $content) {
	extract(shortcode_atts(array(
		'days' => '',
		'comments' => '',
        'style' => 'green'
	), $atts));

    global $current_user;
    global $wpdb;
    $output = '';

    wp_get_current_user();
    $userid = $current_user->ID;
    $comment_count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) AS total FROM $wpdb->comments WHERE comment_approved = 1 AND user_id = %s", $userid ) );
    $registered = $current_user->user_registered;
    $now = time(); 
    $your_date = strtotime($registered);
    $datediff = $now - $your_date;
    $temp = floor($datediff/(60*60*24));
    if ($userid==NULL) {
        $comment_count=0;
        $temp=0;    
    }
    
    $comments = (int)$comments;
    $days = (int)$days;
    if ($comments=='' and $days=='') {
       $comments = 10;
       $days = 10;
    }
    
    $bbhide_options = get_option('bbhide_options');
    
    $text_days = $bbhide_options['hide-days'];
    $text_days = str_replace("%days%", sprintf( _n( '<strong>%s</strong> days', '<strong>%s</strong> days', $days, 'bbhide' ), $days ), $text_days);
    $text_days = str_replace("%your_days%", sprintf( _n( '<strong>%s</strong> days', '<strong>%s</strong> days', $temp, 'bbhide' ), $temp ), $text_days);
    
    $text_comments = $bbhide_options['hide-comments'];
    $text_comments = str_replace("%comments%", sprintf( _n( '<strong>%s</strong> comment', '<strong>%s</strong> comments', $comments, 'bbhide' ), $comments ), $text_comments);
    $text_comments = str_replace("%your_comments%", sprintf( _n( '<strong>%s</strong> comment', '<strong>%s</strong> comments', $comment_count, 'bbhide' ), $comment_count ), $text_comments);
    
    $text_all = $bbhide_options['hide-all'];
    $text_all = str_replace("%comments%", sprintf( _n( '<strong>%s</strong> comment', '<strong>%s</strong> comments', $comments, 'bbhide' ), $comments ), $text_all);
    $text_all = str_replace("%days%", sprintf( _n( '<strong>%s</strong> days', '<strong>%s</strong> days', $days, 'bbhide' ), $days ), $text_all);
    $text_all = str_replace("%your_days%", sprintf( _n( '<strong>%s</strong> days', '<strong>%s</strong> days', $temp, 'bbhide' ), $temp ), $text_all);
    $text_all = str_replace("%your_comments%", sprintf( _n( '<strong>%s</strong> comment', '<strong>%s</strong> comments', $comment_count, 'bbhide' ), $comment_count ), $text_all);
    
    
    
    if ($comments!='' and $days!='') {
        if ($comment_count<$comments or $temp<$days) {
            $output  = "\n<div class=\"hide {$style}\">\n";
            $output .= wpautop($text_all);
            $output .= "\n</div>\n";
        }
        else {
            $output .= wpautop(do_shortcode($content));
        }
    }
    if ($comments!='' and $days=='') {
        if ($comment_count<$comments) {
            $output  = "\n<div class=\"hide {$style}\">\n";
            $output .= wpautop($text_comments);
            $output .= "\n</div>\n";
        }
        else {
            $output .= wpautop(do_shortcode($content));
        }
    }
    if ($comments=='' and $days!='') {
        if ($temp<$days) {
            $output  = "\n<div class=\"hide {$style}\">\n";
            $output .= wpautop($text_days);
            $output .= "\n</div>\n";
        }
        else {
            $output .= wpautop(do_shortcode($content));
        }
    }
    
	return $output;
}
add_shortcode ('hide', 'bbhide_shortcode');


function bbhide_options_page() {
$purl = plugins_url('', __FILE__);

if (isset($_POST['submit'])) {
    
if ( ! wp_verify_nonce( $_POST['bbhide_nonce'], plugin_basename(__FILE__) ) || ! current_user_can('edit_posts') ) {
   wp_die(__( 'Cheatin&#8217; uh?' ));
}     
        
    $bbhide_options['hide-days'] = esc_attr($_POST['hide-days']);
    $bbhide_options['hide-comments'] = esc_attr($_POST['hide-comments']);
    $bbhide_options['hide-all'] = esc_attr($_POST['hide-all']);
    
    update_option('bbhide_options', $bbhide_options);
    
}
$bbhide_options = get_option('bbhide_options');
?>
<?php   if (!empty($_POST)) :
if ( ! wp_verify_nonce( $_POST['bbhide_nonce'], plugin_basename(__FILE__) ) || ! current_user_can('edit_posts') ) {
   wp_die(__( 'Cheatin&#8217; uh?' ));
} 
?>
<div id="message" class="updated fade"><p><strong><?php _e('Options saved.', "bbhide") ?></strong></p></div>
<?php endif; ?>
<div class="wrap">
<h2><?php _e('BBHide Settings', 'bbhide'); ?></h2>

<div class="metabox-holder" id="poststuff">
<div class="meta-box-sortables">

<?php $lang = get_locale(); ?>
<?php if ($lang != "ru_RU") { ?>
<div class="postbox">

    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e("Do you like this plugin ?", "bbhide"); ?></span></h3>
    <div class="inside" style="display: block;margin-right: 12px;">
        <img src="<?php echo $purl . '/img/icon_coffee.png'; ?>" title="<?php _e("buy me a coffee", "bbhide"); ?>" style=" margin: 5px; float:left;" />
		
        <p><?php _e("Hi! I'm <strong>Flector</strong>, developer of this plugin.", "bbhide"); ?></p>
        <p><?php _e("I've been spending many hours to develop this plugin.", "bbhide"); ?> <br />
		<?php _e("If you like and use this plugin, you can <strong>buy me a cup of coffee</strong>.", "bbhide"); ?></p>
        <form target="new" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
            <input type="image" src="<?php echo $purl . '/img/donate.gif'; ?>" border="0" name="submit" title="<?php _e("Donate with PayPal", "bbhide"); ?>">
        </form>
        <div style="clear:both;"></div>
    </div>
</div>
<?php } ?>
<?php if ($lang == "ru_RU") { ?>
<div class="postbox">

    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e("Do you like this plugin ?", 'bbhide'); ?></span></h3>
    <div class="inside" style="display: block;margin-right: 12px;">
        <img src="<?php echo $purl . '/img/icon_coffee.png'; ?>" title="<?php _e("buy me a coffee", 'bbhide'); ?>" style=" margin: 5px; float:left;" />
		
        <p><?php _e("Hi! I'm <strong>Flector</strong>, developer of this plugin.", 'bbhide'); ?></p>
        <p><?php _e("I've been spending many hours to develop this plugin.", 'bbhide'); ?> <br />
		<?php _e("If you like and use this plugin, you can <strong>buy me a cup of coffee</strong>.", 'bbhide'); ?></p>
      <iframe frameborder="0" allowtransparency="true" scrolling="no" src="https://money.yandex.ru/embed/donate.xml?account=41001443750704&quickpay=donate&payment-type-choice=on&mobile-payment-type-choice=on&default-sum=200&targets=%D0%9D%D0%B0+%D1%80%D0%B0%D0%B7%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D0%BA%D1%83+WordPress-%D0%BF%D0%BB%D0%B0%D0%B3%D0%B8%D0%BD%D0%BE%D0%B2.&project-name=&project-site=&button-text=05&successURL=" width="508" height="64"></iframe>
      
      <p>Или вы можете заказать у меня услуги по WordPress, от мелких правок до создания полноценного сайта.<br />
        Быстро, качественно и дешево. Прайс-лист смотрите по адресу <a target='new' href='https://www.wpuslugi.ru/?from=bbhide-plugin'>https://www.wpuslugi.ru/</a>.</p>
        <div style="clear:both;"></div>
    </div>
</div>
<?php } ?>

<form action="" method="post">

<div class="postbox">

    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e("General Options", "bbhide"); ?></span></h3>
    <div class="inside" style="display: block;">

        <table class="form-table">
        
          
            
            <tr>
                <th><?php _e("Text of hidden content by registration date only:", "bbhide") ?></th>
                <td>
                    <p><textarea rows="5" cols="60" name="hide-days" id="hide-days"><?php echo stripslashes($bbhide_options['hide-days']); ?></textarea></p>
                    
                    <small><?php _e("<strong>%days%</strong> is a variable indicating the number of days the user needs to have been registered.", "bbhide"); ?> </small><br />
                    <small><?php _e("<strong>%your_days%</strong>is a variable indicating the number of days the user has currently been registered.", "bbhide"); ?></small><br />
                </td>
            </tr>
            
             <tr>
                <th><?php _e("Text of hidden content by number of comments only:", "bbhide") ?></th>
                <td>
                    <p><textarea rows="5" cols="60" name="hide-comments" id="hide-comments"><?php echo stripslashes($bbhide_options['hide-comments']); ?></textarea></p>
                    
                    <small><?php _e("<strong>%comments%</strong> is a variable indicating the number of comments the user needs to have left.", "bbhide"); ?> </small><br />
                    <small><?php _e("<strong>%your_comments%</strong> is a variable indicating the current number of comments the user has left.", "bbhide"); ?> </small><br />
                </td>
            </tr>
            
            <tr>
                <th><?php _e("Text of hidden content by registration date and number of comments:", "bbhide") ?></th>
                <td>
                    <p><textarea rows="8" cols="60" name="hide-all" id="hide-all"><?php echo stripslashes($bbhide_options['hide-all']); ?></textarea></p>
                    
                    <small><?php _e("<strong>%days%</strong> and <strong>%comments%</strong> are variables indicating the required numbers of days registered and comments posted.", "bbhide"); ?> </small><br />
                     <small><?php _e("<strong>%your_comments%</strong> and <strong>%your_days%</strong> are variables indicating the user's current numbers of days registered and comments posted.", "bbhide"); ?> </small><br />
                </td>
            </tr>
           
           

            <tr>
                <th></th>
                <td>
                    <input type="submit" name="submit" class="button button-primary" value="<?php _e('Update options &raquo;', "bbhide"); ?>" />
                    <input style="float:right;" type="button" name="button" class="button button-primary restorebtn" value="<?php _e('Restore defaults', "bbhide"); ?>" />
                </td>
            </tr>
            
            
        </table>

    </div>
</div>



<div class="postbox">

    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e('Help', 'bbhide'); ?></span></h3>
	  <div class="inside" style="display: block;"><p>
	 
	  <?php _e('The <strong>BBHide</strong> plugin uses the following shortcode <strong>[hide]</strong> syntax:', 'bbhide'); ?>
    </p>
    
     <table><tr><td width="280px;">
     <span style="color:#183691;">[hide]</span>text<span style="color:#183691;">[/hide]</span></td><td> <span style="color:#bcbcbc;">// <?php _e('If the numbers of comments and days are not set, the default values will be 10.', 'bbhide'); ?> </span></td></tr>
     <tr><td><span style="color:#183691;">[hide comments=</span><span style="color:green;">'20'</span><span style="color:#183691;">]</span>text<span style="color:#183691;">[/hide]</span></td><td> <span style="color:#bcbcbc;">// <?php _e('Hidden text will only be shown to users who have left a certain number of comments.', 'bbhide'); ?></span></td></tr>
    <tr><td><span style="color:#183691;">[hide days=</span><span style="color:green;">'20'</span><span style="color:#183691;">]</span>text<span style="color:#183691;">[/hide]</span></td><td>  <span style="color:#bcbcbc;">// <?php _e('Hidden text will only be shown to users who have been registered on the site for more than a certain number of days.', 'bbhide'); ?></span></td></tr>
    <tr><td><span style="color:#183691;">[hide comments=</span><span style="color:green;">'20'</span> <span style="color:#183691;">days=</span><span style="color:green;">'20'</span><span style="color:#183691;">]</span>text<span style="color:#183691;">[/hide]</span></td><td>  <span style="color:#bcbcbc;">// <?php _e('Hidden text will only be shown to users who have left a certain number of comments and been registered on the site for more than a certain number of days.', 'bbhide'); ?></span></td></tr>
     <tr><td><span style="color:#183691;">[hide comments=</span><span style="color:green;">'15'</span> <span style="color:#183691;">style=</span><span style="color:green;">'grey'</span><span style="color:#183691;">]</span>text<span style="color:#183691;">[/hide]</span></td><td>  <span style="color:#bcbcbc;">// <?php _e('The "style" parameter indicates the color of the bar with the warning. The default is green.', 'bbhide'); ?></span></td></tr>
	  </table>
      
      <p><?php _e('You can also use the plugin\'s button in the visual editor.', 'bbhide'); ?></p>
    
    </div>
</div>


<div class="postbox">
    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e('About', 'bbhide'); ?></span></h3>
	  <div class="inside" style="padding-bottom:15px;display: block;">
     
      <p><?php _e('If you liked my plugin, please <a target="new" href="https://wordpress.org/plugins/bbhide/"><strong>rate</strong></a> it.', 'bbhide'); ?></p>
      <p style="margin-top:20px;margin-bottom:10px;"><?php _e('You may also like my other plugins:', 'bbhide'); ?></p>
      
      <div class="about">
        <ul>
            <li style="list-style-type: square;margin: 0px 0px 3px 35px;"><a class="myplugin" target="new" href="https://wordpress.org/plugins/bbspoiler/">BBSpoiler</a> - <?php _e('this plugin allows you to hide text under the tags [spoiler]your text[/spoiler].', 'bbhide'); ?></li>
            <li style="list-style-type: square;margin: 0px 0px 3px 35px;"><a class="myplugin" target="new" href="https://wordpress.org/plugins/cool-tag-cloud/">Cool Tag Cloud</a> - <?php _e('a simple, yet very beautiful tag cloud.', 'bbhide'); ?> </li>
            <li style="list-style-type: square;margin: 0px 0px 3px 35px;"><a class="myplugin" target="new" href="https://wordpress.org/plugins/easy-textillate/">Easy Textillate</a> - <?php _e('very beautiful text animations (shortcodes in posts and widgets or PHP code in theme files).', 'bbhide'); ?> </li>
            <li style="list-style-type: square;margin: 0px 0px 3px 35px;"><a class="myplugin" target="new" href="https://wordpress.org/plugins/cool-image-share/">Cool Image Share</a> - <?php _e('this plugin adds social sharing icons to each image in your posts.', 'bbhide'); ?> </li>
            </ul>
      </div>     
    </div>
</div>

<?php wp_nonce_field( plugin_basename(__FILE__), 'bbhide_nonce'); ?>
</form>
</div>
</div>
<?php 
}

function bbhide_menu() {
	add_options_page('BBHide', 'BBHide', 'manage_options', 'bbhide.php', 'bbhide_options_page');
}
add_action('admin_menu', 'bbhide_menu');

add_action('admin_print_footer_scripts','bbhide_quicktags'); 
function bbhide_quicktags(){
if (wp_script_is('quicktags')){ ?>
<script type="text/javascript" charset="utf-8">
buttonHide = edButtons.length;
edButtons[edButtons.length] = new edButton('hide','hide','[hide comments=\'10\' days=\'10\']Text[/hide]\n');

jQuery(document).ready(function($){
    jQuery("#ed_toolbar").append('<input type="button" value="hide" id="ed_hide" class="ed_button" onclick="edInsertTag(edCanvas, buttonHide);" comments="10" days="10" />');
});
</script>
<?php } }

function bbhide_admin_bbscript() {
?>
<script type='text/javascript'>
var bbhidebutton = {
    "days":"<?php _e('Number of days', 'bbhide'); ?>",
    "comments":"<?php _e('Number of comments', 'bbhide'); ?>",
    "text":"<?php _e('Hidden text', 'bbhide'); ?>",
    "hide":"<?php _e('Hidden text', 'bbhide'); ?>",
    "style":"<?php _e('Style', 'bbhide'); ?>",
    "green":"<?php _e('Green', 'bbhide'); ?>",
    "blue":"<?php _e('Blue', 'bbhide'); ?>",
    "yellow":"<?php _e('Yellow', 'bbhide'); ?>",
    "orange":"<?php _e('Orange', 'bbhide'); ?>",
    "tan":"<?php _e('Tan', 'bbhide'); ?>",
    "grey":"<?php _e('Grey', 'bbhide'); ?>",
    "red":"<?php _e('Red', 'bbhide'); ?>",
    };
</script>
<?php }    
add_action('admin_head', 'bbhide_admin_bbscript');

function bbhide_add_tinymce() {
    global $typenow;
    if(!in_array($typenow, array('post', 'page')))
        return ;
    add_filter('mce_external_plugins', 'bbhide_add_tinymce_plugin');
    add_filter('mce_buttons', 'bbhide_add_tinymce_button');
}
add_action('admin_head', 'bbhide_add_tinymce');

function bbhide_add_tinymce_plugin($plugin_array) {
	$plugin_array['bbhide_button'] = plugins_url('/inc/bbhide.js', __FILE__);
    return $plugin_array;
}

// Add the button key for address via JS
function bbhide_add_tinymce_button($buttons) {
    array_push($buttons, 'bbhide_button_button_key');
    return $buttons;
}


function bbhide_admin_print_scripts() {
$post_permalink = $_SERVER["REQUEST_URI"];
if(strpos($post_permalink, 'bbhide.php') == true) : 
$rest_hide_days = __('To view hidden content you must have been registered on the site for at least %days% (you have currently been registered for %your_days%).','bbhide');
$rest_hide_comments = __('To view hidden content you must have at least %comments% (you currently have %your_comments%).','bbhide');
$rest_hide_all = __('To view hidden content you must have been registered on the site for at least %days% (you have currently been registered for %your_days%) and have at least %comments% (you currently have %your_comments%).','bbhide');
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
$('.tcode').textillate({
  loop: true,
  minDisplayTime: 5000,
  initialDelay: 800,
  autoStart: true,
  inEffects: [],
  outEffects: [],
  in: {
    effect: 'rollIn',
    delayScale: 1.5,
    delay: 50,
    sync: false,
    shuffle: true,
    reverse: false,
    callback: function () {}
  },
   out: {
    effect: 'fadeOut',
    delayScale: 1.5,
    delay: 50,
    sync: false,
    shuffle: true,
    reverse: false,
    callback: function () {}
  },
  callback: function () {}
});})
</script>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $(".restorebtn").click( function (){
        $("#hide-days").val('<?php echo $rest_hide_days; ?>');
        $("#hide-comments").val('<?php echo $rest_hide_comments; ?>');
        $("#hide-all").val('<?php echo $rest_hide_all; ?>');
    });
;})
</script>    
<?php endif; ?>
<?php }    
add_action('admin_head', 'bbhide_admin_print_scripts');

