<?php

if(!defined('EXT'))
{
	exit('Invalid file request');
}

class Entry_reedirect
{
	var $settings        = array();
	var $name            = 'Entry REEdirect';
	var $version         = '1.0.3';
	var $description     = 'Choose where users are redirected after publishing/updating new entries in the control panel.';
	var $settings_exist  = 'y';
	var $docs_url        = '';

	
	// -------------------------------
	//   Constructor - Extensions use this for settings
	// -------------------------------
	
	function Entry_reedirect($settings='')
	{
	    $this->settings = $settings;
	}
	// END
	
	
	// --------------------------------
	//  Settings
	// --------------------------------  
	
	function settings()
	{	    
	    global $DB, $LANG, $PREFS;

		$settings = array();
		
		$locations = array(
			'default' => 'Default Preview',
			'new' => 'Publish Form',
			'remain' => 'Edit Entry',
			'edit' => 'Manage Entries'
		);
		
		$global_locations = $locations;
		$global_locations['none'] = 'None';
		
		$settings['hide_success_message'] = '5';
		$settings['global_new_entry_redirect'] = array('r', $global_locations, 'none');
		$settings['global_updated_entry_redirect'] = array('r', $global_locations, 'none');
	
		$query = $DB->query("SELECT blog_title, weblog_id FROM exp_weblogs ORDER BY blog_title ASC");
		if($query->num_rows > 0)
		{
			foreach($query->result as $value)
			{
				$LANG->language['new_redirect_weblog_id_'.$value['weblog_id']] = "Redirect after publishing new " . strtoupper($value['blog_title']) . ' entries:';
				$settings['new_redirect_weblog_id_'.$value['weblog_id']] = array('s', $locations, 'default');
				$LANG->language['updated_redirect_weblog_id_'.$value['weblog_id']] = "Redirect after updating " . strtoupper($value['blog_title']) . ' entries:';
				$settings['updated_redirect_weblog_id_'.$value['weblog_id']] = array('s', $locations, 'default');				}
		}
	    
	    return $settings;
	}
	// END
	
	
	// --------------------------------
	//  Put $_POST['return_url'] into a global variable so we can access it later
	//  (No access to this variable from the other, more appropriate hooks)
	// --------------------------------  	
	
	function grab_return_url() {		
		global $saef_return_url;
		$saef_return_url = ($_POST['return_url']) ? $_POST['return_url'] : '';
	}	
    // END
    
	
	// --------------------------------
	//  Do the redirect
	// --------------------------------  	
	
	function redirect_location($entry_id, $data, $cp_call) {
				
		if($cp_call == TRUE)
		{
			$type = ($_POST['entry_id']) ? 'updated' : 'new';
			$redirect = $this->settings[$type.'_redirect_weblog_id_'.$data['weblog_id']];
			$default = BASE.AMP.
			'C=edit'.AMP.
			'M=view_entry'.AMP.
			'weblog_id='.$data['weblog_id'].AMP.
			'entry_id='.$entry_id.AMP.
			'U='.$type;
			
			if($redirect)
			{
				switch($redirect)
					{
						case 'new':
							$location = BASE.AMP.
							'C=publish'.AMP.
							'M=entry_form'.AMP.
							'weblog_id='.$data['weblog_id'].AMP.
							'reedirect_entry_id='.$entry_id.AMP.
							'U='.$type;
							break;
						case 'remain':
							$location = BASE.AMP.
							'C=edit'.AMP.
							'M=edit_entry'.AMP.
							'weblog_id='.$data['weblog_id'].AMP.
							'entry_id='.$entry_id.AMP.
							'reedirect_entry_id='.$entry_id.AMP.
							'U='.$type;
							break;
						case 'edit':
							$location = BASE.AMP.
							'C=edit'.AMP.
							'M=view_entries'.AMP.
							'weblog_id='.$data['weblog_id'].AMP.
							'reedirect_entry_id='.$entry_id.AMP.
							'U='.$type;
							break;
						default:
							$location = $default;			
					}
			}
			else
			{
				$location = $default;	
			}
		}
		else
		{
			global $FNS, $saef_return_url;
			$FNS->template_type = 'webpage';
			$location = ($saef_return_url == '') ? $FNS->fetch_site_index() : $FNS->create_url($saef_return_url, 1, 1);
		}
		
		return $location;

	}	
    // END
    
    
	// --------------------------------
	//  Edits to the control panel output
	// --------------------------------  	
	
