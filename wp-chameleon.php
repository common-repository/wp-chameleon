<?php
/*
Plugin Name: WP Chameleon
Plugin URI: http://www.jv2win.com/wp-chameleon
Description: Used for generating random copies of pages or articles for opt-in page optimization or article rewriting.
Version: 1.22
Author: Gustav Stieger
Author URI: http://www.jv2win.com
License: Copyright (c) 2009, 2010 Gustav Stieger. All rights reserved.

Please refer to "license.txt" file located in the plugin folder
for copyright notice and end user license agreement.
*/
include("cham-rewrite.php");

function chameleon_globals()
{
  $vars = array();
  
  $options = get_option('cham_variables');
  
  if (isset($options))
  {
    $globals = $options['variables'];
    
    if (isset($globals))
    {
      foreach ($globals as $key => $value)
      {
        $vars['@' . trim($value['name'])] = trim($value['value']);
      }
    }
  }    

  return $vars;
}

function chameleon_variables($postId)
{
  $vars = array();
  
  $globals = chameleon_globals();

  // Legacy
  foreach ($globals as $key => $value)
    $vars[$key] = $value;
  
  $custom_field_keys = get_post_custom_keys($postId);
  
  if (isset($custom_field_keys))
  {  
    foreach ($custom_field_keys as $key => $value) 
    {
      $valuet = trim($value);
    
      if ('_' == $valuet{0})
        continue;
      
      $vars['@' . $valuet] = get_post_meta($postId, $valuet, true); 
    }
  }
  
  $vars['globals'] = $globals;
  
  return $vars;
}

function chameleon_formatter($buffer) 
{
  // modify buffer here, and then return the updated code
  global $post;
  
  $vars = chameleon_variables($post->ID);
  
  $request = chameleon_request();
  
  $request["@single"] = is_single()?1:0;
  $request["@url"] = get_permalink($post->ID) . '?' . $_SERVER['QUERY_STRING']; 
    	
  // The request 'tid' if set overrides the 'tid' custom variable
  if (isset($request['@tid']))
    $vars['@tid'] = $request['@tid']; 
  	
  $vars['tid'] = $vars['@tid']; // Legacy
  $vars['request'] = $request;
  $vars['@mode'] = $request['@mode'];
  
  return chameleon_post_rewrite($buffer, $vars);
}

// seed with microseconds
function chameleon_seed()
{
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
}

function chameleon_addfunctions(&$vars)
{
  $vars["post()"] = 'chameleon_getpost';
  $vars["postraw()"] = 'chameleon_getpostraw';
  $vars["decode()"] = "chameleon_decode";
  $vars["rewrite()"] = "chameleon_text_rewrite";
  $vars["call()"] = "chameleon_callpost";
}

function chameleon_post_rewrite($buffer, &$vars) {
  // modify buffer here, and then return the updated code
  	
  if ($vars['@mode'] == 'raw')
    return "$buffer";

  if (!isset($vars['@n']))
  	$vars['@n'] =  chameleon_fixn($vars['@url']);
    
  chameleon_addfunctions($vars);
  
  //mt_srand(chameleon_seed());
  
  for ($pos = 0; $pos < strlen($buffer); $pos++)
  {
    $pos = stripos($buffer, "{rewrite}", $pos);

    if ($pos === false)
      break;

    $epos = stripos($buffer, "{/rewrite}", $pos);

    if ($epos === false)
      break;

    $rw = chameleon_rewrite($vars, substr($buffer, $pos + 9, $epos - ($pos + 9)));

    $buffer = substr_replace($buffer, $rw, $pos, $epos + 10 - $pos);

    $pos += strlen($rw);
  }

  return "$buffer";
}

function chameleon_decode(&$params)
{
  return wp_specialchars_decode($params['@']);
}

function chameleon_getpostraw($params)
{
  $postRef = get_post($params["@"], ARRAY_A);

  return wpautop($postRef['post_content']);
}

function chameleon_getpost($params)
{
  $buffer = chameleon_getpostraw($params);

  return chameleon_post_rewrite($buffer, $params);
}

function chameleon_callpost($params)
{
  $buffer = chameleon_getpostraw($params);
  
  $vars = chameleon_variables($params['@']);
  
  foreach ($params as $key => $value)
  	$vars[$key] = $value;
  
  $vars['@id'] = $params['@'];

  return chameleon_post_rewrite($buffer, $vars);
}

