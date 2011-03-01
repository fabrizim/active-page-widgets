<?php
function apw_form($values, $showHidden=false)
{
	$hidden_sidebars = apw_hidden_sidebars();
	?>
	<div id="active-page-widgets" class="active-page-widgets">
		<input type="hidden" name="apw_submit" value="1" />
	<?php
    foreach( apw_get_sidebars() as $key => $sidebar ){
		$sidebar_checked = ($values === true) ? true : $values['sidebars'][$key] == 'on';
		$style = !$showHidden && in_array($key, $hidden_sidebars) ? 'style="display:none;"' : '';
		?>
		<div class="sidebar-list" <?php echo $style; ?>>
			<h4>
				<span class="all-none">
					<a class="all" href="javascript:;">All</a>
					<span style="font-weight: normal;">|</span>
					<a class="none" href="javascript:;">None</a>
				</span>
				<input <?php echo $sidebar_checked ? 'checked' : ''; ?> type="checkbox" name="active_sidebar[]" value="<?php echo $key; ?>" id="active-sidebar-<?php echo $key; ?>" class="sidebar_checkbox" />
				<label for="active-sidebar-<?php echo $key; ?>"><?php echo $sidebar['name']; ?></label>
			</h4>
			<ul>
				<?php foreach( $sidebar['widgets'] as $widget ){
					$widget_checked = ($values === true) ? true : $values['widgets'][$widget['key']] == 'on';
					?>
				<li>
					<input <?php echo $widget_checked ? 'checked' : ''; ?>  type="checkbox" name="active_widget[]" value="<?php echo $widget['key']; ?>" id="active-widget-<?php echo $widget['key']; ?>" class="widget_checkbox" />
					<label for="active-widget-<?php echo $widget['key']; ?>"><?php echo $widget['name']; ?></label>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}

function apw_setup_post($id)
{
	apw_setup_type('post', $id);
}

function apw_setup_term($id)
{
    apw_setup_type('term', $id);
}

function apw_setup_type($type, $id=false)
{
	$values = apw_get_sidebars_widgets($type, $id);
	apw_setup($values);
}

function apw_setup($values)
{
	global $wp_registered_sidebars, $wp_registered_widgets;
	
	if( $values === true ){
		return;
	}
	
	foreach( $values['sidebars'] as $key => $active){
		if( !$active ) unset($wp_registered_sidebars[$key] );
	}
	foreach( $values['widgets'] as $key => $active ){
		if( !$active ) unset($wp_registered_widgets[$key]);
	}
}

function apw_get_sidebars()
{
	static $sidebars;
	if( !isset($sidebars) ){
		$sidebars = array();
		global $wp_registered_sidebars, $wp_registered_widgets;
		
		$sidebars_widgets = wp_get_sidebars_widgets();
		$widgets = array();
		foreach( $wp_registered_widgets as $key => $config){
			$widget =& $config['callback'][0];
			$num = $config['params'][0]['number'];
			$widget->_set($num);
			$instance = $widget->get_settings();
			if( is_array($instance) && isset( $instance[$num] ) ) $instance = $instance[$num];
			$widgets[$key] = array(
				'key' => $key,
				'name' => $instance['title'] == '' ? $config['name'] : $instance['title']
			);
		}
		foreach( $sidebars_widgets as $sidebar => $sidebar_widgets ){
			
			if( isset($wp_registered_sidebars[$sidebar])){
				$sidebars[$sidebar] = array(
					'name' => $wp_registered_sidebars[$sidebar]['name'],
					'widgets' => array()
				);
				foreach( $sidebar_widgets as $sidebar_widget ){
					$key = $widgets[$sidebar_widget]['key'];
					$sidebars[$sidebar]['widgets'][$key] = $widgets[$sidebar_widget];
				}
			}
		}
	}
	return $sidebars;
}


function apw_hidden_sidebars()
{
	return get_option('apw_hidden_sidebars', array());
}


/**
 * Value structure
 *
 * 	Array(
 * 		'post' => Array (
 * 	
 *			page_id => Array(
 *				'parent' => default | preset_id
 *				'sidebars' => Array(
 *					key => true | false
 *				),
 *				
 *				'widgets' => Array(
 *					key => true | false
 *				)
 *			
 *			)
 * 		),
 * 		'term' => Array(
 * 			'parent' => default | preset_id
 *			term_id => Array(
 *				'sidebars' => Array(
 *					key => true | false
 *				)
 *				'widgets' => Array(
 *					key => true | false
 *				)
 *			
 *			)
 * 		),
 * 		'default' => Array(
 * 			'sidebars' => array,
 * 			'widgets' => array
 * 		),
 * 		'home' => Array(
 * 			'parent' => default | preset_id
 * 			'sidebars' => array,
 * 			'widgets' => array
 * 		),
 * 		'search' => Array(
 * 			'parent' => default | preset_id
 * 			'sidebars' => array,
 * 			'widgets' => array
 * 		),
 * 		'perset' => Array(
 * 			preset_id => Array(
 * 				'name' => string,
 * 				'parent' => default | preset_id,
 * 				'sidebars' => array,
 * 				'widgets' => array
 * 			)
 * 		)
 * 		
 */
function apw_get_values()
{
	return get_option('apw_values', array('term' => array(), 'post' => array(), 'default' => true, 'home' => true, 'search' => true, 'preset' => array() ) );
}

function apw_get_defaults()
{
	return apw_get_sidebars_widgets('default');
}

function apw_get_post_sidebars_widgets($id)
{
	return apw_get_sidebars_widgets('post', $id);
}

function apw_set_post_sidebars_widgets($id, $values)
{
	return apw_set_sidebars_widgets('post', $id, $values);
}

function apw_get_term_sidebars_widgets($id)
{
	return apw_get_sidebars_widgets('term', $id);
}

function apw_set_term_sidebars_widgets($id, $values)
{
	return apw_set_sidebars_widgets('term', $id, $values);
}

function apw_get_sidebars_widgets($type, $id=false)
{
	$defaults = false;
	if( $type != 'default'){
		$defaults = apw_get_defaults();
	}
	$values = apw_get_sidebars_widgets_raw($type, $id);
	
	if( $values === true ){
		$values = array(
			'sidebars' => array(),
			'widgets' => array()
		);
	}
	// now loop through to get any unknown widgets / sidebars
	foreach( apw_get_sidebars() as $key => $sidebar ){
		if( !isset($values['sidebars'][$key])){
			$values['sidebars'][$key] = $defaults ? $defaults['sidebars'][$key] : true;
		}
		foreach( $sidebar['widgets'] as $name => $widget ){
			if( !isset($values['widgets'][$name] )){
				$values['widgets'][$name] = $defaults ? $defaults['widgets'][$name] : true;
			}
		}
	}
	return $values;
}

function apw_get_sidebars_widgets_raw($type, $id=false)
{
	$values = apw_get_values();
	if( !$id && isset( $values[$type]) ){
		return $values[$type];
	}
	else if( $id && isset( $values[$type][$id] ) ){		
		return $values[$type][$id];
	}
	return true;
}

function apw_set_sidebars_widgets($type, $id, $value=null)
{
	if( $value == null && $id ){
		$value = $id;
	}
	if( $type != 'default'){
		$value = apw_values_diff( $value, apw_get_defaults() );
	}
	$values = apw_get_values();
	switch( $type ){
		case 'post':
		case 'term':
			$values[$type][$id] = $value;
			break;
		default:
			$values[$type] = $value;
	}
	update_option('apw_values', $values);
}

function apw_values_diff($values, $defaults)
{
	foreach( array('sidebars','widgets') as $type){
		foreach( $values[$type] as $key => $value ){
			if( isset($defaults[$type][$key]) && $defaults[$type][$key] == $values[$type][$key] ) unset($values[$type][$key]);
		}
	}
	return $values;
}

function apw_process($term, $id=false)
{
	if( !@$_POST['apw_submit']) return;
	// save the sidebar / widget activations...
	$sidebars = $_POST['active_sidebar'];
	$widgets = $_POST['active_widget'];
    
    if( !is_array($sidebars) ) $sidebars = array();
    if( !is_array($widgets) ) $widgets = array();
	
	$values = array(
		'sidebars' => array(),
		'widgets' => array()
	);
	foreach( apw_get_sidebars() as $key => $sidebar ){
		$values['sidebars'][$key] = in_array($key, $sidebars);
		foreach( $sidebar['widgets'] as $name => $widget ){
			$values['widgets'][$name] = in_array($name, $widgets);
		}
	}
	if( !$id ) apw_set_sidebars_widgets($term, $values);
	else apw_set_sidebars_widgets($term, $id, $values);
}