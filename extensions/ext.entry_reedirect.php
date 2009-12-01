<?php

if(!defined('EXT'))
{
	exit('Invalid file request');
}

class Entry_reedirect
{
	var $settings        = array();
	var $name            = 'Entry REEdirect';
	var $version         = '1.0.5';
	var $description     = 'Choose where users are redirected after publishing/updating new entries in the control panel.';
	var $settings_exist  = 'y';
	var $docs_url        = 'http://github.com/amphibian/ext.entry_reedirect.ee_addon';

	
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
	
	function settings_form($current)
	{	    
	    global $DB, $DSP, $IN, $LANG, $PREFS;
				
		$locations = array('default','new','remain','edit');
		
		// Check to see if the Structure module is installed
		$structure = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = 'Structure'");
		if($structure->num_rows > 0)
		{
			$locations[] = 'structure';
		}
		
		$weblogs = $DB->query("SELECT blog_title, weblog_id 
			FROM exp_weblogs 
			WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' 
			ORDER BY blog_title ASC");
								
		// Start building the page
		$DSP->crumbline = TRUE;
		
		$DSP->title  = $LANG->line('extension_settings');
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=admin'.AMP.'area=utilities', $LANG->line('utilities')).
		$DSP->crumb_item($DSP->anchor(BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=extensions_manager', $LANG->line('extensions_manager')));
		$DSP->crumb .= $DSP->crumb_item($this->name);
		
		$DSP->right_crumb($LANG->line('disable_extension'), BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=toggle_extension_confirm'.AMP.'which=disable'.AMP.'name='.$IN->GBL('name'));
		
		$DSP->body = $DSP->form_open(
			array(
				'action' => 'C=admin'.AMP.'M=utilities'.AMP.'P=save_extension_settings',
				'name'   => 'entry_reedirect',
				'id'     => 'entry_reedirect'
			),
			array('name' => get_class($this))
		);
		
		// $DSP->body .=	'<pre>'.print_r($current, TRUE).'</pre>';
	
		$DSP->body .=   $DSP->heading($this->name.NBS.$DSP->qspan('defaultLight', $this->version), 1);
		
		// Open the table
		$DSP->body .=   $DSP->table('tableBorder', '0', '', '100%');
		$DSP->body .=  	$DSP->tr();
		
		$DSP->body .=   $DSP->td('tableHeading', '30%');
		$DSP->body .=   ucfirst($PREFS->ini('weblog_nomenclature'));
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->td('tableHeading', '30%');
		$DSP->body .=   $LANG->line('redirect_after_new');
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->td('tableHeading', '30%');
		$DSP->body .=   $LANG->line('redirect_after_update');
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->tr_c();
		
		// Global location controls
		$DSP->body .=  	$DSP->tr();
		
		$DSP->body .=   '<td class="box" style="border-width: 0 0 1px; font-weight: bold; margin: 0; padding: 6px;">';
		$DSP->body .=   $LANG->line('global_redirect');
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   '<td class="box" style="border-width: 0 0 1px; margin: 0; padding: 6px;">';
		$DSP->body .= 	$DSP->input_select_header('global_new_entry_redirect');
		$DSP->body .= $DSP->input_select_option('none', $LANG->line('none'), 1);				
		foreach($locations as $location)
		{
			$DSP->body .= $DSP->input_select_option($location, $LANG->line($location.'_lang'));		
		}
		$DSP->body .= 	$DSP->input_select_footer();
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   '<td class="box" style="border-width: 0 0 1px; margin: 0; padding: 6px;">';
		$DSP->body .= 	$DSP->input_select_header('global_updated_entry_redirect');
		$DSP->body .= 	$DSP->input_select_option('none', $LANG->line('none'), 1);
		foreach($locations as $location)
		{
			$DSP->body .= $DSP->input_select_option($location, $LANG->line($location.'_lang'));			
		}
		$DSP->body .= 	$DSP->input_select_footer();
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->tr_c();		
		
		// Per-weblog settings
		$i = 1;
		foreach($weblogs->result as $value)
		{
			$class = ($i % 2) ? 'tableCellTwo' : 'tableCellOne';
			extract($value);
			
			$DSP->body .=  	$DSP->tr();
			
			$DSP->body .=   $DSP->td($class);
			$DSP->body .=   $blog_title;
			$DSP->body .=   $DSP->td_c();
			
			$DSP->body .=   $DSP->td($class);
			$DSP->body .= 	$DSP->input_select_header('new_redirect_weblog_id_'.$weblog_id);
			foreach($locations as $location)
			{
				$DSP->body .= $DSP->input_select_option($location, $LANG->line($location.'_lang'), ( isset($current['new_redirect_weblog_id_'.$weblog_id]) && $current['new_redirect_weblog_id_'.$weblog_id] == $location) ? 1 : '');		
			}
			$DSP->body .= 	$DSP->input_select_footer();
			$DSP->body .=   $DSP->td_c();
			
			$DSP->body .=   $DSP->td($class);
			$DSP->body .= 	$DSP->input_select_header('updated_redirect_weblog_id_'.$weblog_id);
			foreach($locations as $location)
			{
				$DSP->body .= $DSP->input_select_option($location, $LANG->line($location.'_lang'), ( isset($current['updated_redirect_weblog_id_'.$weblog_id]) && $current['updated_redirect_weblog_id_'.$weblog_id] == $location) ? 1 : '');			
			}
			$DSP->body .= 	$DSP->input_select_footer();
			$DSP->body .=   $DSP->td_c();
			
			$DSP->body .=   $DSP->tr_c();
			
			$i++;
		}
		
		// Setting for message display
		$DSP->body .=  	$DSP->tr();
		
		$DSP->body .=   '<td class="box" style="border-width: 0 0 1px; margin: 0; padding: 6px;">';
		$DSP->body .=   $LANG->line('hide_success_message');
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   '<td colspan="2" class="box" style="border-width: 0 0 1px; margin: 0; padding: 6px;">';
		$DSP->body .= 	$DSP->input_text('hide_success_message', (isset($current['hide_success_message'])) ? $current['hide_success_message'] : '5', 10, '2', '', '50px');
		$DSP->body .=   $DSP->td_c();
				
		$DSP->body .=   $DSP->tr_c();		
	    
		// Wrap it up
		$DSP->body .=   $DSP->table_c();
		$DSP->body .=   $DSP->qdiv('itemWrapperTop', $DSP->input_submit());
		$DSP->body .=   $DSP->form_c();	  
	}
	// END
	
	
	function save_settings()
	{
		global $DB;

		// Get all settings
		$settings = $this->get_settings(TRUE);
		
		unset($_POST['name']);
		unset($_POST['global_new_entry_redirect']);
		unset($_POST['global_updated_entry_redirect']);
				
		// Only update the settings we just posted
		// (Leave other sites' settings alone)
		foreach($_POST as $k => $v)
		{
			$settings[$k] = $v;
		}
			
		$data = array('settings' => addslashes(serialize($settings)));
		$update = $DB->update_string('exp_extensions', $data, "class = 'Entry_reedirect'");
		$DB->query($update);
	}

	
	function get_settings($all_sites = FALSE)
	{
		global $DB, $PREFS, $REGX;
		$site = $PREFS->ini('site_id');

		$get_settings = $DB->query("SELECT settings FROM exp_extensions WHERE class = 'Entry_reedirect' LIMIT 1");
		if ($get_settings->num_rows > 0 && $get_settings->row['settings'] != '')
        {
        	$settings = $REGX->array_stripslashes(unserialize($get_settings->row['settings']));
        	$settings = ($all_sites == TRUE) ? $settings : $settings[$site];
        }
        else
        {
        	$settings = array();
        }
        return $settings;		
	}		
	
	
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
			$redirect = (!empty($this->settings) && isset($this->settings[$type.'_redirect_weblog_id_'.$data['weblog_id']])) ? $this->settings[$type.'_redirect_weblog_id_'.$data['weblog_id']] : '';
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
						case 'structure':
							$location = BASE.AMP.
							'C=modules'.AMP.
							'M=Structure'.AMP.
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
				
		global $DB, $EXT, $DSP, $IN, $LANG;
		if ($EXT->last_call !== FALSE)
		{
			$out = $EXT->last_call;
		}
		
		// Add jQuery to extension settings page
		if($IN->GBL('P') == 'extension_settings' && $IN->GBL('name') == 'entry_reedirect')
		{
			$target = '</head>';
			$js = '
			<script type="text/javascript">
			<!-- Added by Entry REEdirect -->
			jQuery.noConflict();
			jQuery(document).ready(function($)
			{
				$("select[name=global_new_entry_redirect]").change(function(){
					var setValue = $(this).val();
					if(setValue != "none")
					{
						$("select[name^=new_redirect] option[value=" + setValue + "]").attr("selected", "selected");
						$("select[name^=new_redirect]").attr("disabled", "disabled"); 
					}
					else
					{
						$("select[name^=new_redirect]").removeAttr("disabled");
					}						
				});
				$("select[name=global_updated_entry_redirect]").change(function(){
					var setValue = $(this).val();
					if(setValue != "none")
					{
						$("select[name^=updated_redirect] option[value=" + setValue + "]").attr("selected", "selected");
						$("select[name^=updated_redirect]").attr("disabled", "disabled"); 
					}
					else
					{
						$("select[name^=updated_redirect]").removeAttr("disabled");
					}
				});
				$("form#entry_reedirect").submit(function(){
					$("select[name*=redirect_weblog_id]").removeAttr("disabled");
				});
			});
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
					#entry_reedirect_message { border-bottom:1px solid #CCC9A4; position: fixed; width: 100%; left: 0; top: 0; display: none; }
					* html #entry_reedirect_message { position: absolute; }
					#entry_reedirect_message div { padding: 10px 15px; background-color: rgb(252,252,222); }
					#entry_reedirect_message > div { background-color: rgba(252,252,222,0.95); }
					a#reedirectClose { display: block; position: absolute; right: 15px; top: 7px; padding: 0 3px; border: 1px solid #CCC9A4; font-size: 18px; line-height: 18px; color: #CCC9A4; text-decoration: none; -webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px; }
					a#reedirectClose:hover { background-color: #CCC9A4; color: rgb(252,252,222); }
				</style>
				
				<script type="text/javascript">
					<!-- Added by Entry REEdirect -->
					jQuery.noConflict();
					jQuery(document).ready(function($)
					{
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
			        'settings'     => '',
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