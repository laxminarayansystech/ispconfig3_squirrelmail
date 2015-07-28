<?php
/*
Copyright (c) 2009, Scott Barr <gsbarr@gmail.com>
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice,
      this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.
    * Neither the name of ISPConfig nor the names of its contributors
      may be used to endorse or promote products derived from this software without
      specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

function squirrelmail_plugin_init_ispconfig3()
{
    global $squirrelmail_plugin_hooks;

    $base_locale = setlocale(LC_MESSAGES, "0");
    sq_setlocale(LC_MESSAGES, $base_locale . '.UTF-8');
    
    $squirrelmail_plugin_hooks['optpage_register_block']['ispconfig3'] = 'ispc_optpage_register_block';  
    $squirrelmail_plugin_hooks['login_before']['ispconfig3'] = 'ispc_autoselect'; 

    ispc_setaddr();
}

function ispc_optpage_register_block() 
{
	global $optpage_blocks;
	
	$prev = sq_change_text_domain('ispconfig3', SM_PATH . 'plugins/ispconfig3/locale');
	$ispc_label = _("acc_acc");
	
	sq_change_text_domain($prev);
	
  	$optpage_blocks[] =
    array (
           'name' => $ispc_label,
           'url'  => '../plugins/ispconfig3/ispconfig3.php',
           'desc' => _("Here you can change your password, set an autoresponder, manage your forwarding instructions and customize your spam scoring rules."),
           'js'   => FALSE);
}

function ispc_autoselect($args=null)
{
	global $imapServerAddress, $login_username;
	
	if (strpos($login_username, '@') !== false)
	{		
		require_once('config.php');
		require_once('functions.php');
		require_once('ispc_remote.class.php');
		
		if (in_array('autoselect', $ispc_config['enable_modules']))
		{		
			$_ispc_remote = new ispc_remote();
			$res = $_ispc_remote->grud_record('get','user', array('email' => $login_username));
			
			if (isset($res[0]['server_id'])) {
				$soap = $_ispc_remote->get_instance();
				
				$mail_server = $soap->server_get($_ispc_remote->get_session_id(), $res[0]['server_id'], 'server');
				if ($mail_server['ip_address'] != $imapServerAddress) 
				{
					sqsession_register($mail_server['ip_address'], 'ispc_imap_address');
					$imapServerAddress = $mail_server['ip_address'];
				}
			}
		}
	}
}

function ispc_setaddr()
{
	global $imapServerAddress;
	
	sqgetGlobalVar('ispc_imap_address', $ispc_imap_address, SQ_SESSION);
	if ($ispc_imap_address) {
		$imapServerAddress = $ispc_imap_address;
	}
}

?>
