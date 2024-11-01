<?php
/*
File Name: cham-rewrite.php
Description: Used for generating random copies of pages or articles for opt-in page optimization or article rewriting.
Author: Gustav Stieger
Author URI: http://www.jv2win.com
License: Copyright (c) 2009, 2010 Gustav Stieger. All rights reserved.

Please refer to "license.txt" file located in the plugin folder
for copyright notice and end user license agreement.
*/

function chameleon_listVariants(&$buffer, &$pos, &$weight)
{
   $ar = array();
   
   $weight = array();

   $depth = 0;
   
   $cdepth = 0;
   
   $subvar = 1;

   $start = $pos;

   for ($len = strlen($buffer); $pos < $len; $pos++)
   {
     $c = $buffer[$pos];
     
     if ($c == '[')
     	$cdepth++;
     elseif ($c == ']')
     	$cdepth--;

     if ($cdepth != 0)
     	continue;
     	
     if ($c == '}')
     {
       if ($depth-- == 0)
       {
         array_push($ar, substr($buffer, $start, $pos - $start));
         array_push($weight, (float)$subvar);
         $tweight += $subvar;
         
         foreach($weight as $key => $value)
         	$weight[$key] = $value / $tweight;
         
	  	 return $ar;
       }
     }
     elseif ($c == '|')
     {
       if ($depth == 0)
       {
         array_push($ar, substr($buffer, $start, $pos - $start));
         array_push($weight, (float)$subvar);
         $tweight += $subvar;
         
         $start = $pos + 1;
         
         $subvar = 1;
       }
       else
         $subvar++;
     }
     elseif ($c == '{')
     {
       $depth++; // {a|{x|{{b|c}|d|{e|f}}|g}
     }
   }

   if ($depth > 0)
     return false;

   return $ar;
}

function chameleon_getText(&$buffer, &$pos, $open = '{', $close = '}')
{
   $depth = 0;

   $start = $pos;

   for ($len = strlen($buffer); $pos < $len; $pos++)
   {
     $c = $buffer[$pos];

     if ($c == $close)
     {
       if ($depth-- == 0)
       {
         $pos++;
         
         return substr($buffer, $start, $pos - $start - 1);
       }
     }
     elseif ($c == $open)
     {
       $depth++;
     }
   }

   return false;
}