	function cp_changes($out) {
				
		global $DB, $EXT, $DSP, $LANG;
		if ($EXT->last_call !== FALSE)
		{
			$out = $EXT->last_call;
		}
		
		// Add jQuery to extension settings page
		if( (isset($_GET['P']) && $_GET['P'] == 'extension_settings') && $_GET['name'] == 'entry_reedirect')
		{
			$target = '</head>';
			$js = '
			<script type="text/javascript">
			<!-- Added by Entry REEdirect -->
			$(document).ready(function()
				{
					if($("input[type=radio][name=global_new_entry_redirect]:checked").val() != "none")
					{
						$("select[name^=new_redirect]").attr("disabled", "disabled");
					}
					if($("input[type=radio][name=global_updated_entry_redirect]:checked").val() != "none")
					{
						$("select[name^=updated_redirect]").attr("disabled", "disabled");
					}
					$("input[type=radio][name=global_new_entry_redirect][value!=none]").click(function(){
						var setValue = $(this).val();
						$("select[name^=new_redirect] option[value=" + setValue + "]").attr("selected", "selected");
						$("select[name^=new_redirect]").attr("disabled", "disabled"); 
					});
					$("input[type=radio][name=global_updated_entry_redirect][value!=none]").click(function(){
						var setValue = $(this).val();
						$("select[name^=updated_redirect] option[value=" + setValue + "]").attr("selected", "selected");
						$("select[name^=updated_redirect]").attr("disabled", "disabled");
 
					});
					$("input[type=radio][name=global_new_entry_redirect][value=none]").click(function(){
						$("select[name^=new_redirect]").removeAttr("disabled");
					});
					$("input[type=radio][name=global_updated_entry_redirect][value=none]").click(function(){
						$("select[name^=updated_redirect]").removeAttr("disabled");
					});
					$("form[name=settings_entry_reedirect]").submit(function(){
						$("select[name*=redirect_weblog_id]").removeAttr("disabled");
					});
				}
			);
			</script>
			</head>
			';
			$out = str_replace($target, $js, $out);
		}
		
		
		// Display success messages
		$find = array();
		$replace = array();
		
		if(isset($_GET['reedirect_entry_id']))
		{
			// Success message goes bye-bye? If so, add the callback.
			$auto_hide = ($this->settings['hide_success_message']) ? ',function(){
				setTimeout(
					function(){
						$("div#entry_reedirect_message").slideUp();
					}
				,'.($this->settings['hide_success_message']*1000).'
				);
			}' : '';	
			
			$find[] = "<div id='contentNB'>";
			
			$get_title = $DB->query("SELECT title FROM exp_weblog_titles 
				WHERE entry_id = " . $DB->escape_str($_GET['reedirect_entry_id']) . " LIMIT 1");
			$title = $get_title->row['title'];
			
			$message = "<div id='contentNB'>
			".'<div id="entry_reedirect_message"><div>'.$DSP->span('success');
			if($_GET['U'] == 'new')
			{
				$message .= $LANG->line('entry_has_been_added');
			}
			elseif($_GET['U'] == 'updated')
			{	
				$message .= $LANG->line('entry_has_been_updated');
			}

			if($_GET['M'] != 'edit_entry')
			{
				$message .= ': '.$DSP->span_c();
				$message .= $DSP->qspan('defaultBold', $title).
				$DSP->span('defaultSmall').$DSP->qspan('defaultLight', '&nbsp;|&nbsp;').
				$DSP->anchor(BASE.AMP.'C=edit'.AMP.'M=edit_entry'.AMP.
					'weblog_id='.$_GET['weblog_id'].AMP.'entry_id='.$_GET['reedirect_entry_id'], 
					$LANG->line('edit_this_entry')).
				$DSP->span_c();
			}
			else
			{
				$message .= $DSP->span_c();
			}
			
			$message .= '<a href="#" id="reedirectClose" title="Hide this notice">&times;</a>'
				.$DSP->div_c().$DSP->div_c();
			
			$replace[] = $message;
			
			$find[] = '</head>';
			
			$replace[] = '
				<style type="text/css">
				#entry_reedirect_message {
					border-bottom:1px solid #CCC9A4;
					position: fixed;
					width: 100%;
					left: 0;
					top: 0;
					display: none;
				}
				* html #entry_reedirect_message {
					position: absolute;
				}
				#entry_reedirect_message div {
					padding: 10px 15px;
					background-color: rgb(252,252,222);
				}
				#entry_reedirect_message > div {
					background-color: rgba(252,252,222,0.95);	
				}
				a#reedirectClose {
					display: block;
					position: absolute;
					right: 15px;
					top: 7px;
					padding: 0 3px;
					border: 1px solid #CCC9A4;
					font-size: 18px;
					line-height: 18px;
					color: #CCC9A4;
					text-decoration: none;
					-webkit-border-radius: 3px;
					-moz-border-radius: 3px;
					border-radius: 3px;
				}
				a#reedirectClose:hover {
					background-color: #CCC9A4;
					color: rgb(252,252,222);
				}
				</style>
				
				<script type="text/javascript">
					<!-- Added by Entry REEdirect -->
					$(document).ready(function(){
						$("div#entry_reedirect_message").slideDown("normal"'.$auto_hide.');
						$("a#reedirectClose").click(function(){
							$("div#entry_reedirect_message").slideUp();
							return false;
						});
					});
				</script>
				
				</head>
			';
			
			$out = str_replace($find, $replace, $out);
				
		}
		
		// May as well make the other success messages a little nicer
		// (Delete and multi-entry category update. No message for multi-entry edit for some reason.)
		if( isset($_GET['C']) && $_GET['C'] == 'edit' && isset($_GET['M']) && ($_GET['M'] == 'delete_entries' || $_GET['M'] == 'entry_category_update'))
		{
			$target = "/<div class='success' >\s*([^<]*)\s*/";
			$message = '<div class="box"><span class="success">$1</span>';
			$out = preg_replace($target, $message, $out);
		}
		
		return $out;

	}	
    // END    
	
   
	// --------------------------------
	//  Activate Extension
	// --------------------------------
	
	function activate_extension()
	{
	    global $DB;

	    $hooks = array(
	    	'weblog_standalone_insert_entry' => 'grab_return_url',
	    	'submit_new_entry_redirect' => 'redirect_location',
	    	'show_full_control_panel_end' => 'cp_changes'
	    );
	    
	    foreach($hooks as $hook => $method)
	    {
		    $DB->query($DB->insert_string('exp_extensions',
		    	array(
					'extension_id' => '',
			        'class'        => "Entry_reedirect",
			        'method'       => $method,
			        'hook'         => $hook,
			        'settings'     => serialize($this->settings),
			        'priority'     => 10,
			        'version'      => $this->version,
			        'enabled'      => "y"
					)
				)
			);
	    }		
	}
	// END


	// --------------------------------
	//  Update Extension
	// --------------------------------  
	
	function update_extension($current='')
	{
	    global $DB;
	    
	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }
	    
	    $DB->query("UPDATE exp_extensions 
	                SET version = '".$DB->escape_str($this->version)."' 
	                WHERE class = 'Entry_reedirect'");
	}
	// END
	
	
	// --------------------------------
	//  Disable Extension
	// --------------------------------
	
	function disable_extension()
	{
	    global $DB;
	    
	    $DB->query("DELETE FROM exp_extensions WHERE class = 'Entry_reedirect'");
	}
	// END


}
// END CLASS