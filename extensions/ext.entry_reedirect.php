<?php

if(!defined('EXT'))
{
	exit('Invalid file request');
}

class Entry_reedirect
{
	var $settings        = array();
	var $name            = 'Entry REEdirect';
	var $version         = '1.0.1';
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
				$settings['updated_redirect_weblog_id_'.$value['weblog_id']] = array('s', $locations, 'default');			}
		}
	    
	    return $settings;
	}
	// END
	
	
	// --------------------------------
	//  Put $_POST['return_url'] into a global variable so we can access it later
	//	(No access to this variable from the other, more appropriate hooks)
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
			
			switch($this->settings[$type.'_redirect_weblog_id_'.$data['weblog_id']])
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
					$location = BASE.AMP.
					'C=edit'.AMP.
					'M=view_entry'.AMP.
					'weblog_id='.$data['weblog_id'].AMP.
					'entry_id='.$entry_id.AMP.
					'U='.$type;				
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
		if($_GET['P'] == 'extension_settings' && $_GET['name'] == 'entry_reedirect')
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
		if(isset($_GET['reedirect_entry_id']))
		{
			$target = "<div id='contentNB'>";
			
			$get_title = $DB->query("SELECT title FROM exp_weblog_titles WHERE entry_id = " . $DB->escape_str($_GET['reedirect_entry_id']) . " LIMIT 1");
			$title = $get_title->row['title'];
			
			$message = $target.'<div id="entry_reedirect_message" class="box">'.$DSP->span('success');
			if($_GET['U'] == 'new')
			{
				$message .= $LANG->line('entry_has_been_added');
			}
			elseif($_GET['U'] == 'updated')
			{	
				$message .= $LANG->line('entry_has_been_updated');
			}
			$message .= $DSP->span_c();
			if($_GET['M'] != 'edit_entry')
			{
				$message .= $DSP->qspan('',': ' . $title).
				$DSP->span('defaultSmall').$DSP->qspan('defaultLight', '&nbsp;|&nbsp;').
				$DSP->anchor(BASE.AMP.'C=edit'.AMP.'M=edit_entry'.AMP.'weblog_id='.$_GET['weblog_id'].AMP.'entry_id='.$_GET['reedirect_entry_id'], $LANG->line('edit_this_entry')).
				$DSP->span_c();
			}
			$message .= $DSP->div_c();
			
			$out = str_replace($target, $message, $out);
			
			
			// Success message goes bye-bye after 5 seconds
			$target = '</head>';
			$js = '
			<script type="text/javascript">
			<!-- Added by Entry REEdirect -->
			$(document).ready(function()
				{
					setTimeout(function(){$("div#entry_reedirect_message").slideUp();},5000);
				}
			);
			</script>
			</head>
			';		
			
			$out = str_replace($target, $js, $out);
		
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

	    $DB->query($DB->insert_string('exp_extensions',
	    	array(
				'extension_id' => '',
		        'class'        => "Entry_reedirect",
		        'method'       => "grab_return_url",
		        'hook'         => "weblog_standalone_insert_entry",
		        'settings'     => "",
		        'priority'     => 10,
		        'version'      => $this->version,
		        'enabled'      => "y"
				)
			)
		);
		
	    $DB->query($DB->insert_string('exp_extensions',
	    	array(
				'extension_id' => '',
		        'class'        => "Entry_reedirect",
		        'method'       => "redirect_location",
		        'hook'         => "submit_new_entry_redirect",
		        'settings'     => "",
		        'priority'     => 10,
		        'version'      => $this->version,
		        'enabled'      => "y"
				)
			)
		);

	    $DB->query($DB->insert_string('exp_extensions',
	    	array(
				'extension_id' => '',
		        'class'        => "Entry_reedirect",
		        'method'       => "cp_changes",
		        'hook'         => "show_full_control_panel_end",
		        'settings'     => "",
		        'priority'     => 10,
		        'version'      => $this->version,
		        'enabled'      => "y"
				)
			)
		);
		
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
	    
	    if ($current < '1.0.1')
	    {
	    	// Do upgrade stuff here.
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