function chameleon_text_rewrite(&$params)
{
  chameleon_addfunctions($params);
  
  return chameleon_rewrite($params, $params['@']);
}

if (is_admin()) 
{
	add_action( 'admin_menu', 'chameleon_menu' );
	add_action( 'admin_init', 'chameleon_register_settings' );
	add_action( 'wp_insert_post', 'chameleon_post' );
	
	register_activation_hook( __FILE__, 'chameleon_activate');
 
	function chameleon_menu() 
	{
		add_submenu_page('options-general.php', 'WP Chameleon Settings', 'WP Chameleon', 'manage_options', 'chameleon', 'chameleon_admin');
	}

	function chameleon_register_settings() 
	{
		register_setting('chameleon-options', 'cham_options', 'chameleon_sanitize_options');
		register_setting('chameleon-variables', 'cham_variables', 'chameleon_sanitize_options');
	}

	function chameleon_admin() 
	{
		require_once('cham-options.php');
	}

	function chameleon_get_tags($postId) 
	{
		$terms = get_object_term_cache($postId, 'post_tag');
		
		if ($terms === false) 
			$terms = wp_get_object_terms($postId, 'post_tag');

		if (empty($terms))
			return false;

		return $terms;
	}
	
	function chameleon_activate() 
	{
	}

	function chameleon_sanitize_options($opt) 
	{
	    if (isset($opt['servers']))
	    {
		  foreach ($opt['servers'] AS $skey => $sinfo) 
		  {
			if ($sinfo['server'] == '') 
				unset($opt['servers'][$skey]);
			else 
			{
				$server = trim($sinfo['server']);
				
				if (substr($server, 0, 7 ) != 'http://') 
		        	$server = "http://$server";
		        	
				if (substr($server, -1) != '/') 
		        	$server .= '/';
		         
				$opt['servers'][$skey]['server'] = $server;
			}
		  }
	    }
	    
	    if (isset($opt['variables']))
	    {
		  foreach ($opt['variables'] AS $vkey => $vinfo) 
		  {
			if ($vinfo['name'] == '') 
				unset($opt['variables'][$vkey]);
			else 
			{
			}
		  }
	    }
	    
		return $opt;
	}

	function chameleon_post($postId) 
	{
	    $options = get_option('cham_options');
	    
	    if (!isset($options) || !isset($options['servers']))
	    	return;
	     
		$postData = get_post($postId);

		if ($postData->post_status == 'publish' && $postData->post_type == 'post')
		{
			if (@include_once(ABSPATH . WPINC . '/class-IXR.php'))
			{
				$rids = unserialize(get_post_meta($postId, '_cham_postinfo', true));

				if (!isset($rids))
					$rids = array();

  				foreach ($options['servers'] AS $skey => $sinfo) 
  				{
  					$catstr = str_replace(" ", "", $sinfo['categories']);
  					
	  				$categories = get_the_category($postId);
	  				
  					if ($catstr != "")
  					{
	  					$catset = explode(',', $catstr);
	  					
	  					$process = false;
	  					
	  					foreach ($categories AS $cat)
	  					{
	  						if (in_array($cat->cat_ID, $catset))
	  						{
								$process = true;
								break;  							
	  						}
	  					}
	  					
	  					if ($process == false)
	  						continue;
  					}
  									
					$rpostId = $rids[$skey];

					$xmlrpc = new IXR_Client($sinfo['server'].'xmlrpc.php');
					
					// Records available ids
					$ids = array();
					
					if (isset($rpostId))
					{
						$xmlrpc->query('metaWeblog.getPost', $rpostId, $sinfo['username'], $sinfo['password']);
						
						$existPost = $xmlrpc->getResponse();
						
						if ($existPost['faultCode'] == '404')
							unset($rpostId);
						else
						{
							$remoteCustom = $existPost['custom_fields'];
							
							if (isset($remoteCustom))
							{
								foreach ($remoteCustom as $field)
								{								
								    $arr = $ids[$field['key']];
								    
								    if (!isset($arr))
								    	$arr = array();
	
								    $arr[] = $field['id'];
								    
								    $ids[$field['key']] = $arr;
								}
							}
						}
					}
					
					$custom = array();
					
					$custom_field_keys = get_post_custom_keys($postId);
					
  					foreach ($custom_field_keys as $key) 
  					{
      					if ($key[0] == '_')
      						continue;
      						
      					$mykey_values = get_post_custom_values($key, $postId);
      					
  						foreach ($mykey_values as $value) 
  						{
  							$arr = $ids[$key];
  							
  							if (isset($arr) && count($arr) != 0)
  							{
  								$id = array_pop($arr);
  								
  								$ids[$key] = $arr;

  								$custom[] = array('id' => $id, 'key' => $key, 'value' => $value);
  							}
  							else
								$custom[] = array('key' => $key, 'value' => $value);
  						}
  					}
  					
  					// Create dummy custom fields with unused ids to facilitate deletion
  					foreach ($ids as $arr)
  						foreach ($arr as $id)
  							$custom[] = array('id' => $id);

  					$rpost['custom_fields'] = $custom;
					
					$vars = chameleon_variables($postId);
	
					$request = array();
					$request['@single'] = 0;
					
					$vars['request'] = $request;
					
					$vars['@url'] = $sinfo['server'] . '?' . $postId;
					
					if ($sinfo['raw'] == 'on')
						$rpost['description'] = $postData->post_content;
					else
						$rpost['description'] = chameleon_post_rewrite($postData->post_content, $vars);
  					
					if ($sinfo['raw'] == 'on')
  						$rpost['title'] = $postData->post_title;
  					else
  						$rpost['title'] = chameleon_post_rewrite($postData->post_title, $vars);
  					
					if ($postTags = chameleon_get_tags($postId)) 
					{
						$keywords = array();
						
						foreach ($postTags AS $postTag ) 
							$keywords[] = $postTag->name;

						$rpost['mt_keywords'] = implode(',', $keywords);
					}
					
					$xmlrpc->query('metaWeblog.getCategories', 0, $sinfo['username'], $sinfo['password']);
					
					$cats = $xmlrpc->getResponse();
					
					$catNames = array();
					
					foreach ($cats AS $cat)
						$catNames[$cat['categoryName']] = 1;
				
					$rpost['categories'] = array();
				
					foreach ($categories AS $cat)
						if (isset($catNames[$cat->cat_name]))
			    			$rpost['categories'][] = $cat->cat_name;
			    	
					if (!isset($rpostId))
						$func = 'metaWeblog.newPost';
					else
						$func = 'metaWeblog.editPost';
			    		
					$xmlrpc->query($func, "$rpostId", $sinfo['username'], $sinfo['password'], $rpost, 1);

					if (!isset($rpostId))
					{
						$rpostId = $xmlrpc->getResponse();
						
  						$rids[$skey] = $rpostId;
					}
	  			}
	  			
				update_post_meta($postId, '_cham_postinfo', serialize($rids));
			}
		}
	}
}
else
{
	add_filter('the_title', 'chameleon_formatter', 99);
	
	add_filter('the_content', 'chameleon_formatter', 99);
	
	add_filter('the_excerpt', 'chameleon_formatter', 99);
	
	add_filter('widget_text', 'chameleon_formatter', 99);
	
	// REMOVING THE CODE BELOW WILL VIOLATE OUR TERMS OF USE AND CONSTITUTE COPYRIGHT INFRINGEMENT.
	// This code can be removed for a specific domain after making a $10 donation and sending  
	// an e-mail asking for permission to admin@jv2win.com. The e-mail must include the domain name. 
	function extraFooterContent() { ?>
	<!-- please do not remove this. respect the authors :) -->
	<center><p style="font-size:10px">This site uses <?php
	$params = array();
	$buffer = "[@n[@url]][@u[@url]]{the WP Chameleon {WordPress |}{plugin|article software} to rewrite {content|articles}|the WP Chameleon {content|text|post|article} {{re|}writer|plugin|software} for {{{re|}writing|authoring|creating} {original |distinct |unique |}{content|posts|text|pages|blogs|articles|{content|text|posts|blogs|pages|article} {re|}writing|{creating|generating|authoring} {original |distinct |unique |} {text|blogs|pages|posts|content|articles}}}.";
	$rewritex = chameleon_rewrite($params, $buffer);
	$n = $params["@n"];
	$url = $params['@u']; 
	
	if (isset($url) && strpos($url, "wp-chameleon") === false)
		$url = 'http://www.jv2win.com/wp-chameleon/?Using-' . str_replace(' ', '-', $rewritex) . '&n=' . $n;
		
	echo(str_replace('WP Chameleon', '<a href="' . "$url" . '">WP Chameleon</a>', $rewritex));
	?></p></center>
<?php }

    add_filter('wp_footer', 'extraFooterContent');    
}
?>