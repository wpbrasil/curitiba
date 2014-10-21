<?php
/*
Plugin Name: Neto de holandes
Description: Identifica os netos de holandeses
Author: Leo Germani
Version: 0.02
 
*/
 
class pjw_user_meta {
 
 function pjw_user_meta() {
 if ( is_admin() )
 {
 add_action('show_user_profile', array(&$this,'action_show_user_profile'));
 add_action('edit_user_profile', array(&$this,'action_show_user_profile'));
 add_action('personal_options_update', array(&$this,'action_process_option_update'));
 add_action('edit_user_profile_update', array(&$this,'action_process_option_update'));
 }
 
 }
 
 function action_show_user_profile($user) 
 {
 $nacionalidades = array('Armênio' => 'armenio', 'Italiano' => 'italiano', 'Turco' => 'turco', 'Holandês' => 'holandes');
 $current = get_user_meta($user->ID, 'acendencia', true);
 
 ?>
 <h3><?php _e('Acendência') ?></h3>
 
 <table>
 <tr>
 <th><label for="something">Neto de:</label></th>
 <td>
     <?php foreach ($nacionalidades as $label => $db): ?>
     
        <input type="radio" name="acendencia" value="<?php echo $db; ?>" <?php if ($db == $current) echo 'checked'; ?> />
        <?php echo $label; ?><br />
     
     <?php endforeach; ?>
     
 </tr>
 </table>
 <?php
 }
 
 function action_process_option_update($user_id)
 {
 update_usermeta($user_id, 'acendencia', $_POST['acendencia']);
 }
}
/* Initialise outselves */
add_action('plugins_loaded', create_function('','global $pjw_user_meta_instance; $pjw_user_meta_instance = new pjw_user_meta();'));
?>
