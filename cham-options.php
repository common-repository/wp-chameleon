<div class="wrap">
	<h2>WP Chameleon</h2>
	<form method="post" action="options.php">
<?php
	$opt = get_option( 'cham_variables' );
	settings_fields( 'chameleon-variables' );
?>
		<h3>Site-Level Chameleon Variables (Optional)</h3>
		<p>USAGE IN POSTS: These variables can be included in your posts by placing square brackets around the variable name</p>
		<p>OR you can use variables as templates by inserting "[rewrite(@varname)]" where "varname" is the variable name.</p>
		<p>EDITING THIS LIST: Fill in all the boxes to add a variables.  Remove the variable name to delete a variable.</p>
		<p>Name: The name of the variable.</p>
		<p>Value: The string value of the variable.</p>
		<table class="form-table" style="width:100%">
			<tr>
				<th><b>Name</b></th>
				<th><b>Value</b></th>
			</tr>
<?php
	$maxkey = 0;
	
	if (isset($opt) && isset($opt['variables'])) {
	foreach ($opt['variables'] AS $key => $variable) {
		if ($key > $maxkey) $maxkey = $key;
?>
			<tr>
				<td><input type="text" name="cham_variables[variables][<?php echo htmlentities2( $key ); ?>][name]" value="<?php echo htmlentities2( $variable['name'] ); ?>" /></td>
				<td><input type="text" name="cham_variables[variables][<?php echo htmlentities2( $key ); ?>][value]" value="<?php echo htmlentities2( $variable['value'] ); ?>" /></td>
			</tr>
<?
	} }
?>
			<tr>
				<td><input type="text" name="cham_variables[variables][<?php echo htmlentities2( ($maxkey + 1) ); ?>][name]" value="" /></td>
				<td><input type="text" name="cham_variables[variables][<?php echo htmlentities2( ($maxkey + 1) ); ?>][value]" value="" /></td>
				<td></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="save" class="button-primary" value="<?php _e( 'Save Variables' ) ?>" />
		</p>
    </form>
    
	<form method="post" action="options.php">
		<?php
			$opt = get_option( 'cham_options' );
			settings_fields( 'chameleon-options' );
		?>

		<h3>Remote Mirrors for Publishing Posts (Optional)</h3>
		<p>IMPORTANT: In order to successfully post to remote blogs you must (1) Set the checkbox  
		&quot;[ ] XML-RPC Enable the WordPress, Movable Type, MetaWeblog and Blogger XML-RPC publishing 
		protocols&quot; on the REMOTE BLOG's Dashboard under Settings / Writing. (2) Have at least some 
		categories on the remote blog with identical names to the categories your local posts belong to. 
		(3) You can initially leave the "Category IDs" field empty. This should only be populated if you
		have a category that all syndicated posts should belong to - e.g. Syndicated Articles and
		the <b>ID</b> of the field should be entered in this field - not the name.</p>
		<p>EDITING THIS LIST: Fill in all the boxes to add a server.  Remove the server name to delete a server.</p>
		<p>Address: The server RSS address - e.g. &quot;http://www.yourremoteservername.com/&quot;</p>
		<p>Username: Your user id on the remote server.</p>
		<p>Password: Your password on the remote server.</p>
		<p>Categories: Leave empty until you get this working. Read below on how to use.</p>
		<p>Raw: Set this checkbox if you have wp-chameleon installed on the remote site.</p>
		<table class="form-table">
			<tr>
				<th><b>Address</b></th>
				<th><b>Username</b></th>
				<th><b>Password</b></th>
				<th><b>Categories</b>
				<th><b>Raw</b></th>
			</tr>
<?php
	$maxkey = 0;
	
	if (isset($opt) && isset($opt['servers'])) {
	foreach ($opt['servers'] AS $key => $server) {
		if ($key > $maxkey) $maxkey = $key;
		$checked = "";
		if ($server['raw'] == 'on')
		  $checked = 'checked';
?>
			<tr>
				<td><input type="text" name="cham_options[servers][<?php echo htmlentities2( $key ); ?>][server]" value="<?php echo htmlentities2( $server['server'] ); ?>" /></td>
				<td><input type="text" name="cham_options[servers][<?php echo htmlentities2( $key ); ?>][username]" value="<?php echo htmlentities2( $server['username'] ); ?>" /></td>
				<td><input type="password" name="cham_options[servers][<?php echo htmlentities2( $key ); ?>][password]" value="<?php echo htmlentities2( $server['password'] ); ?>" /></td>
				<td><input type="text" name="cham_options[servers][<?php echo htmlentities2( $key ); ?>][categories]" value="<?php echo htmlentities2( $server['categories'] ); ?>" /></td>
				<td><input type="checkbox" name="cham_options[servers][<?php echo htmlentities2( $key ); ?>][raw]" <?php echo $checked; ?> /></td>
			</tr>
<?
	} }
?>
			<tr>
				<td><input type="text" name="cham_options[servers][<?php echo htmlentities2( ($maxkey + 1) ); ?>][server]" value="" /></td>
				<td><input type="text" name="cham_options[servers][<?php echo htmlentities2( ($maxkey + 1) ); ?>][username]" value="" /></td>
				<td><input type="password" name="cham_options[servers][<?php echo htmlentities2( ($maxkey + 1) ); ?>][password]" value="" /></td>
				<td><input type="text" name="cham_options[servers][<?php echo htmlentities2( ($maxkey + 1) ); ?>][categories]" value="" /></td>
				<td><input type="checkbox" name="cham_options[servers][<?php echo htmlentities2( ($maxkey + 1) ); ?>][raw]" /></td>
				<td></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="save" class="button-primary" value="<?php _e( 'Save Servers' ) ?>" />
		</p>
	</form>
</div>