function chameleon_token(&$buffer, &$pos)
{
  // Skip white space
  for ($len = strlen($buffer); 
  	$pos < $len && $buffer[$pos] == ' '; $pos++); 
  
  if ($pos >= $len)
  	return "";
  	
  // Get the token  
  $spos = $pos;

  $c = $buffer[$pos++];

  if ($c == '@' || $c == '_' || ($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z'))
  {
    $digits = false;
  }
  elseif (($c >= '0' && $c <= '9'))
  {
    $digits = true;
  }
  else
    return $c;

  for (; $pos < $len; $pos++)
  {
    $c = $buffer[$pos];

    if ($c >= '0' && $c <= '9') continue; 
    if ($digits) 
    {
    	if ($c == '.') continue;
    	break;
    }
    if ($c == '_') continue;
    if ($c >= 'a' && $c <= 'z') continue;
    if ($c >= 'A' && $c <= 'Z') continue;

    break;
  }

  return substr($buffer, $spos, $pos - $spos);
}

function chameleon_eval(&$vars, &$buffer, &$pos, $term)
{
  $ar = array();

  for (; $pos < strlen($buffer); )
  {
    $token = chameleon_token($buffer, $pos);
    
    // Anonymous String or Template
    if ($token == "{")
    {
      $buffer2 = '{' . chameleon_getText($buffer, $pos) . '}';
      
      $ar['@'] = chameleon_rewrite_inner($vars, $buffer2);
    }
    // Anonymous number
    else if (is_numeric($token))
    {
      $ar["@"] = $token;
    }
    // Anonymous Node
    else if ($token == "[")
    {
      $pos++;

      $ar['@'] = chameleon_eval($vars, $buffer, $pos, "]");
    }    
    else
    {
      unset($ch);
      
      if ($pos < strlen($buffer)) $ch = $buffer[$pos];
       
      if ($ch == "(" || ($ch == "[" && $token[0] == "@") || $ch == "{")
      {
      	$pos++;
      	
        // Get value from function
      	if ($ch == "(")
      	{
	      $params = chameleon_eval($vars, $buffer, $pos, ")");
	      
	      // Make request context available
	      $params['request'] = $vars['request'];
	        
	      $params['@id'] = $vars['@id'];
	        
	      $rep = $vars["$token()"];
	
	      if (isset($rep))
	        $rep = $rep($params);
	        
	      $token = '@';
      	}
      	// Get value from String or Template
      	elseif ($ch == "{")
      	{
          $buffer2 = '{' . chameleon_getText($buffer, $pos) . '}';
      
          $rep = chameleon_rewrite_inner($vars, $buffer2);
      	}
      	// Get scalar value
      	elseif ($ch == "[")
      	{
  	      // @x[@n] (@x = @n)
          $ev = chameleon_eval($vars, $buffer, $pos, "]");
      
          if ($token == "@n")
            $rep = chameleon_fixn($ev["@"]);
	      else
            $rep = $ev['@'];
      	}
      }
      else
      // Get value from variable
      {
        // TODO: Extend variable selection to be xpath selection
        // Current:
        // x or @x
      	// XPath:
        // x[@id="1"]
    	// x[@n] (x that has n attribute!)
    	// x[@id="1"]/y[@id='2']/@name[@n]
    	// x/y[@id="2"]/z[@id='3']/@name[@n]
    	
        $ctx = $vars;
        
        $global = true;
    	
        for (; ; $global = false)
        {
	      if (isset($ctx))
	      {
		    $rep = $ctx[$token];
		    
		    if (!isset($rep) && $global == true)
		    {
              if (!isset($rep))
              {
                $rep = $vars['request'];
      
    		    if (isset($rep))
	              $rep = $rep[$token];
              }
		    }
	      }
	      else
	      	unset($rep);
		  
		  if ($pos >= strlen($buffer)) break;
		    
	      if ($buffer[$pos] == '/')
          {
            $pos++;
            $ctx = $rep;
    		$token = chameleon_token($buffer, $pos);
          }
          elseif ($buffer[$pos] == '[')
          {
            $pos++;
            // Not supported yet
            $dummy = chameleon_getText($buffer, $pos, '[', ']');
          }
          else break;
        }
        
        $token = '@';
      }

      $ar[$token] = $rep;
    }
   
    $sep = chameleon_token($buffer, $pos);

    if ($sep == $term) 
      break;
  }

  return $ar;
}

function chameleon_fixn($n)
{
  if (!is_numeric($n)) 
    $n = abs(hexdec(hash('crc32', $n)) % 10000);

  mt_srand($n);

  return $n;
}

function chameleon_ucwords(&$params)
{
  return ucwords($params["@"]);
}

function chameleon_ucfirst(&$params)
{
  return ucfirst($params["@"]);
}

function chameleon_map(&$params)
{
  $res = $params[$params["@"]];
  
  if (!isset($res))
    $res = $params['_else'];
    
  return $res;
}

// setcookies(30, @tid[@tid], @search[referrer/@q]);
function chameleon_setcookies(&$params)
{
  $expiry = $params['@'];
  
  if (is_numeric($expiry))
    $expiry = time() + $expiry;
  else
    unset($expiry);
  	
  foreach ($params as $key => $value)
    if ($key[0] == '@' && $key != '@' && isset($value))
      setcookie(substr($key, 1), "$value", $expiry, '/');
 
  return '';
}

function chameleon_request()
{
  $request = array();
  
  foreach (array_merge($_COOKIE, $_GET, $_POST) as $key => $val)
  	$request['@' . $key] = $val;
  
  $request["@ip"] = $_SERVER['REMOTE_ADDR'];
  $request["@cbip"] = str_replace(".", "o", $request["@ip"]);
  $request["@url"] = 'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  
  $referer = $_SERVER["HTTP_REFERER"];
  
  $refargs = array();
  
  if (isset($referer))
  {
    $parts = explode('?', $referer);
  
    if (count($parts) > 1)
    {
      $rvars = explode('&', $parts[1]);
  
      $ref = array();
  
      foreach ($rvars as $rvar)
      {
  	    $kv = explode('=', $rvar);
  	
  	    if (count($kv) == 1)
  	      $refargs['@'] = $kv[0];
  	    else
  	      $refargs['@' . $kv[0]] = $kv[1];
  	  }
    }
  }
  
  $request['@referrer'] = $referer;  
  $request['refargs'] = $refargs;   
  	
  return $request;
}

function chameleon_rewrite(&$vars, $buffer) 
{
  $vars["lcrl"] = "{";
  $vars["rcrl"] = "}";
  $vars["lsqb"] = "[";
  $vars["rsqb"] = "]";
  $vars["pipe"] = "|";
  $vars["amps"] = "&";
  
  if (!isset($vars['request']))
  	$vars['request'] = chameleon_request();
  
  $vars["ucwords()"] = "chameleon_ucwords";
  $vars["ucfirst()"] = "chameleon_ucfirst";
  $vars["map()"] = "chameleon_map";
  $vars["setcookies()"] = "chameleon_setcookies";
  
  $n = $vars["@n"];

  if (strlen("$n") == 0)
    $n = mt_rand(0, 9999);

  $vars["@n"] = chameleon_fixn($n);

  // Legacy support
  $vars["n"] = $vars["@n"];
  $vars["ip"] = $vars["@ip"];
  $vars["cbip"] = $vars["@cbip"];
  
  return chameleon_rewrite_inner($vars, $buffer);
}

function chameleon_rewrite_inner(&$vars, $buffer) 
{
  for ($pos = 0; $pos < strlen($buffer); $pos++)
  {
    $pos1 = strpos($buffer, "{", $pos);
    $pos2 = strpos($buffer, "[", $pos);

    if ($pos1 === false && $pos2 === false)
      return $buffer;

    if ($pos1 === false || ($pos2 !== false && $pos2 < $pos1))
    {
      $pos = $pos2 + 1;

      $rep = chameleon_eval($vars, $buffer, $pos, "]");
      
      $res = $rep['@'];
      
      for (; is_array($res); $res = $res['@']);
      
      $buffer = substr_replace($buffer, "$res", $pos2, $pos - $pos2);

      $pos = $pos2 + strlen($res) - 1;

      foreach($rep as $variable => $value)
      {
        if ("$variable" == "@n")
	      $vars["@n"] = chameleon_fixn($value);
	    else
      	  $vars[$variable] = $value;
      }

      continue;
    }
      
    $pos = $pos1 + 1;

    $variants = chameleon_listVariants($buffer, $pos, $weight);    

    if ($variants === false)
       return $buffer;
       
    $rnum = (float)mt_rand(0, 999999) / 1000000;
    
    $ndx = 0;
    
    for ($tot = $weight[0]; $tot < $rnum; )
      $tot += $weight[++$ndx];

    $rep = chameleon_rewrite_inner($vars, $variants[$ndx]);

    $buffer = substr_replace($buffer, "$rep", $pos1, $pos - $pos1 + 1);

    $pos = $pos1 + strlen($rep) - 1;
  }

  return $buffer;
}
?>