<?php
/*
 * --------------------------------------------------------------------------------------------------
 * File: functions.php
 * Description: This is the child functions file for this theme
 * It appends to the main theme functions file.
 * --------------------------------------------------------------------------------------------------
 */

/* Add video popup */
add_shortcode ( 'video_popup', '_video_popup' );
function _video_popup($atts, $content = null) {
	extract ( shortcode_atts ( array (
			'button' => '',
			'id' => '' 
	), $atts, 'video_popup' ) );
	
	$output = "
	<a class='fancybox popup-video fancybox.iframe' href='http://www.youtube.com/embed/{$id}?enablejsapi=1&amp;autoplay=1&amp;wmode=opaque'>
<img src='{$button}' style='float: right;' />
	</a>";
	
	return $output;
}
// ===========Redirect users to homepage=======
function redirect_home($redirect_to, $request, $user) {
	return home_url ();
}
// ============================================
add_filter ( 'login_redirect', 'redirect_home' );
// ===============Adjust number of form entries shows up on page========
add_filter ( 'gform_entry_page_size', 'my_entry_page_size' );
function my_entry_page_size() {
	return 20;
}
// ====================================================================

// Pre submission of test form
add_action ( 'gform_pre_submission_60', 'test_pre_submission' );
function test_pre_submission($form) {
	$account_type = $_POST ['input_9'];
	
	// echo "<script>alert($account_type')</script>";
	// var_dump($account_type);
	
	switch ($account_type) {
		
		case "Premium" :
			
			$_POST ['input_7'] = date ( "m/d/Y" );
			break;
		
		case "Free" :
			$_POST ['input_7'] = "";
			
			break;
	}
}
/*
 * //Pre submission of test form
 * add_action( 'gform_pre_submission_35', 'create_user_pre_submission' );
 *
 * function create_user_pre_submission($form){
 *
 * $account_type = $_POST['input_26'];
 *
 * //echo "<script>alert($account_type')</script>";
 * //var_dump($account_type);
 *
 * switch ($account_type){
 *
 * case "Premium":
 *
 * $_POST['input_40'] = date("m/d/Y");
 * break;
 *
 * case "Free":
 * $_POST['input_40'] = "";
 *
 * break;
 * }
 * }
 */

/*
 * Insert this script into functions.php in your WordPress theme (be cognizant of the opening and closing php tags) to allow field groups in Gravity Forms. The script will create two new field types - Open Group and Close Group. Add classes to your Open Group fields to style your groups.
 *
 * Note that there is a stray (but empty) <li> element created. It is given the class "fieldgroup_extra_li" so that you can hide it in your CSS if needed.
 */
add_filter ( "gform_add_field_buttons", "add_fieldgroup_fields" );
function add_fieldgroup_fields($field_groups) {
	foreach ( $field_groups as &$group ) {
		if ($group ["name"] == "standard_fields") {
			$group ["fields"] [] = array (
					"class" => "button",
					"value" => __ ( "Open Group", "gravityforms" ),
					"onclick" => "StartAddField('fieldgroupopen');" 
			);
			$group ["fields"] [] = array (
					"class" => "button",
					"value" => __ ( "Close Group", "gravityforms" ),
					"onclick" => "StartAddField('fieldgroupclose');" 
			);
			break;
		}
	}
	return $field_groups;
}

// Add title to the Field Group fields
add_filter ( 'gform_field_type_title', 'field_group_titles' );
function field_group_titles($type) {
	if ($type == 'fieldgroupopen') {
		return __ ( 'Open Field Group', 'gravityforms' );
	} else if ($type == 'fieldgroupclose') {
		return __ ( 'Close Field Group', 'gravityforms' );
	}
}

add_filter ( "gform_field_content", "create_gf_field_group", 10, 5 );
function create_gf_field_group($content, $field, $value, $lead_id, $form_id) {
	if (! is_admin ()) {
		if (rgar ( $field, "type" ) == "fieldgroupopen") {
			$content = "<ul><li style='display: none;'>";
		} else if (rgar ( $field, "type" ) == "fieldgroupclose") {
			$content = "</li></ul><!-- close field group --><li style='display: none;'>";
		}
	}
	return $content;
}

// Add a CSS class to the Field Group Close field so we can hide the extra <li> that is created.
add_action ( "gform_field_css_class", "close_field_group_class", 10, 3 );
function close_field_group_class($classes, $field, $form) {
	if ($field ["type"] == "fieldgroupclose") {
		$classes .= " fieldgroup_extra_li";
	}
	return $classes;
}

add_action ( "gform_editor_js_set_default_values", "field_group_default_labels" );
function field_group_default_labels() {
	?>
case "fieldgroupopen" :
field.label = "Field Group Open";
break;
case "fieldgroupclose" :
field.label = "Field Group Close";
break;
<?php
}

add_action ( "gform_editor_js", "allow_fieldgroup_settings" );
function allow_fieldgroup_settings() {
	?>
<script type='text/javascript'>
fieldSettings["fieldgroupopen"] = fieldSettings["text"] + ", .cssClass";
fieldSettings["fieldgroupclose"] = fieldSettings["text"] + ", .cssClass";
</script>
<?php
}

/**
 * Gravity Wiz // Require Minimum Character Limit for Gravity Forms
 *
 * Adds support for requiring a minimum number of characters for text-based Gravity Form fields.
 *
 * @version 1.0
 * @author David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link http://gravitywiz.com/...
 * @copyright 2013 Gravity Wiz
 *           
 */
class GW_Minimum_Characters {
	public function __construct($args = array()) {
		
		// make sure we're running the required minimum version of Gravity Forms
		if (! property_exists ( 'GFCommon', 'version' ) || ! version_compare ( GFCommon::$version, '1.7', '>=' ))
			return;
			
			// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args ( $args, array (
				'form_id' => false,
				'field_id' => false,
				'min_chars' => 0,
				'max_chars' => false,
				'validation_message' => false,
				'min_validation_message' => __ ( 'Please enter at least %s characters.' ),
				'max_validation_message' => __ ( 'You may only enter %s characters.' ) 
		) );
		
		extract ( $this->_args );
		
		if (! $form_id || ! $field_id || ! $min_chars)
			return;
			
			// time for hooks
		add_filter ( "gform_field_validation_{$form_id}_{$field_id}", array (
				$this,
				'validate_character_count' 
		), 10, 4 );
	}
	public function validate_character_count($result, $value, $form, $field) {
		$char_count = strlen ( $value );
		$is_min_reached = $this->_args ['min_chars'] !== false && $char_count >= $this->_args ['min_chars'];
		$is_max_exceeded = $this->_args ['max_chars'] !== false && $char_count > $this->_args ['max_chars'];
		
		if (! $is_min_reached) {
			
			$message = $this->_args ['validation_message'];
			if (! $message)
				$message = $this->_args ['min_validation_message'];
			
			$result ['is_valid'] = false;
			$result ['message'] = sprintf ( $message, $this->_args ['min_chars'] );
		} else if ($is_max_exceeded) {
			
			$message = $this->_args ['max_validation_message'];
			if (! $message)
				$message = $this->_args ['validation_message'];
			
			$result ['is_valid'] = false;
			$result ['message'] = sprintf ( $message, $this->_args ['max_chars'] );
		}
		
		return $result;
	}
}

// Configuration

new GW_Minimum_Characters ( array (
		'form_id' => 35,
		'field_id' => 18,
		'min_chars' => 6,
		'max_chars' => 15,
		'min_validation_message' => __ ( 'Oops! You need to enter at least %s characters.' ),
		'max_validation_message' => __ ( 'Oops! You can only enter up to %s characters.' ) 
) );

new GW_Minimum_Characters ( array (
		'form_id' => 41,
		'field_id' => 1,
		'min_chars' => 6,
		'max_chars' => 15,
		'min_validation_message' => __ ( 'Oops! You need to enter at least %s characters.' ),
		'max_validation_message' => __ ( 'Oops! You can only enter up to %s characters.' ) 
) );

/**
 * Gravity Wiz // Gravity Perks // Get Sum of Nested Form Fields
 *
 * Get the sum of a column from a Gravity Forms List field.
 *
 * @version 1.1
 * @author David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link http://gravitywiz.com/...
 * @copyright 2014 Gravity Wiz
 *           
 */
class GPNF_Field_Sum {
	private static $script_output = false;
	public function __construct($args = array()) {
		
		// make sure we're running the required minimum version of Gravity Forms
		if (! property_exists ( 'GFCommon', 'version' ) || ! version_compare ( GFCommon::$version, '1.8', '>=' ))
			return;
			
			// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args ( $args, array (
				'form_id' => false,
				'nested_form_field_id' => false,
				'nested_field_id' => false,
				'target_field_id' => false 
		) );
		
		extract ( $this->_args );
		
		// time for hooks
		add_action ( "gform_register_init_scripts_{$form_id}", array (
				$this,
				'register_init_script' 
		) );
		add_action ( "gform_pre_render_{$form_id}", array (
				$this,
				'maybe_output_script' 
		) );
	}
	public function register_init_script($form) {
		$args = array (
				'formId' => $this->_args ['form_id'],
				'nestedFormFieldId' => $this->_args ['nested_form_field_id'],
				'nestedFieldId' => $this->_args ['nested_field_id'],
				'targetFieldId' => $this->_args ['target_field_id'] 
		);
		
		$script = 'new GPNFFieldSum( ' . json_encode ( $args ) . ' );';
		$slug = "gpnf_column_sum_{$this->_args['form_id']}_{$this->_args['target_field_id']}";
		
		GFFormDisplay::add_init_script ( $form ['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
	}
	public function maybe_output_script($form) {
		if (! self::$script_output)
			$this->script ();
		
		return $form;
	}
	public function script() {
		?>

<script type="text/javascript">
 
var GPNFFieldSum;
 
( function( $ ){
 
GPNFFieldSum = function( args ) {
 
var self = this;
 
// copy all args to current object: formId, fieldId
for( prop in args ) {
if( args.hasOwnProperty( prop ) )
self[prop] = args[prop];
}
 
self.init = function() {
 
var gpnf = $( '#gform_wrapper_' + self.formId ).data( 'GPNestedForms_' + self.nestedFormFieldId );
 
gpnf.viewModel.entries.subscribe( function( newValue ) {
self.updateSum( newValue, self.nestedFieldId, self.targetFieldId, self.formId )
} );
 
self.updateSum( gpnf.viewModel.entries(), self.nestedFieldId, self.targetFieldId, self.formId );
 
}
 
self.calculateSum = function( entries, fieldId ) {
 
var total = 0;
 
for( var i = 0; i < entries.length; i++ ) {
 
var count = gformToNumber( entries[i][fieldId] ? entries[i][fieldId] : 0 );
 
console.log( count );
 
if( ! isNaN( parseFloat( count ) ) )
total += parseFloat( count );
 
}
 
return total;
}
 
self.updateSum = function( entries, nestedFieldId, targetFieldId, formId ) {
 
var total = self.calculateSum( entries, nestedFieldId );
 
$( '#input_' + formId + '_' + targetFieldId ).val( total ).change();
 
}
 
self.init();
 
}
 
} )( jQuery );
 
</script>

<?php
	}
}

// Configuration

new GPNF_Field_Sum ( array (
		'form_id' => 37,
		'nested_form_field_id' => 7,
		'nested_field_id' => 8,
		'target_field_id' => 10 
) );

new GPNF_Field_Sum ( array (
		'form_id' => 49,
		'nested_form_field_id' => 7,
		'nested_field_id' => 8,
		'target_field_id' => 10 
) );

/**
 * Calculation Subtotal Merge Tag
 *
 * Adds a {subtotal} merge tag which calculates the subtotal of the form. This merge tag can only be used
 * within the "Formula" setting of Calculation-enabled fields (i.e. Number, Calculated Product).
 *
 * @author David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link http://gravitywiz.com/subtotal-merge-tag-for-calculations/
 * @copyright 2013 Gravity Wiz
 *           
 */
class GWCalcSubtotal {
	public static $merge_tag = '{subtotal}';
	function __construct() {
		
		// front-end
		add_filter ( 'gform_pre_render', array (
				$this,
				'maybe_replace_subtotal_merge_tag' 
		) );
		add_filter ( 'gform_pre_validation', array (
				$this,
				'maybe_replace_subtotal_merge_tag_submission' 
		) );
		
		// back-end
		add_filter ( 'gform_admin_pre_render', array (
				$this,
				'add_merge_tags' 
		) );
	}
	
	/**
	 * Look for {subtotal} merge tag in form fields 'calculationFormula' property.
	 * If found, replace with the
	 * aggregated subtotal merge tag string.
	 *
	 * @param mixed $form        	
	 *
	 */
	function maybe_replace_subtotal_merge_tag($form, $filter_tags = false) {
		foreach ( $form ['fields'] as &$field ) {
			if (current_filter () == 'gform_pre_render' && rgar ( $field, 'origCalculationFormula' ))
				$field ['calculationFormula'] = $field ['origCalculationFormula'];
			if (! self::has_subtotal_merge_tag ( $field ))
				continue;
			
			$subtotal_merge_tags = self::get_subtotal_merge_tag_string ( $form, $field, $filter_tags );
			$field ['origCalculationFormula'] = $field ['calculationFormula'];
			$field ['calculationFormula'] = str_replace ( self::$merge_tag, $subtotal_merge_tags, $field ['calculationFormula'] );
		}
		
		return $form;
	}
	function maybe_replace_subtotal_merge_tag_submission($form) {
		return $this->maybe_replace_subtotal_merge_tag ( $form, true );
	}
	
	/**
	 * Get all the pricing fields on the form, get their corresponding merge tags and aggregate them into a formula that
	 * will yeild the form's subtotal.
	 *
	 * @param mixed $form        	
	 *
	 */
	static function get_subtotal_merge_tag_string($form, $current_field, $filter_tags = false) {
		$pricing_fields = self::get_pricing_fields ( $form );
		$product_tag_groups = array ();
		foreach ( $pricing_fields ['products'] as $product ) {
			
			$product_field = rgar ( $product, 'product' );
			$option_fields = rgar ( $product, 'options' );
			$quantity_field = rgar ( $product, 'quantity' );
			
			// do not include current field in subtotal
			if ($product_field ['id'] == $current_field ['id'])
				continue;
			
			$product_tags = GFCommon::get_field_merge_tags ( $product_field );
			$quantity_tag = 1;
			
			// if a single product type, only get the "price" merge tag
			if (in_array ( GFFormsModel::get_input_type ( $product_field ), array (
					'singleproduct',
					'calculation',
					'hiddenproduct' 
			) )) {
				
				// single products provide quantity merge tag
				if (empty ( $quantity_field ) && ! rgar ( $product_field, 'disableQuantity' ))
					$quantity_tag = $product_tags [2] ['tag'];
				
				$product_tags = array (
						$product_tags [1] 
				);
			}
			
			// if quantity field is provided for product, get merge tag
			if (! empty ( $quantity_field )) {
				$quantity_tag = GFCommon::get_field_merge_tags ( $quantity_field );
				$quantity_tag = $quantity_tag [0] ['tag'];
			}
			if ($filter_tags && ! self::has_valid_quantity ( $quantity_tag ))
				continue;
			$product_tags = wp_list_pluck ( $product_tags, 'tag' );
			$option_tags = array ();
			foreach ( $option_fields as $option_field ) {
				
				if (is_array ( $option_field ['inputs'] )) {
					
					$choice_number = 1;
					
					foreach ( $option_field ['inputs'] as &$input ) {
						
						// hack to skip numbers ending in 0. so that 5.1 doesn't conflict with 5.10
						if ($choice_number % 10 == 0)
							$choice_number ++;
						
						$input ['id'] = $option_field ['id'] . '.' . $choice_number ++;
					}
				}
				
				$new_options_tags = GFCommon::get_field_merge_tags ( $option_field );
				if (! is_array ( $new_options_tags ))
					continue;
				
				if (GFFormsModel::get_input_type ( $option_field ) == 'checkbox')
					array_shift ( $new_options_tags );
				
				$option_tags = array_merge ( $option_tags, $new_options_tags );
			}
			
			$option_tags = wp_list_pluck ( $option_tags, 'tag' );
			
			$product_tag_groups [] = '( ( ' . implode ( ' + ', array_merge ( $product_tags, $option_tags ) ) . ' ) * ' . $quantity_tag . ' )';
		}
		
		$shipping_tag = 0;
		/*
		 * Shipping should not be included in subtotal, correct?
		 * if( rgar( $pricing_fields, 'shipping' ) ) {
		 * $shipping_tag = GFCommon::get_field_merge_tags( rgars( $pricing_fields, 'shipping/0' ) );
		 * $shipping_tag = $shipping_tag[0]['tag'];
		 * }
		 */
		
		$pricing_tag_string = '( ( ' . implode ( ' + ', $product_tag_groups ) . ' ) + ' . $shipping_tag . ' )';
		
		return $pricing_tag_string;
	}
	/**
	 * Get all pricing fields from a given form object grouped by product and shipping with options nested under their
	 * respective products.
	 *
	 * @param mixed $form        	
	 *
	 */
	static function get_pricing_fields($form) {
		$product_fields = array ();
		
		foreach ( $form ["fields"] as $field ) {
			
			if ($field ["type"] != 'product')
				continue;
			
			$option_fields = GFCommon::get_product_fields_by_type ( $form, array (
					"option" 
			), $field ['id'] );
			
			// can only have 1 quantity field
			$quantity_field = GFCommon::get_product_fields_by_type ( $form, array (
					"quantity" 
			), $field ['id'] );
			$quantity_field = rgar ( $quantity_field, 0 );
			
			$product_fields [] = array (
					'product' => $field,
					'options' => $option_fields,
					'quantity' => $quantity_field 
			);
		}
		
		$shipping_field = GFCommon::get_fields_by_type ( $form, array (
				"shipping" 
		) );
		
		return array (
				"products" => $product_fields,
				"shipping" => $shipping_field 
		);
	}
	static function has_valid_quantity($quantity_tag) {
		if (is_numeric ( $quantity_tag )) {
			
			$qty_value = $quantity_tag;
		} else {
			
			// extract qty input ID from the merge tag
			preg_match_all ( '/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi', $quantity_tag, $matches, PREG_SET_ORDER );
			$qty_input_id = rgars ( $matches, '0/1' );
			$qty_value = rgpost ( 'input_' . str_replace ( '.', '_', $qty_input_id ) );
		}
		return intval ( $qty_value ) > 0;
	}
	function add_merge_tags($form) {
		$label = __ ( 'Subtotal', 'gravityforms' );
		
		?>

<script type="text/javascript">
 
// for the future (not yet supported for calc field)
gform.addFilter("gform_merge_tags", "gwcs_add_merge_tags");
function gwcs_add_merge_tags( mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option ) {
mergeTags["pricing"].tags.push({ tag: '<?php echo self::$merge_tag; ?>', label: '<?php echo $label; ?>' });
return mergeTags;
}
 
// hacky, but only temporary
jQuery(document).ready(function($){
 
var calcMergeTagSelect = $('#field_calculation_formula_variable_select');
calcMergeTagSelect.find('optgroup').eq(0).append( '<option value="<?php echo self::$merge_tag; ?>"><?php echo $label; ?></option>' );
 
});
 
</script>

<?php
		// return the form object from the php hook
		return $form;
	}
	static function has_subtotal_merge_tag($field) {
		// check if form is passed
		if (isset ( $field ['fields'] )) {
			
			$form = $field;
			foreach ( $form ['fields'] as $field ) {
				if (self::has_subtotal_merge_tag ( $field ))
					return true;
			}
		} else {
			
			if (isset ( $field ['calculationFormula'] ) && strpos ( $field ['calculationFormula'], self::$merge_tag ) !== false)
				return true;
		}
		
		return false;
	}
}

new GWCalcSubtotal ();

/* Get Unique FormID (after submission) */
add_filter ( "gform_pre_render", "process_unique" );
function process_unique($form) {
	global $uuid;
	$uuid ['form_id'] = $form ['id'];
	
	switch ($form ['id']) {
		case 15 : // form ID for custom brokerage & clearance
			$uuid ['field_id'] = 28; // field ID on the form custom brokerage & clearance
			break;
		case 14 : // form ID
			$uuid ['field_id'] = 85; // field ID on the form
			break;
		case 6 : // form ID
			$uuid ['field_id'] = 114; // field ID on the form
			break;
	}
	
	add_filter ( "gform_field_value_uuid", "get_unique" );
	return $form;
}
function get_unique() {
	global $uuid;
	
	$form_id = $uuid ['form_id'];
	$field_id = $uuid ['field_id'];
	
	global $wpdb;
	
	do {
		$formid = $form_id; // get ID of the form
		
		switch ($form_id) {
			
			case 15 :
				$prefixs = "CBC # - "; // prefix for custom brokerage & clearance form uniqueid
				break;
			case 14 :
				$prefixs = "EB # - "; // prefixs for different forms
				break;
			case 6 :
				$prefixs = "PD # - "; // prefixs for different forms
				break;
		}
		$tables = $wpdb->prefix . 'rg_lead';
		$form_count = $wpdb->get_var ( "SELECT COUNT(*) FROM $tables WHERE form_id = '$formid'" );
		// $date = date("d/m/Y");
		// $form_count = RGFormsModel::get_form_counts($formid);
		
		$unique = $form_count + 1; // count of the lead form entries incremented by one
		                           // $unique = str_pad($unique, 3, '0', STR_PAD_LEFT); // padding for number format 001,002...015 so 3 digit number format
		$unique = str_pad ( $unique, 8, '0', STR_PAD_LEFT ); // padding for number format 00000001,00000002...00000015 so 8 digit number format
		$date = date ( 'm-d-Y' );
		
		// print_r($date);
		// $unique = $prefixs . $unique . ' - ' . $date; // prefixs and the unique number //
		$unique = $prefixs . $unique; // prefixs and the unique number //
	} while ( ! check_unique ( $unique, $form_id, $field_id ) );
	// print_r($unique);
	return $unique;
}
function check_unique($unique, $form_id, $field_id) {
	global $wpdb;
	
	$table = $wpdb->prefix . 'rg_lead_detail';
	$result = $wpdb->get_var ( "SELECT value FROM $table WHERE form_id = '$form_id' AND field_number = '$field_id' AND value = '$unique'" );
	
	if (empty ( $result ))
		return true;
	
	return false;
}

add_filter ( 'gpnf_item_labels_29', 'my_item_labels_29' );
function my_item_labels_29() {
	return array (
			'singular' => __ ( 'shipment details', 'gravityperks' ),
			'plural' => __ ( 'shipment entries', 'gravityperks' ) 
	);
}

add_filter ( 'gpnf_init_script_args_29_2', 'my_nested_forms_args_29' );
function my_nested_forms_args_29($args) {
	$args ['modalWidth'] = 800;
	
	return $args;
}

add_filter ( 'gpnf_item_labels_31', 'my_item_labels_31' );
function my_item_labels_31() {
	return array (
			'singular' => __ ( 'express details', 'gravityperks' ),
			'plural' => __ ( 'shipment entries', 'gravityperks' ) 
	);
}

add_filter ( 'gpnf_init_script_args_31_3', 'my_nested_forms_args_31' );
function my_nested_forms_args_31($args) {
	$args ['modalWidth'] = 800;
	
	return $args;
}

add_filter ( 'gpnf_item_labels_34', 'my_item_labels_34' );
function my_item_labels_34() {
	return array (
			'singular' => __ ( 'shipment details', 'gravityperks' ),
			'plural' => __ ( 'shipment entries', 'gravityperks' ) 
	);
}

add_filter ( 'gpnf_init_script_args_34_3', 'my_nested_forms_args_34' );
function my_nested_forms_args_34($args) {
	$args ['modalWidth'] = 800;
	
	return $args;
}

add_filter ( 'gpnf_item_labels_37', 'my_item_labels_37' );
function my_item_labels_37() {
	return array (
			'singular' => __ ( 'a product', 'gravityperks' ),
			'plural' => __ ( 'entries', 'gravityperks' ) 
	);
}

add_filter ( "gform_product_price_37", "set_price_label", 10, 2 );
function set_price_label($sublabel, $form_id) {
	return "Cost";
}

add_filter ( 'gpnf_item_labels_49', 'my_item_labels_49' );
function my_item_labels_49() {
	return array (
			'singular' => __ ( 'a product', 'gravityperks' ),
			'plural' => __ ( 'entries', 'gravityperks' ) 
	);
}

add_filter ( "gform_product_price_49", "set_price_label2", 10, 2 );
function set_price_label2($sublabel, $form_id) {
	return "Cost";
}

add_filter ( 'gpnf_item_labels_59', 'my_item_labels_59' );
function my_item_labels_59() {
	return array (
			'singular' => __ ( 'a product', 'gravityperks' ),
			'plural' => __ ( 'entries', 'gravityperks' ) 
	);
}

add_filter ( "gform_product_price_59", "set_price_label3", 10, 2 );
function set_price_label3($sublabel, $form_id) {
	return "Cost";
}

add_filter ( 'gpnf_init_script_args_44_2', 'my_nested_forms_args_44' );
function my_nested_forms_args_44($args) {
	$args ['modalWidth'] = 800;
	
	return $args;
}

/* Show Summary of Purchase Request */
add_filter ( "gform_pre_render_37", "populate_summary_html" );
function populate_summary_html($form) {
	// this is a 3-page form with the data from page one being displayed in an html field on page 3
	$current_page = GFFormDisplay::get_current_page ( $form ["id"] );
	$html_content = "";
	if ($current_page == 3) {
		$html_content .= "<table width= 100% border='3' cellpadding='5' cellspacing='2'>";
		// $html_content .= "<table id='PSformat'>";
		$html_content .= "<tr style='background-color:#06635B; color:#ffffff'>";
		$html_content .= "  <th>UID</th>";
		$html_content .= "  <th>Product Description</th>";
		$html_content .= "  <th>Item/Part No.</th>";
		$html_content .= "  <th>Product Website</th>";
		$html_content .= "  <th>File</th>";
		$html_content .= "  <th>Supplier</th>";
		$html_content .= "  <th>Brand</th>";
		$html_content .= "  <th>Price</th>";
		$html_content .= "  <th>Qty</th>";
		$html_content .= "  <th>S&H</th>";
		$html_content .= "  <th>Total</th>";
		$html_content .= " </tr>";
		
		$child_entries = explode ( ',', $_POST ["input_7"] );
		// print_r( $child_entries );
		$nested_form_id = 36; // nested form id
		$nested_form = RGFormsModel::get_form_meta ( $nested_form_id );
		foreach ( $child_entries as $child_entry_id ) {
			$nested_lead = RGFormsModel::get_lead ( $child_entry_id );
			$nested_form_data = GFPDFEntryDetail::lead_detail_grid_array ( $nested_form, $nested_lead );
			$product_uid = $nested_form_data ['field'] ['13.UID'];
			$product_description = $nested_form_data ['field'] ['1.Product Name/Description'];
			$product_part_number = $nested_form_data ['field'] ['2.Item/Part No.'];
			$product_website = $nested_form_data ['field'] ['3.Product Website'];
			$product_file = $nested_form_data ['field'] ['9.File'] [0];
			$product_filename = substr ( strrchr ( $product_file, "/" ), 1 );
			$product_supplier = $nested_form_data ['field'] ['4.Supplier'];
			$product_brand = $nested_form_data ['field'] ['5.Brand'];
			$product_price = $nested_form_data ['products'] [6] ['price'];
			$product_quantity = $nested_form_data ['products'] [6] ['quantity'];
			$product_shipping = $nested_form_data ['products'] [10] ['price'];
			$product_total = $nested_form_data ['products_totals'] ['total'];
			
			$html_content .= "<tr>";
			$html_content .= "<td>$product_uid</td>";
			$html_content .= "<td>$product_description</td>";
			$html_content .= "<td>$product_part_number</td>";
			if (! empty ( $product_website )) {
				$html_content .= "<td><a href='" . $product_website . "' target='_blank'><font color='blue'>" . $product_website . "</font></a></td>";
			} else {
				$html_content .= "<td></td>";
			}
			
			if (! empty ( $product_file )) {
				$html_content .= "<td><a href='" . $product_file . "' target='_blank'><font color='blue'>" . $product_filename . "</font></a></td>";
			} else {
				$html_content .= "<td></td>";
			}
			
			$html_content .= "<td>$product_supplier</td>";
			$html_content .= "<td>$product_brand</td>";
			$html_content .= "<td>$product_price</td>";
			$html_content .= "<td>$product_quantity</td>";
			$html_content .= "<td>$product_shipping</td>";
			$html_content .= "<td>$" . $product_total . "</td>";
			$html_content .= "</tr>";
		}
		$html_content .= "</table>";
		foreach ( $form ["fields"] as &$field ) {
			$is_hidden = RGFormsModel::is_field_hidden ( $form, $field, array () );
			if (rgar ( $field, 'pageNumber' ) == 1) 
			// continue;
			{
				if ($is_hidden)
					continue;
					// gather form data to save into html field (id 45 on my form), exclude page break
				if (($field ["id"] != 51 && $field ["type"] != "page") && (rgar ( $field, 'pageNumber' ) != 2)) {
					// see if this is a complex field (will have inputs)
					if (rgar ( $field, "inputs" )) {
						// this is a complex fieldset (name, adress, etc.) - get individual field info
						foreach ( $field ["inputs"] as $input ) {
							if ($input ["label"] == "Price") {
								
								// get name of individual field, replace period with underscore when pulling from post
								$input_name = "input_" . str_replace ( ".", "_", $input ["id"] );
								$value = rgpost ( $input_name );
								$html_content .= "<table width= 100% border='3' cellpadding='5'>";
								$html_content .= "<tr>";
								// $html_content .= "<ul style = 'text-align:right;'>" . $field["label"] . ": " . $value ."</ul>";
								$html_content .= "<td style = 'text-align:right; width:90%'>" . $field ["label"] . "</td>";
								$html_content .= "<td style = 'text-align:right;'>" . $value . "</td>";
								$html_content .= "</tr>";
								$html_content .= "</table>";
							}
						}
					} else {
						if ($field ["label"] == "Total") {
							$html_content .= "<table width= 100% border='3' cellpadding='5'>";
							$html_content .= "<tr>";
							$html_content .= "<td style = 'text-align:right; width:90%'>" . $field ["label"] . "</td>";
							$html_content .= "<td style = 'text-align:right;'>$" . round ( rgpost ( 'input_' . $field ['id'] ), 2 ) . "</td>";
							$html_content .= "</tr>";
							$html_content .= "</table>";
						}
					}
				}
			}
			// $html_content .= "</table>";
		}
	}
	// loop back through form fields to get html field (id 45 on my form) that we are populating with the data gathered above
	foreach ( $form ["fields"] as &$field ) {
		// get html field
		if ($field ["id"] == 45) {
			// set the field content to the html
			$field ["content"] = $html_content;
		}
	}
	
	// return altered form so changes are displayed
	return $form;
}

add_filter ( 'nav_menu_link_attributes', 'update_custom_menu', 10, 3 );
function update_custom_menu($atts, $item, $args) {
	// The ID of the target menu item
	// $menu_target = 2175;
	
	// inspect $item
	if ($item->ID == 1627) {
		$atts ['data-rel'] = 'prettyPhoto[login_panel]';
	}
	
	if ($item->ID == 2288) {
		$atts ['href'] = wp_logout_url ( HOME_URL );
	}
	
	if ($item->ID == 2302) {
		$atts ['href'] = wp_logout_url ( HOME_URL );
	}
	
	if ($item->ID == 2372) {
		$atts ['rel'] = 'wp-video-lightbox';
		$atts ['href'] = 'https://swiftpac.com/show-customer-swiftpac-address?iframe=true&width=248&height=165';
	}
	
	/*
	 * if ($item->ID == 2271) {
	 * $atts['href'] = 'http://swiftpac.cargotrack.net/default.asp?action=login&user=' . $loggedin_username . . '';
	 * }
	 */
	
	return $atts;
}

// Begin Login Stuff
function add_items_to_wp_menu($items, $args) {
	global $loggedin_user;
	global $loggedin_username;
	global $loggedin_userrealname;
	global $loggedin_accounttype;
	global $loggedin_ctid;
	
	$loggedin_user = wp_get_current_user ();
	$loggedin_username = $loggedin_user->user_login;
	$loggedin_userrealname = $loggedin_user->user_firstname . " " . $loggedin_user->user_lastname;
	
	$loggedin_userid = $loggedin_user->ID;
	// $key = 'cargotrack';
	// $single = true;
	// $cargotrackpw_forlogin = get_user_meta($loggedin_userid,$key,$single);
	// $single = false;
	// $all_user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta( $loggedin_userid ) );
	// $cargotrackpw_forlogin=$all_user_meta['cargotrack_pass'];
	// $loggedin_ctid=$all_user_meta['cargotrack_id'];
	// $loggedin_accounttype=$all_user_meta['cargotrack_class'];
	
	$single = true;
	$cargotrackpw_forlogin = get_user_meta ( $loggedin_userid, 'cargotrack_pass', $single );
	$loggedin_ctid = get_user_meta ( $loggedin_userid, 'cargotrack_id', $single );
	$loggedin_accounttype = get_user_meta ( $loggedin_userid, 'cargotrack_class', $single );
	
	// session_start();
	// $_SESSION["loggedin_ctid"] = $loggedin_ctid;
	
	if (('main_navigation' === $args->theme_location) and (is_user_logged_in ())) {
		
		$items .= '<li id = "cargo-track-login">';
		$items .= '<form action="http://swiftpac.cargotrack.net/default.asp" method="post" name="form1" target="_blank" id="form1"><input type="hidden" value ="' . $loggedin_username . '" id="user" name="user"><input type="hidden" value="' . $cargotrackpw_forlogin . '" id="password" name="password" maxlength="128"><input class="cargo-track-button" type="submit" value="MANAGE YOUR CARGO" name="Submit"><input type="hidden" name="action" value="login"></form>';
		// $items .= '<form action="http://swiftpac.cargotrack.net/default.asp" onsubmit="window.open';
		// $items .= "('','ctwindow','width=1024,height=580,top=160,left=50,toolbar=no,directories=no,status=no,menubar=no,scrollbars=yes,copyhistory=no,resizable=yes')";
		// $items .= '" method="post" name="ctform" id="ctform" target="ctwindow"><input type="hidden" value ="' . $loggedin_username . '" id="user" name="user"><input type="hidden" value="' . $cargotrackpw_forlogin . '" id="password" name="password" maxlength="128"><input class="cargo-track-button" type="submit" value="WAREHOUSE" name="Submit"><input type="hidden" name="action" value="login"></form>';
		$items .= '</li>';
		/*
		 * $items .= '<li id = "cargo-track-login">';
		 * $items .='<a href="http://swiftpac.cargotrack.net/default.asp?action=login&user=' . $loggedin_username . '&password=' . $cargotrackpw_forlogin . '&iframe=true&width=1024&height=600&" rel="wp-video-lightbox[iframes]">';
		 * $items .= 'Warehouse</a></li>';
		 */
	}
	
	if (('header_navigation' === $args->theme_location) and (is_user_logged_in ())) {
		
		$loggedin_accounttype = strtoupper ( $loggedin_accounttype );
		global $account_type_ct;
		
		if ($loggedin_accounttype == 'PREMIUM') {
			
			$username = "swiftpac";
			$password = "r&dC@rG0#!4";
			$dbh = new PDO ( 'mysql:host=swiftpac.cargotrack.net;dbname=swiftpac', $username, $password );
			
			$strsql = "Select type from clients where client_id = '" . $loggedin_ctid . "'";
			
			foreach ( $dbh->query ( $strsql ) as $row ) {
				$account_type_ct = $row ['type'];
			}
			
			if ($account_type_ct !== 'PREMIUM') {
				$show_accounttype = 'PREMIUM PENDING PAYMENT <img src="https://swiftpac.com/wp-content/uploads/2014/08/premium_pending.png" alt="Premium Payment Pending">';
			} else {
				$show_accounttype = 'PREMIUM <img src="https://swiftpac.com/wp-content/uploads/2014/08/premium_member.png" alt="Premium Member">';
			}
		} elseif ($loggedin_accounttype == 'COMMERCIAL') {
			$show_accounttype = 'COMMERCIAL';
		} 		/*
		 * elseif ($loggedin_accounttype == 'PRIVATE') {
		 * $show_accounttype = 'REGULAR';
		 * }
		 */
		else {
			$show_accounttype = '';
		}
		
		$newitems = '<li id = "current-user-notif" class="topnav" style = "margin-top: 0px;">';
		if ($show_accounttype != '') {
			$newitems .= '<a href="//swiftpac.com/show-customer-swiftpac-address?iframe=true&width=248&height=165" rel="wp-video-lightbox">' . $loggedin_userrealname . ' [' . $loggedin_username . '] - ' . $show_accounttype . '</a>';
		} else {
			$newitems .= '<a href="//swiftpac.com/show-customer-swiftpac-address?iframe=true&width=248&height=165" rel="wp-video-lightbox">' . $loggedin_userrealname . ' [' . $loggedin_username . ']</a>';
		}
		$newitems .= '</li>';
		$items = $newitems . $items;
	}
	
	return $items;
}

add_filter ( 'wp_nav_menu_items', 'add_items_to_wp_menu', 10, 2 );

// End Login Stuff

// Add user ID to users
add_filter ( 'manage_users_columns', 'wti_add_user_custom_column' );
add_filter ( 'manage_users_sortable_columns', 'wti_add_user_custom_column' );
function wti_add_user_custom_column($columns) {
	$new_columns = $columns + array (
			'user_id' => 'ID' 
	);
	return $new_columns;
}

add_action ( 'manage_users_custom_column', 'wti_show_user_custom_column_content', 10, 3 );
function wti_show_user_custom_column_content($value, $column_name, $user_id) {
	if ('user_id' == $column_name)
		return $user_id;
	
	return $value;
}
function block_dashboard() {
	$file = basename ( $_SERVER ['PHP_SELF'] );
	if (is_user_logged_in () && is_admin () && ! current_user_can ( 'edit_posts' ) && $file != 'admin-ajax.php') {
		wp_redirect ( home_url () );
		exit ();
	}
}
function disable_password_reset() {
	return false;
}
add_filter ( 'allow_password_reset', 'disable_password_reset' );

// Get pickup states
add_filter ( "gform_pre_render", "populate_dropdown_pickup_state" );
add_filter ( "gform_admin_pre_render", "populate_dropdown_pickup_state" );
function populate_dropdown_pickup_state($form) {
	if ($form ["id"] != 48)
		return $form;
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$stateterms = $wpdb->get_results ( "SELECT distinct state from wp_sp_zipcodes where state in ('FL', 'NJ', 'NY', 'GA', 'TX', 'MA') order by state asc" );
	$stateitems = array ();
	$stateitems [] = array (
			"text" => __ ( 'Select state...', 'theme' ),
			"value" => '' 
	);
	foreach ( $stateterms as $stateterm )
		$stateitems [] = array (
				"text" => $stateterm->state,
				"value" => $stateterm->state 
		);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 37) {
			$field ["cssClass"] = 'pickup_state';
			$field ["choices"] = $stateitems;
		}
	}
	return $form;
}

// Get pickup zips
add_filter ( "gform_pre_render", "populate_dropdown_pickup_zip" );
add_filter ( "gform_admin_pre_render", "populate_dropdown_pickup_zip" );
function populate_dropdown_pickup_zip($form) {
	if ($form ["id"] != 48)
		return $form;
	$zipitems = array ();
	$zipitems [] = array (
			"text" => __ ( 'Select zip...', 'theme' ),
			"value" => '' 
	);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 40) {
			$field ["cssClass"] = 'pickup_zip';
			$field ["choices"] = $zipitems;
		}
	}
	return $form;
}

// Get pickup cities
add_filter ( "gform_pre_render", "populate_dropdown_pickup_city" );
add_filter ( "gform_admin_pre_render", "populate_dropdown_pickup_city" );
function populate_dropdown_pickup_city($form) {
	if ($form ["id"] != 48)
		return $form;
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select city...', 'theme' ),
			"value" => '' 
	);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 43) {
			$field ["cssClass"] = 'pickup_city';
			$field ["choices"] = $cityitems;
		}
	}
	return $form;
}
function get_pickup_zip_fn() {
	$pickupstate = $_POST ['pickupstate'];
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$zipterms = $wpdb->get_results ( "SELECT distinct zip from wp_sp_zipcodes where state = '" . $pickupstate . "'" );
	$zipitems = array ();
	$zipitems [] = array (
			"text" => __ ( 'Select zip...', 'theme' ),
			"value" => '' 
	);
	foreach ( $zipterms as $zipterm ) {
		$zipitems [] = array (
				"text" => $zipterm->zip,
				"value" => $zipterm->zip 
		);
	}
	
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select city...', 'theme' ),
			"value" => '' 
	);
	foreach ( $cityterms as $cityterm ) {
		$cityitems [] = array (
				"text" => __ ( 'Select city...', 'theme' ),
				"value" => '' 
		);
	}
	
	$array = array (
			$zipitems,
			$cityitems 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_get_pickup_zip', 'get_pickup_zip_fn' );
add_action ( 'wp_ajax_nopriv_get_pickup_zip', 'get_pickup_zip_fn' );
function get_pickup_city_fn() {
	$pickupzip = $_POST ['pickupzip'];
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$cityterms = $wpdb->get_results ( "SELECT merge_cities from wp_sp_zipcodes where zip = $pickupzip" );
	$result = "";
	foreach ( $cityterms as $cityterm ) {
		$result = $cityterm->merge_cities;
	}
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select city...', 'theme' ),
			"value" => '' 
	);
	$myArray = explode ( ', ', $result );
	foreach ( $myArray as &$value ) {
		$cityitems [] = array (
				"text" => $value,
				"value" => $value 
		);
		;
	}
	
	echo json_encode ( $cityitems );
	die ();
}
add_action ( 'wp_ajax_get_pickup_city', 'get_pickup_city_fn' );
add_action ( 'wp_ajax_nopriv_get_pickup_city', 'get_pickup_city_fn' );

// Get delivery states
add_filter ( "gform_pre_render", "populate_dropdown_delivery_state" );
add_filter ( "gform_admin_pre_render", "populate_dropdown_delivery_state" );
function populate_dropdown_delivery_state($form) {
	if ($form ["id"] != 48)
		return $form;
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$stateterms = $wpdb->get_results ( "SELECT distinct state from wp_sp_zipcodes where state in ('FL', 'NJ', 'NY', 'GA', 'TX', 'MA') order by state asc" );
	$stateitems = array ();
	$stateitems [] = array (
			"text" => __ ( 'Select state...', 'theme' ),
			"value" => 'default' 
	);
	foreach ( $stateterms as $stateterm )
		$stateitems [] = array (
				"text" => $stateterm->state,
				"value" => $stateterm->state 
		);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 52) {
			$field ["cssClass"] = 'delivery_state';
			$field ["choices"] = $stateitems;
		}
	}
	return $form;
}

// Get delivery zips
add_filter ( "gform_pre_render", "populate_dropdown_delivery_zip" );
add_filter ( "gform_admin_pre_render", "populate_dropdown_delivery_zip" );
function populate_dropdown_delivery_zip($form) {
	if ($form ["id"] != 48)
		return $form;
	$zipitems = array ();
	$zipitems [] = array (
			"text" => __ ( 'Select zip...', 'theme' ),
			"value" => 'default' 
	);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 55) {
			$field ["cssClass"] = 'delivery_zip';
			$field ["choices"] = $zipitems;
		}
	}
	return $form;
}

// Get delivery cities
add_filter ( "gform_pre_render", "populate_dropdown_delivery_city" );
add_filter ( "gform_admin_pre_render", "populate_dropdown_delivery_city" );
function populate_dropdown_delivery_city($form) {
	if ($form ["id"] != 48)
		return $form;
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select city...', 'theme' ),
			"value" => 'default' 
	);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 58) {
			$field ["cssClass"] = 'delivery_city';
			$field ["choices"] = $cityitems;
		}
	}
	return $form;
}
function get_delivery_zip_fn() {
	$deliverystate = $_POST ['deliverystate'];
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$zipterms = $wpdb->get_results ( "SELECT distinct zip from wp_sp_zipcodes where state = '" . $deliverystate . "'" );
	$zipitems = array ();
	$zipitems [] = array (
			"text" => __ ( 'Select zip...', 'theme' ),
			"value" => 'default' 
	);
	foreach ( $zipterms as $zipterm ) {
		$zipitems [] = array (
				"text" => $zipterm->zip,
				"value" => $zipterm->zip 
		);
	}
	echo json_encode ( $zipitems );
	die ();
}
add_action ( 'wp_ajax_get_delivery_zip', 'get_delivery_zip_fn' );
add_action ( 'wp_ajax_nopriv_get_delivery_zip', 'get_delivery_zip_fn' );
function get_delivery_city_fn() {
	$deliveryzip = $_POST ['deliveryzip'];
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$cityterms = $wpdb->get_results ( "SELECT merge_cities from wp_sp_zipcodes where zip = $deliveryzip" );
	$result = "";
	foreach ( $cityterms as $cityterm ) {
		$result = $cityterm->merge_cities;
	}
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select city...', 'theme' ),
			"value" => 'default' 
	);
	$myArray = explode ( ', ', $result );
	foreach ( $myArray as &$value ) {
		$cityitems [] = array (
				"text" => $value,
				"value" => $value 
		);
		;
	}
	
	echo json_encode ( $cityitems );
	die ();
}
add_action ( 'wp_ajax_get_delivery_city', 'get_delivery_city_fn' );
add_action ( 'wp_ajax_nopriv_get_delivery_city', 'get_delivery_city_fn' );

add_filter ( "gform_pre_render", "populate_rates" );
add_filter ( "gform_admin_pre_render", "populate_rates" );
function populate_rates($form) {
	if ($form ["id"] != 48)
		return $form;
	
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 62) {
			$field ['defaultValue'] = '';
		}
		if ($field ["id"] == 68) {
			$field ["cssClass"] = 'cal_freight';
		}
	}
	return $form;
}
function get_calculate_rate_fn() {
	$fieldvalues = $_POST ['fieldvalues'];
	$inputvals = explode ( ", ", $fieldvalues );
	
	$actual_weight = $inputvals [0];
	$volumetric_weight = $inputvals [1];
	$cubic_feet = $inputvals [2];
	$origin = $inputvals [3];
	$destination = $inputvals [4];
	$pkgType = $inputvals [5];
	$number_pieces = $inputvals [6];
	$wgtType = $inputvals [7];
	$pickup_zip = $inputvals [8];
	$hazardous = $inputvals [9];
	$pkg_length = $inputvals [10];
	$pkg_width = $inputvals [11];
	$pkg_height = $inputvals [12];
	
	if ($wgtType == 'kgs') {
		$actual_weight = ($actual_weight * 2.20462);
	} else {
		$actual_weight = $actual_weight;
	}
	
	$express_volumetric_weight = ((($pkg_length * $pkg_width * $pkg_height) / 133) * $number_pieces);
	
	if ($actual_weight > $express_volumetric_weight) {
		$express_given_weight = ceil ( $actual_weight );
	} else {
		$express_given_weight = ceil ( $express_volumetric_weight );
	}
	
	$new_cubic_feet = $cubic_feet;
	$temp_cubic_feet = str_replace ( ',', '', $new_cubic_feet );
	
	if (is_numeric ( $temp_cubic_feet )) {
		$new_cubic_feet = $temp_cubic_feet;
	}
	
	$new_volumetric_weight = $volumetric_weight;
	$temp_volumetric_weight = str_replace ( ',', '', $new_volumetric_weight );
	
	if (is_numeric ( $temp_volumetric_weight )) {
		$new_volumetric_weight = $temp_volumetric_weight;
	}
	
	if ($actual_weight > $new_volumetric_weight) {
		$given_weight = ceil ( $actual_weight );
	} else {
		$volumetric_weight = ceil ( $new_volumetric_weight );
		$given_weight = $volumetric_weight;
	}
	
	switch ($origin) {
		case "FL" :
			$new_origin = 'MIA';
			break;
		case "MA" :
			$new_origin = 'BOS';
			break;
		case "TX" :
			$new_origin = 'HUS';
			break;
		case "GA" :
			$new_origin = 'ATL';
			break;
		default :
			$new_origin = $origin;
	}
	
	$new_cubic_feet = ceil ( $new_cubic_feet );
	/* =================================================================================== */
	
	/* =================================================================================== */
	/* ---DB Scripts to get rates based on info entered--- */
	/* =================================================================================== */
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$mailbox_rates = $wpdb->get_results ( "SELECT base_rate, additional_rate, markup from wp_sp_rates where delivery_type = 'US MailBox' and origin = '" . $new_origin . "' and destination = '" . $destination . "' and min_measure <= " . $given_weight . " and max_measure >= " . $given_weight );
	
	$mailbox_rates2 = $wpdb->get_results ( "SELECT base_rate, min_measure, max_measure from wp_sp_rates where delivery_type = 'US MailBox' and origin = '" . $new_origin . "' and destination = '" . $destination . "'" );
	
	$ocean_rates = $wpdb->get_results ( "SELECT base_rate, additional_rate, markup from wp_sp_rates where delivery_type = 'Ocean Cargo' and origin = '" . $new_origin . "' and destination = '" . $destination . "' and min_measure <= " . $new_cubic_feet . " and max_measure >= " . $new_cubic_feet );
	
	$barrel_ocean_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = 'Ocean Cargo Barrel' and origin = '" . $new_origin . "' and destination = '" . $destination . "'" );
	
	$air_rates = $wpdb->get_results ( "SELECT base_rate, additional_rate, FSC, markup from wp_sp_rates where delivery_type = 'Air Cargo' and origin = '" . $new_origin . "' and destination = '" . $destination . "'" );
	
	$small_package_rates = $wpdb->get_results ( "SELECT base_rate, additional_rate, markup from wp_sp_rates where delivery_type = 'Small Package' and origin = '" . $new_origin . "' and destination = '" . $destination . "' and min_measure <= " . $given_weight . " and max_measure >= " . $given_weight );
	
	$express_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = 'Express' and origin = '" . $new_origin . "' and destination = '" . $destination . "' and min_measure <= " . $express_given_weight . " and max_measure >= " . $express_given_weight );
	
	$express_letter_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = 'Express Letter' and origin = '" . $new_origin . "' and destination = '" . $destination . "'" );
	
	$express_document_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = concat('Express Document',$number_pieces) and origin = '" . $new_origin . "' and destination = '" . $destination . "'" );
	
	$domestic_rates_smallpkg = $wpdb->get_results ( "SELECT base_rate, additional_rate, zones from wp_sp_rates where delivery_type like 'Domestic Small Package' and origin = " . $pickup_zip );
	
	$domestic_rates_air_ocean = $wpdb->get_results ( "SELECT base_rate, additional_rate, zones from wp_sp_rates where delivery_type like 'Domestic' and origin = " . $pickup_zip );
	
	$domestic_rates_barrel = $wpdb->get_results ( "SELECT base_rate, additional_rate, zones from wp_sp_rates where delivery_type like 'Domestic Barrel' and origin = " . $pickup_zip );
	
	$domestic_rates_express = $wpdb->get_results ( "SELECT base_rate, additional_rate, zones from wp_sp_rates where delivery_type like 'Domestic Express' and origin = " . $pickup_zip );
	/* =================================================================================== */
	
	/* =================================================================================== */
	/* ---Get the transit time info for air and ocean--- */
	/* =================================================================================== */
	$zones = $wpdb->get_results ( "SELECT distinct zones from wp_sp_rates where origin = " . $pickup_zip );
	$transit_zone = $zones [0]->zones;
	
	if (strpos ( $transit_zone, 'FL' ) !== false) {
		if ($transit_zone == 'ZONE1 FL') {
			$transit_origin = 'Miami, FL';
		} else if ($transit_zone == 'ZONE2 FL') {
			$transit_origin = 'Miami, FL';
		} else {
			$transit_origin = 'Orlando, FL';
		}
	} else {
		$transit_origin = $origin;
	}
	
	$transit_times = $wpdb->get_results ( "SELECT transit_time, depart_days from wp_sp_transit_times where origin = '" . $transit_origin . "' and destination = '" . $destination . "'" );
	
	$air_transit_time = $transit_times [0]->transit_time;
	$air_depart_days = $transit_times [0]->depart_days;
	
	$ocean_transit_time = $transit_times [1]->transit_time;
	$ocean_depart_days = $transit_times [1]->depart_days;
	/* =================================================================================== */
	
	/* =================================================================================== */
	/* ---Destination Rates--- */
	/* =================================================================================== */
	$base_rate_mailbox = $mailbox_rates [0]->base_rate;
	$additional_rate_mailbox = $mailbox_rates [0]->additional_rate;
	$markup_mailbox = $mailbox_rates [0]->markup;
	
	$base_rate_ocean = $ocean_rates [0]->base_rate;
	$additional_rate_ocean = $ocean_rates [0]->additional_rate;
	$markup_ocean = $ocean_rates [0]->markup;
	
	$base_rate_ocean_barrel = $barrel_ocean_rates [0]->base_rate;
	$markup_ocean_barrel = $barrel_ocean_rates [0]->markup;
	$PSS_ocean_barrel = $barrel_ocean_rates [0]->PSS;
	
	$base_rate_air = $air_rates [0]->base_rate;
	$additional_rate_air = $air_rates [0]->additional_rate;
	$markup_air = $air_rates [0]->markup;
	$fsc_air = $air_rates [0]->FSC;
	
	$base_rate_small_package = $small_package_rates [0]->base_rate;
	$markup_small_package = $small_package_rates [0]->markup;
	
	$base_rate_express = $express_rates [0]->base_rate;
	$markup_express = $express_rates [0]->markup;
	
	$base_rate_express_letter = $express_letter_rates [0]->base_rate;
	$markup_express_letter = $express_letter_rates [0]->markup;
	
	$base_rate_express_document = $express_document_rates [0]->base_rate;
	$markup_express_document = $express_document_rates [0]->markup;
	/* =================================================================================== */
	
	/* =================================================================================== */
	/* --Domestic Rates--- */
	/* =================================================================================== */
	/* ---Domestic Small Package Rates--- */
	$base_rate_domestic_smallpkg = $domestic_rates_smallpkg [0]->base_rate;
	$additional_rate_domestic_smallpkg = $domestic_rates_smallpkg [0]->additional_rate;
	$zones_domestic_smallpkg = $domestic_rates_smallpkg [0]->zones;
	
	$domestic_calculated_rate_smallpkg = number_format ( ($given_weight * $additional_rate_domestic_smallpkg), 2, '.', '' );
	if ($domestic_calculated_rate_smallpkg >= $base_rate_domestic_smallpkg) {
		$domestic_cost_smallpkg = $domestic_calculated_rate_smallpkg;
	} else {
		$domestic_cost_smallpkg = $base_rate_domestic_smallpkg;
	}
	
	/* ---Domestic Air and Ocean Rates--- */
	$base_rate_domestic_air_ocean = $domestic_rates_air_ocean [0]->base_rate;
	$additional_rate_domestic_air_ocean = $domestic_rates_air_ocean [0]->additional_rate;
	$zones_domestic_air_ocean = $domestic_rates_air_ocean [0]->zones;
	
	$domestic_calculated_rate_air_ocean = number_format ( ($given_weight * $additional_rate_domestic_air_ocean), 2, '.', '' );
	if ($domestic_calculated_rate_air_ocean >= $base_rate_domestic_air_ocean) {
		$domestic_cost_air_ocean = $domestic_calculated_rate_air_ocean;
	} else {
		$domestic_cost_air_ocean = $base_rate_domestic_air_ocean;
	}
	
	/* ---Domestic Barrel Rate--- */
	$base_rate_domestic_barrel = $domestic_rates_barrel [0]->base_rate;
	$additional_rate_domestic_barrel = $domestic_rates_barrel [0]->additional_rate;
	$zones_domestic_barrel = $domestic_rates_barrel [0]->zones;
	
	$domestic_calculated_rate_barrel = number_format ( ($given_weight * $additional_rate_domestic_barrel), 2, '.', '' );
	if ($domestic_calculated_rate_barrel >= $base_rate_domestic_barrel) {
		$domestic_cost_barrel = $domestic_calculated_rate_barrel;
	} else {
		$domestic_cost_barrel = $base_rate_domestic_barrel;
	}
	
	/* ---Domestic Express Rate--- */
	$base_rate_domestic_express = $domestic_rates_express [0]->base_rate;
	$additional_rate_domestic_express = $domestic_rates_express [0]->additional_rate;
	$zones_domestic_express = $domestic_rates_express [0]->zones;
	
	$domestic_calculated_rate_express = number_format ( ($express_given_weight * $additional_rate_domestic_express), 2, '.', '' );
	if ($domestic_calculated_rate_express >= $base_rate_domestic_express) {
		$domestic_cost_express = $domestic_calculated_rate_express;
	} else {
		$domestic_cost_express = $base_rate_domestic_express;
	}
	/* =================================================================================== */
	
	/* =================================================================================== */
	/* ---Calculation--- */
	/* =================================================================================== */
	
	/* ---Check if package type is barrel--- */
	if (strpos ( $pkgType, 'Barrel' ) === false) {
		
		/* ---If package type is not barrel do the following--- */
		
		/* ---US Mailbox Calculation--- */
		
		if ($new_origin == 'MIA') {
			$weight_difference = $volumetric_weight - $actual_weight;
			if ($weight_difference > 5) {
				$given_weight = ceil ( $volumetric_weight );
			} else {
				if ($actual_weight <= 0) {
					$given_weight = ceil ( $volumetric_weight );
				} else {
					$given_weight = ceil ( $actual_weight );
				}
			}
			if (empty ( $mailbox_rates2 )) {
				$us_mailbox = 'N/A';
				$us_mailbox_freight = 'N/A';
			} else {
				if ($given_weight > 150 || $given_weight <= 0) {
					$us_mailbox = 'N/A';
					$us_mailbox_freight = 'N/A';
				} else {
					if ($pickup_zip != 33166) {
						$warehouse_zip = 'No';
					} else {
						$warehouse_zip = 'Yes';
					}
					$us_mailbox = number_format ( ($base_rate_mailbox + (($given_weight - 1) * $additional_rate_mailbox)), 2, '.', '' );
					
					$wgt = $given_weight;
					$us_mailbox_freight = '0.00';
					// $pound_range_list = array();
					// $rates_list = array();
					// $freight_list = array();
					// $decremental_wgt = array();
					
					for($i = 0; $i < count ( $mailbox_rates2 ); $i ++) {
						if ($wgt <= 0) {
							break;
						} else {
							$pound_range = ($mailbox_rates2 [$i]->max_measure - $mailbox_rates2 [$i]->min_measure) + 1;
							// $pound_range_list[] = array("value" => $pound_range);
							$base_rate_mailbox2 = $mailbox_rates2 [$i]->base_rate;
							// $rates_list[] = array("value" => $base_rate_mailbox2);
							if ($wgt >= $pound_range) {
								$use_weight = $pound_range;
							} else {
								$use_weight = $wgt;
							}
							// $incremental_freight = ($use_weight * $base_rate_mailbox2);
							// $freight_list[] = array("value" => $incremental_freight);
							$us_mailbox_freight = number_format ( ($us_mailbox_freight + ($use_weight * $base_rate_mailbox2)), 2, '.', '' );
							$wgt = $wgt - $use_weight;
							// $decremental_wgt[] = array("value" => $wgt);
						}
					}
				}
			}
		} else {
			$us_mailbox = 'N/A';
			$us_mailbox_freight = 'N/A';
		}
		
		/* ---Ocean Calculation--- */
		if (empty ( $ocean_rates )) {
			$ocean_cargo = 'N/A';
		} else {
			if ($hazardous == 'true') {
				if (! ($zones_domestic_air_ocean == 'ZONE1 FL')) {
					if (! ($zones_domestic_air_ocean == 'ZONE2 FL')) {
						$ocean_cargo = number_format ( ((($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean)) * 1.5) + 75.00 + $domestic_cost_air_ocean), 2, '.', '' );
						$require_pickup = 'No';
					} else {
						$ocean_cargo = number_format ( ((($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean)) * 1.5) + 75.00), 2, '.', '' );
						$require_pickup = 'Yes';
					}
				} else {
					$ocean_cargo = number_format ( ((($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean)) * 1.5) + 75.00), 2, '.', '' );
					$require_pickup = 'Yes';
				}
			} else {
				if (! ($zones_domestic_air_ocean == 'ZONE1 FL')) {
					if (! ($zones_domestic_air_ocean == 'ZONE2 FL')) {
						$ocean_cargo = number_format ( (($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean)) + $domestic_cost_air_ocean), 2, '.', '' );
						$require_pickup = 'No';
					} else {
						$ocean_cargo = number_format ( (($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean))), 2, '.', '' );
						$require_pickup = 'Yes';
					}
				} else {
					$ocean_cargo = number_format ( (($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean))), 2, '.', '' );
					$require_pickup = 'Yes';
				}
			}
		}
		/* ---Air Calculation--- */
		if (empty ( $air_rates )) {
			$air_cargo = 'N/A';
		} else {
			if ($given_weight <= 0) {
				$air_cargo = 'N/A';
			} else {
				$air_cargo_per_lb = ($additional_rate_air + $fsc_air) * $given_weight;
				if ($air_cargo_per_lb >= $base_rate_air) {
					if ($hazardous == 'true') {
						if (! ($zones_domestic_air_ocean == 'ZONE1 FL')) {
							if (! ($zones_domestic_air_ocean == 'ZONE2 FL')) {
								$air_cargo = number_format ( ((($air_cargo_per_lb * 1.5) + 75.00) + $domestic_cost_air_ocean), 2, '.', '' );
								$require_pickup = 'No';
							} else {
								$air_cargo = number_format ( ((($air_cargo_per_lb * 1.5) + 75.00)), 2, '.', '' );
								$require_pickup = 'Yes';
							}
						} else {
							$air_cargo = number_format ( ((($air_cargo_per_lb * 1.5) + 75.00)), 2, '.', '' );
							$require_pickup = 'Yes';
						}
					} else {
						if (! ($zones_domestic_air_ocean == 'ZONE1 FL')) {
							if (! ($zones_domestic_air_ocean == 'ZONE2 FL')) {
								$air_cargo = number_format ( ($air_cargo_per_lb + $domestic_cost_air_ocean), 2, '.', '' );
								$require_pickup = 'No';
							} else {
								$air_cargo = number_format ( ($air_cargo_per_lb), 2, '.', '' );
								$require_pickup = 'Yes';
							}
						} else {
							$air_cargo = number_format ( ($air_cargo_per_lb), 2, '.', '' );
							$require_pickup = 'Yes';
						}
					}
					$airway = 'Yes';
				} else {
					if ($hazardous == 'true') {
						if (! ($zones_domestic_air_ocean == 'ZONE1 FL')) {
							if (! ($zones_domestic_air_ocean == 'ZONE2 FL')) {
								$air_cargo = number_format ( ((($base_rate_air * 1.5) + 75.00) + $domestic_cost_air_ocean), 2, '.', '' );
								$require_pickup = 'No';
							} else {
								$air_cargo = number_format ( ((($base_rate_air * 1.5) + 75.00)), 2, '.', '' );
								$require_pickup = 'Yes';
							}
						} else {
							$air_cargo = number_format ( ((($base_rate_air * 1.5) + 75.00)), 2, '.', '' );
							$require_pickup = 'Yes';
						}
					} else {
						if (! ($zones_domestic_air_ocean == 'ZONE1 FL')) {
							if (! ($zones_domestic_air_ocean == 'ZONE2 FL')) {
								$air_cargo = number_format ( ($base_rate_air + $domestic_cost_air_ocean), 2, '.', '' );
								$require_pickup = 'No';
							} else {
								$air_cargo = number_format ( ($base_rate_air), 2, '.', '' );
								$require_pickup = 'Yes';
							}
						} else {
							$air_cargo = number_format ( ($base_rate_air), 2, '.', '' );
							$require_pickup = 'Yes';
						}
					}
					$airway = 'No';
				}
			}
		}
		
		/* ---Small Package Calculation--- */
		if (empty ( $small_package_rates )) {
			$small_package = 'N/A';
		} else {
			if ($new_origin != 'MIA') {
				if ($given_weight <= 20) {
					// $small_package = number_format((($base_rate_small_package + $markup_small_package)+ $domestic_cost_smallpkg),2,'.','');
					$small_package = number_format ( ($base_rate_small_package + $markup_small_package), 2, '.', '' );
				} else {
					$small_package = 'N/A';
				}
			} else {
				$small_package = 'N/A';
			}
		}
		/* ---Express Calculation--- */
		if ($given_weight > 40) {
			$express_freight = 'N/A';
		} else {
			if ($pkgType == 'Letter') {
				if (empty ( $express_letter_rates )) {
					$express_freight = 'N/A';
				} else {
					$express_freight = number_format ( (($base_rate_express_letter + $markup_express_letter) * $number_pieces), 2, '.', '' );
				}
			} else if ($pkgType == 'Document') {
				if ($number_pieces > 5) {
					$express_freight = 'N/A';
				} else {
					if (empty ( $express_letter_rates )) {
						$express_freight = 'N/A';
					} else {
						$express_freight = number_format ( ($base_rate_express_document + $markup_express_document), 2, '.', '' );
					}
				}
			} else {
				if (empty ( $express_rates )) {
					$express_freight = 'N/A';
				} else {
					$express_freight = number_format ( (($base_rate_express + $markup_express)), 2, '.', '' );
				}
			}
		}
		
		$array = array (
				$us_mailbox,
				$ocean_cargo,
				$air_cargo,
				$small_package,
				$domestic_cost_smallpkg,
				$airway,
				$domestic_cost_air_ocean,
				$require_pickup,
				$warehouse_zip,
				$air_transit_time,
				$air_depart_days,
				$ocean_transit_time,
				$ocean_depart_days,
				$zones,
				$us_mailbox_freight,
				$express_freight,
				$domestic_cost_express 
		);
		/* $array = array($us_mailbox, $ocean_cargo, $air_cargo, $small_package,$domestic_cost_smallpkg, $airway, $domestic_cost_air_ocean, $require_pickup, $warehouse_zip, $air_transit_time, $air_depart_days,$ocean_transit_time, $ocean_depart_days,$zones); */
	} else {
		
		/* ---If package type is barrel do the following--- */
		if (empty ( $barrel_ocean_rates )) {
			$ocean_barrel = 'N/A';
		} else {
			$ocean_barrel = number_format ( ((($base_rate_ocean_barrel + $markup_ocean_barrel) * $number_pieces) + $base_rate_domestic_barrel + ($additional_rate_domestic_barrel * ($number_pieces - 1))), 2, '.', '' );
		}
		$array = array (
				$ocean_barrel,
				$ocean_transit_time,
				$ocean_depart_days 
		);
	}
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_get_calculate_rate', 'get_calculate_rate_fn' );
add_action ( 'wp_ajax_nopriv_get_calculate_rate', 'get_calculate_rate_fn' );
function ReadOnlyDims_fn() {
	$fieldvalues = $_POST ['fieldvalues'];
	$inputvals = explode ( ", ", $fieldvalues );
	
	$pkgtype = $inputvals [0];
	$nopcs = $inputvals [1];
	
	if ($pkgtype == 'Barrel Jumbo') {
		$volwgt = round ( (((22 * 22 * 36) / 166) * $nopcs), 2 );
		$cuft = round ( (((22 * 22 * 36) / 1728) * $nopcs), 2 );
	}
	if ($pkgtype == 'Barrel Super Jumbo') {
		$volwgt = round ( (((24 * 24 * 43) / 166) * $nopcs), 2 );
		$cuft = round ( (((24 * 24 * 43) / 1728) * $nopcs), 2 );
	}
	
	$array = array (
			$pkgtype,
			$nopcs,
			$volwgt,
			$cuft 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_ReadOnlyDims', 'ReadOnlyDims_fn' );
add_action ( 'wp_ajax_nopriv_ReadOnlyDims', 'ReadOnlyDims_fn' );

add_filter ( 'gpnf_item_labels_47', 'my_item_labels_47' );
function my_item_labels_47() {
	return array (
			'singular' => __ ( 'Cargo Details', 'gravityperks' ),
			'plural' => __ ( 'shipment entries', 'gravityperks' ) 
	);
}

add_filter ( 'gpnf_init_script_args_47_2', 'my_nested_forms_args_47' );
function my_nested_forms_args_47($args) {
	$args ['modalWidth'] = 900;
	
	return $args;
}

new GPNF_Field_Sum ( array (
		'form_id' => 47,
		'nested_form_field_id' => 2,
		'nested_field_id' => 69,
		'target_field_id' => 64 
) );

// ====================================================

// Get parent pickup country/warehouse
add_filter ( "gform_pre_render", "parent_populate_dropdown_pickup_country" );
add_filter ( "gform_admin_pre_render", "parent_populate_dropdown_pickup_country" );
function parent_populate_dropdown_pickup_country($form) {
	if ($form ["id"] != 47)
		return $form;
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 75) {
			$field ["cssClass"] = 'parent_pickup_country';
		}
	}
	return $form;
}

// Get parent pickup states
add_filter ( "gform_pre_render", "parent_populate_dropdown_pickup_state" );
add_filter ( "gform_admin_pre_render", "parent_populate_dropdown_pickup_state" );
function parent_populate_dropdown_pickup_state($form) {
	if ($form ["id"] != 47)
		return $form;
		// global $wpdb; //Accessing WP Database (non-WP Table) use code below.
		// $stateterms = $wpdb->get_results("SELECT distinct state from wp_sp_zipcodes where state in ('FL', 'NJ', 'NY', 'GA', 'TX', 'MA') order by state asc");
	$stateitems = array ();
	$stateitems [] = array (
			"text" => __ ( 'Select state...', 'theme' ),
			"value" => '' 
	);
	// foreach($stateterms as $stateterm)
	// $stateitems[] = array( "text" => $stateterm->state, "value" => $stateterm->state);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 78) {
			$field ["cssClass"] = 'parent_pickup_state';
			$field ["choices"] = $stateitems;
		}
	}
	return $form;
}

// Get parent pickup zips
add_filter ( "gform_pre_render", "parent_populate_dropdown_pickup_zip" );
add_filter ( "gform_admin_pre_render", "parent_populate_dropdown_pickup_zip" );
function parent_populate_dropdown_pickup_zip($form) {
	if ($form ["id"] != 47)
		return $form;
	$zipitems = array ();
	$zipitems [] = array (
			"text" => __ ( 'Select zip...', 'theme' ),
			"value" => '' 
	);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 81) {
			$field ["cssClass"] = 'parent_pickup_zip';
			$field ["choices"] = $zipitems;
		}
	}
	return $form;
}

// Get parent pickup cities
add_filter ( "gform_pre_render", "parent_populate_dropdown_pickup_city" );
add_filter ( "gform_admin_pre_render", "parent_populate_dropdown_pickup_city" );
function parent_populate_dropdown_pickup_city($form) {
	if ($form ["id"] != 47)
		return $form;
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select city...', 'theme' ),
			"value" => '' 
	);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 84) {
			$field ["cssClass"] = 'parent_pickup_city';
			$field ["choices"] = $cityitems;
		}
	}
	return $form;
}

// Function called only when warehouse locations selected in order to populate the state zip and city with default info
function get_parent_pickup_warehouse_location_fn() {
	$pickupcountry = $_POST ['parentpickupcountry'];
	
	if ($pickupcountry == 'Miami US Warehouse') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'FL', 'theme' ),
				"value" => 'FL' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( '33166', 'theme' ),
				"value" => '33166' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Miami', 'theme' ),
				"value" => 'Miami' 
		);
	} else if ($pickupcountry == 'Orlando US Warehouse') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'FL', 'theme' ),
				"value" => 'FL' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( '32804', 'theme' ),
				"value" => '32804' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Orlando', 'theme' ),
				"value" => 'Miami' 
		);
	} else if ($pickupcountry == 'United States') {
		global $wpdb; // Accessing WP Database (non-WP Table) use code below.
		$stateterms = $wpdb->get_results ( "SELECT distinct state from wp_sp_zipcodes where state in ('FL', 'NJ', 'NY', 'GA', 'TX', 'MA') order by state asc" );
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'Select state...', 'theme' ),
				"value" => '' 
		);
		foreach ( $stateterms as $stateterm )
			$stateitems [] = array (
					"text" => $stateterm->state,
					"value" => $stateterm->state 
			);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'Select zip...', 'theme' ),
				"value" => '' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Select city...', 'theme' ),
				"value" => '' 
		);
	} else {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'Select state', 'theme' ),
				"value" => '' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'Select zip...', 'theme' ),
				"value" => '' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Select city...', 'theme' ),
				"value" => '' 
		);
	}
	
	$array = array (
			$stateitems,
			$zipitems,
			$cityitems 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_get_parent_pickup_warehouse_location', 'get_parent_pickup_warehouse_location_fn' );
add_action ( 'wp_ajax_nopriv_get_parent_pickup_warehouse_location', 'get_parent_pickup_warehouse_location_fn' );
function get_parent_pickup_zip_fn() {
	$pickupstate = $_POST ['parentpickupstate'];
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$zipterms = $wpdb->get_results ( "SELECT distinct zip from wp_sp_zipcodes where state = '" . $pickupstate . "'" );
	$zipitems = array ();
	$zipitems [] = array (
			"text" => __ ( 'Select zip...', 'theme' ),
			"value" => '' 
	);
	foreach ( $zipterms as $zipterm ) {
		$zipitems [] = array (
				"text" => $zipterm->zip,
				"value" => $zipterm->zip 
		);
	}
	
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select city...', 'theme' ),
			"value" => '' 
	);
	foreach ( $cityterms as $cityterm ) {
		$cityitems [] = array (
				"text" => __ ( 'Select city...', 'theme' ),
				"value" => '' 
		);
	}
	
	$array = array (
			$zipitems,
			$cityitems 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_get_parent_pickup_zip', 'get_parent_pickup_zip_fn' );
add_action ( 'wp_ajax_nopriv_get_parent_pickup_zip', 'get_parent_pickup_zip_fn' );
function get_parent_pickup_city_fn() {
	$pickupzip = $_POST ['parentpickupzip'];
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$cityterms = $wpdb->get_results ( "SELECT merge_cities from wp_sp_zipcodes where zip = $pickupzip" );
	$result = "";
	foreach ( $cityterms as $cityterm ) {
		$result = $cityterm->merge_cities;
	}
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select city...', 'theme' ),
			"value" => '' 
	);
	$myArray = explode ( ', ', $result );
	foreach ( $myArray as &$value ) {
		$cityitems [] = array (
				"text" => $value,
				"value" => $value 
		);
		;
	}
	
	echo json_encode ( $cityitems );
	die ();
}
add_action ( 'wp_ajax_get_parent_pickup_city', 'get_parent_pickup_city_fn' );
add_action ( 'wp_ajax_nopriv_get_parent_pickup_city', 'get_parent_pickup_city_fn' );
function get_parent_location_fn() {
	$fieldvalues = $_POST ['fieldvalues'];
	$inputvals = explode ( ", ", $fieldvalues );
	
	$state = $inputvals [0];
	$zip = $inputvals [1];
	$city = $inputvals [2];
	
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$stateterms = $wpdb->get_results ( "SELECT distinct state from wp_sp_zipcodes where state in ('FL', 'NJ', 'NY', 'GA', 'TX', 'MA') order by state asc" );
	$stateitems = array ();
	$stateitems [] = array (
			"text" => __ ( 'Select state...', 'theme' ),
			"value" => '' 
	);
	foreach ( $stateterms as $stateterm )
		$stateitems [] = array (
				"text" => $stateterm->state,
				"value" => $stateterm->state 
		);
	
	$zipterms = $wpdb->get_results ( "SELECT distinct zip from wp_sp_zipcodes where state = '" . $state . "'" );
	$zipitems = array ();
	$zipitems [] = array (
			"text" => __ ( 'Select zip...', 'theme' ),
			"value" => '' 
	);
	foreach ( $zipterms as $zipterm ) {
		$zipitems [] = array (
				"text" => $zipterm->zip,
				"value" => $zipterm->zip 
		);
	}
	
	$cityterms = $wpdb->get_results ( "SELECT merge_cities from wp_sp_zipcodes where zip = $zip" );
	$result = "";
	foreach ( $cityterms as $cityterm ) {
		$result = $cityterm->merge_cities;
	}
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select city...', 'theme' ),
			"value" => '' 
	);
	$myArray = explode ( ', ', $result );
	foreach ( $myArray as &$value ) {
		$cityitems [] = array (
				"text" => $value,
				"value" => $value 
		);
	}
	
	$array = array (
			$stateitems,
			$zipitems,
			$cityitems 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_get_parent_location', 'get_parent_location_fn' );
add_action ( 'wp_ajax_nopriv_get_parent_location', 'get_parent_location_fn' );

// Warehouse weight validation class assignment
add_action ( "gform_field_css_class_50", "custom_class", 10, 3 );
function custom_class($classes, $field, $form) {
	if ($field ["id"] == 63) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 66) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 71) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 76) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 81) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 86) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 91) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 96) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 101) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 106) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 111) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 116) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 121) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 126) {
		$classes .= " wgtvalidation";
	}
	if ($field ["id"] == 131) {
		$classes .= " wgtvalidation";
	}
	
	return $classes;
}

// ====================================================

// Get origin pickup country/warehouse
add_filter ( "gform_pre_render", "origin_populate_dropdown_pickup_country" );
add_filter ( "gform_admin_pre_render", "origin_populate_dropdown_pickup_country" );
function origin_populate_dropdown_pickup_country($form) {
	if ($form ["id"] != 52)
		return $form;
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 6) {
			$field ["cssClass"] = 'origin_pickup_country';
		}
	}
	return $form;
}

// Get origin pickup states
add_filter ( "gform_pre_render", "origin_populate_dropdown_pickup_state" );
add_filter ( "gform_admin_pre_render", "origin_populate_dropdown_pickup_state" );
function origin_populate_dropdown_pickup_state($form) {
	if ($form ["id"] != 52)
		return $form;
		// global $wpdb; //Accessing WP Database (non-WP Table) use code below.
		// $stateterms = $wpdb->get_results("SELECT distinct state from wp_sp_zipcodes where state in ('FL', 'NJ', 'NY', 'GA', 'TX', 'MA', 'MD', 'PA') order by state asc");
	$stateitems = array ();
	$stateitems [] = array (
			"text" => __ ( 'Select', 'theme' ),
			"value" => '' 
	);
	// foreach($stateterms as $stateterm)
	// $stateitems[] = array( "text" => $stateterm->state, "value" => $stateterm->state);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 9) {
			$field ["cssClass"] = 'origin_pickup_state';
			$field ["choices"] = $stateitems;
		}
	}
	return $form;
}

// Get origin pickup zips
add_filter ( "gform_pre_render", "origin_populate_dropdown_pickup_zip" );
add_filter ( "gform_admin_pre_render", "origin_populate_dropdown_pickup_zip" );
function origin_populate_dropdown_pickup_zip($form) {
	if ($form ["id"] != 52)
		return $form;
	$zipitems = array ();
	$zipitems [] = array (
			"text" => __ ( 'Select', 'theme' ),
			"value" => '' 
	);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 14) {
			$field ["cssClass"] = 'origin_pickup_zip';
			$field ["choices"] = $zipitems;
		}
	}
	return $form;
}

// Get origin pickup cities
add_filter ( "gform_pre_render", "origin_populate_dropdown_pickup_city" );
add_filter ( "gform_admin_pre_render", "origin_populate_dropdown_pickup_city" );
function origin_populate_dropdown_pickup_city($form) {
	if ($form ["id"] != 52)
		return $form;
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select', 'theme' ),
			"value" => '' 
	);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 17) {
			$field ["cssClass"] = 'origin_pickup_city';
			$field ["choices"] = $cityitems;
		}
	}
	return $form;
}

// Function called only when warehouse locations selected in order to populate the state zip and city with default info
function get_origin_pickup_warehouse_location_fn() {
	$pickupcountry = $_POST ['originpickupcountry'];
	
	if ($pickupcountry == 'Miami US Warehouse') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'FL', 'theme' ),
				"value" => 'FL' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( '33166', 'theme' ),
				"value" => '33166' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Miami', 'theme' ),
				"value" => 'Miami' 
		);
	} else if ($pickupcountry == 'Orlando US Warehouse') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'FL', 'theme' ),
				"value" => 'FL' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( '32804', 'theme' ),
				"value" => '32804' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Orlando', 'theme' ),
				"value" => 'Orlando' 
		);
	} else if ($pickupcountry == 'Brooklyn NY USA') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NY', 'theme' ),
				"value" => 'NY' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( '11234', 'theme' ),
				"value" => '11234' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Brooklyn', 'theme' ),
				"value" => 'Brooklyn' 
		);
	} else if ($pickupcountry == 'Boston US Warehouse') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'MA', 'theme' ),
				"value" => 'MA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( '2136', 'theme' ),
				"value" => '2136' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Boston', 'theme' ),
				"value" => 'Boston' 
		);
	} else if ($pickupcountry == 'Huston US Warehouse') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'TX', 'theme' ),
				"value" => 'TX' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( '77010', 'theme' ),
				"value" => '77010' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Huston', 'theme' ),
				"value" => 'Huston' 
		);
	} else if ($pickupcountry == 'Atlanta US Warehouse') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'GA', 'theme' ),
				"value" => 'GA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( '30301', 'theme' ),
				"value" => '30301' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Atlanta', 'theme' ),
				"value" => 'Atlanta' 
		);
	} else if ($pickupcountry == 'Pooleville MD USA') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'MD', 'theme' ),
				"value" => 'MD' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( '20837', 'theme' ),
				"value" => '20837' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Pooleville', 'theme' ),
				"value" => 'Pooleville' 
		);
	} else if ($pickupcountry == 'Philadelphia US Warehouse') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'PA', 'theme' ),
				"value" => 'PA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( '19140', 'theme' ),
				"value" => '19140' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Philadelphia', 'theme' ),
				"value" => 'Philadelphia' 
		);
	} else if ($pickupcountry == 'Kearny NJ USA') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NJ', 'theme' ),
				"value" => 'NJ' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( '7032', 'theme' ),
				"value" => '7032' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Kearny', 'theme' ),
				"value" => 'Kearny' 
		);
	} else if ($pickupcountry == 'Anguilla') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'The Valley', 'theme' ),
				"value" => 'The Valley' 
		);
	} else if ($pickupcountry === 'Antigua and Barbuda') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'St. Johns', 'theme' ),
				"value" => 'St. Johns' 
		);
	} else if ($pickupcountry == 'Aruba') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Oranjestad', 'theme' ),
				"value" => 'Oranjestad' 
		);
	} else if ($pickupcountry == 'Bahamas') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Nassau', 'theme' ),
				"value" => 'Nassau' 
		);
	} else if ($pickupcountry == 'Barbados') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Bridgetown', 'theme' ),
				"value" => 'Bridgetown' 
		);
	} else if ($pickupcountry == 'Belize') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Belmopan', 'theme' ),
				"value" => 'Belmopan' 
		);
	} else if ($pickupcountry == 'Bermuda') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Hamilton', 'theme' ),
				"value" => 'Hamilton' 
		);
	} else if ($pickupcountry == 'Cayman Island') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'George Town', 'theme' ),
				"value" => 'George Town' 
		);
	} else if ($pickupcountry == 'Colombia') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Bogota', 'theme' ),
				"value" => 'Bogota' 
		);
	} else if ($pickupcountry == 'Costa Rica') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'San Jose', 'theme' ),
				"value" => 'San Jose' 
		);
	} else if ($pickupcountry == 'Dominica') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Roseau', 'theme' ),
				"value" => 'Roseau' 
		);
	} else if ($pickupcountry == 'Dominican Republic') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Santo Domingo', 'theme' ),
				"value" => 'Santo Domingo' 
		);
	} else if ($pickupcountry == 'Grenada') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'St. Georges', 'theme' ),
				"value" => 'St Georges' 
		);
	} else if ($pickupcountry == 'Guadeloupe') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Basse-Terre', 'theme' ),
				"value" => 'Basse-Terre' 
		);
	} else if ($pickupcountry == 'Guyana') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Georgetown', 'theme' ),
				"value" => 'Georgetown' 
		);
	} else if ($pickupcountry == 'Haiti') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Port-au-Prince', 'theme' ),
				"value" => 'Port-au-Prince' 
		);
	} else if ($pickupcountry == 'Jamaica') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Kingston', 'theme' ),
				"value" => 'Kingston' 
		);
	} else if ($pickupcountry == 'Martinique') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Fort-de-France', 'theme' ),
				"value" => 'Fort-de-France' 
		);
	} else if ($pickupcountry == 'Montserrat') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Plymouth', 'theme' ),
				"value" => 'Plymouth' 
		);
	} else if ($pickupcountry == 'Saint Kitts and Nevis') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Basseterre', 'theme' ),
				"value" => 'Basseterre' 
		);
	} else if ($pickupcountry == 'Saint Lucia') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Castries', 'theme' ),
				"value" => 'Castries' 
		);
	} else if ($pickupcountry == 'Saint Maarten') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Philipsburg', 'theme' ),
				"value" => 'Philipsburg' 
		);
	} else if ($pickupcountry == 'Saint Vincent and the Grenadines') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Kingstown', 'theme' ),
				"value" => 'Kingstown' 
		);
	} else if ($pickupcountry == 'Tortola') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Road Town', 'theme' ),
				"value" => 'Road Town' 
		);
	} else if ($pickupcountry == 'Trinidad and Tobago') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Port of Spain', 'theme' ),
				"value" => 'Port of Spain' 
		);
	} else if ($pickupcountry == 'Turks and Caicos') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Cockburn Town', 'theme' ),
				"value" => 'Cockburn Town' 
		);
	} else if ($pickupcountry == 'US Virgin Island') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Charlotte Amalie', 'theme' ),
				"value" => 'Charlotte Amalie' 
		);
	} else if ($pickupcountry == 'Venezuela') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Caracas', 'theme' ),
				"value" => 'Caracas' 
		);
	} else if ($pickupcountry == 'United States') {
		global $wpdb; // Accessing WP Database (non-WP Table) use code below.
		$stateterms = $wpdb->get_results ( "SELECT distinct state from wp_sp_zipcodes where state in ('FL', 'NJ', 'NY', 'GA', 'TX', 'MA', 'MD', 'PA') order by state asc" );
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
		foreach ( $stateterms as $stateterm )
			$stateitems [] = array (
					"text" => $stateterm->state,
					"value" => $stateterm->state 
			);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
	} else {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'Select state', 'theme' ),
				"value" => '' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
	}
	
	$array = array (
			$stateitems,
			$zipitems,
			$cityitems 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_get_origin_pickup_warehouse_location', 'get_origin_pickup_warehouse_location_fn' );
add_action ( 'wp_ajax_nopriv_get_origin_pickup_warehouse_location', 'get_origin_pickup_warehouse_location_fn' );
function get_origin_pickup_zip_fn() {
	$pickupstate = $_POST ['originpickupstate'];
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$zipterms = $wpdb->get_results ( "SELECT distinct zip from wp_sp_zipcodes where state = '" . $pickupstate . "'" );
	$zipitems = array ();
	$zipitems [] = array (
			"text" => __ ( 'Select', 'theme' ),
			"value" => '' 
	);
	foreach ( $zipterms as $zipterm ) {
		$zipitems [] = array (
				"text" => $zipterm->zip,
				"value" => $zipterm->zip 
		);
	}
	
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select', 'theme' ),
			"value" => '' 
	);
	foreach ( $cityterms as $cityterm ) {
		$cityitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
	}
	
	$array = array (
			$zipitems,
			$cityitems 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_get_origin_pickup_zip', 'get_origin_pickup_zip_fn' );
add_action ( 'wp_ajax_nopriv_get_origin_pickup_zip', 'get_origin_pickup_zip_fn' );
function get_origin_pickup_city_fn() {
	$pickupzip = $_POST ['originpickupzip'];
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$cityterms = $wpdb->get_results ( "SELECT merge_cities from wp_sp_zipcodes where zip = $pickupzip" );
	$result = "";
	foreach ( $cityterms as $cityterm ) {
		$result = $cityterm->merge_cities;
	}
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select', 'theme' ),
			"value" => '' 
	);
	$myArray = explode ( ', ', $result );
	foreach ( $myArray as &$value ) {
		$cityitems [] = array (
				"text" => $value,
				"value" => $value 
		);
		;
	}
	
	echo json_encode ( $cityitems );
	die ();
}
add_action ( 'wp_ajax_get_origin_pickup_city', 'get_origin_pickup_city_fn' );
add_action ( 'wp_ajax_nopriv_get_origin_pickup_city', 'get_origin_pickup_city_fn' );

// ====================================================

// Get destination delivery country/warehouse
add_filter ( "gform_pre_render", "destination_populate_dropdown_delivery_country" );
add_filter ( "gform_admin_pre_render", "destination_populate_dropdown_delivery_country" );
function destination_populate_dropdown_delivery_country($form) {
	if ($form ["id"] != 52)
		return $form;
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 29) {
			$field ["cssClass"] = 'destination_delivery_country';
		}
	}
	return $form;
}

// Get destination delivery states
add_filter ( "gform_pre_render", "destination_populate_dropdown_delivery_state" );
add_filter ( "gform_admin_pre_render", "destination_populate_dropdown_delivery_state" );
function destination_populate_dropdown_delivery_state($form) {
	if ($form ["id"] != 52)
		return $form;
		// global $wpdb; //Accessing WP Database (non-WP Table) use code below.
		// $stateterms = $wpdb->get_results("SELECT distinct state from wp_sp_zipcodes where state in ('FL', 'NJ', 'NY', 'GA', 'TX', 'MA', 'MD', 'PA') order by state asc");
	$stateitems = array ();
	$stateitems [] = array (
			"text" => __ ( 'Select', 'theme' ),
			"value" => '' 
	);
	// foreach($stateterms as $stateterm)
	// $stateitems[] = array( "text" => $stateterm->state, "value" => $stateterm->state);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 32) {
			$field ["cssClass"] = 'destination_delivery_state';
			$field ["choices"] = $stateitems;
		}
	}
	return $form;
}

// Get destination delivery zips
add_filter ( "gform_pre_render", "destination_populate_dropdown_delivery_zip" );
add_filter ( "gform_admin_pre_render", "destination_populate_dropdown_delivery_zip" );
function destination_populate_dropdown_delivery_zip($form) {
	if ($form ["id"] != 52)
		return $form;
	$zipitems = array ();
	$zipitems [] = array (
			"text" => __ ( 'Select', 'theme' ),
			"value" => '' 
	);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 37) {
			$field ["cssClass"] = 'destination_delivery_zip';
			$field ["choices"] = $zipitems;
		}
	}
	return $form;
}

// Get destination delivery cities
add_filter ( "gform_pre_render", "destination_populate_dropdown_delivery_city" );
add_filter ( "gform_admin_pre_render", "destination_populate_dropdown_delivery_city" );
function destination_populate_dropdown_delivery_city($form) {
	if ($form ["id"] != 52)
		return $form;
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select', 'theme' ),
			"value" => '' 
	);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 40) {
			$field ["cssClass"] = 'destination_delivery_city';
			$field ["choices"] = $cityitems;
		}
	}
	return $form;
}

// Function called only when warehouse locations selected in order to populate the state zip and city with default info
function get_destination_delivery_warehouse_location_fn() {
	$deliverycountry = $_POST ['destinationdeliverycountry'];
	
	if ($deliverycountry == 'Anguilla') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'The Valley', 'theme' ),
				"value" => 'The Valley' 
		);
	} else if ($deliverycountry === 'Antigua and Barbuda') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'St. Johns', 'theme' ),
				"value" => 'St. Johns' 
		);
	} else if ($deliverycountry == 'Aruba') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Oranjestad', 'theme' ),
				"value" => 'Oranjestad' 
		);
	} else if ($deliverycountry == 'Bahamas') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Nassau', 'theme' ),
				"value" => 'Nassau' 
		);
	} else if ($deliverycountry == 'Barbados') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Bridgetown', 'theme' ),
				"value" => 'Bridgetown' 
		);
	} else if ($deliverycountry == 'Belize') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Belmopan', 'theme' ),
				"value" => 'Belmopan' 
		);
	} else if ($deliverycountry == 'Bermuda') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Hamilton', 'theme' ),
				"value" => 'Hamilton' 
		);
	} else if ($deliverycountry == 'Cayman Island') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'George Town', 'theme' ),
				"value" => 'George Town' 
		);
	} else if ($deliverycountry == 'Colombia') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Bogota', 'theme' ),
				"value" => 'Bogota' 
		);
	} else if ($deliverycountry == 'Costa Rica') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'San Jose', 'theme' ),
				"value" => 'San Jose' 
		);
	} else if ($deliverycountry == 'Dominica') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Roseau', 'theme' ),
				"value" => 'Roseau' 
		);
	} else if ($deliverycountry == 'Dominican Republic') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Santo Domingo', 'theme' ),
				"value" => 'Santo Domingo' 
		);
	} else if ($deliverycountry == 'Grenada') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'St. Georges', 'theme' ),
				"value" => 'St Georges' 
		);
	} else if ($deliverycountry == 'Guadeloupe') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Basse-Terre', 'theme' ),
				"value" => 'Basse-Terre' 
		);
	} else if ($deliverycountry == 'Guyana') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Georgetown', 'theme' ),
				"value" => 'Georgetown' 
		);
	} else if ($deliverycountry == 'Haiti') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Port-au-Prince', 'theme' ),
				"value" => 'Port-au-Prince' 
		);
	} else if ($deliverycountry == 'Jamaica') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Kingston', 'theme' ),
				"value" => 'Kingston' 
		);
	} else if ($deliverycountry == 'Martinique') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Fort-de-France', 'theme' ),
				"value" => 'Fort-de-France' 
		);
	} else if ($deliverycountry == 'Montserrat') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Plymouth', 'theme' ),
				"value" => 'Plymouth' 
		);
	} else if ($deliverycountry == 'Saint Kitts and Nevis') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Basseterre', 'theme' ),
				"value" => 'Basseterre' 
		);
	} else if ($deliverycountry == 'Saint Lucia') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Castries', 'theme' ),
				"value" => 'Castries' 
		);
	} else if ($deliverycountry == 'Saint Maarten') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Philipsburg', 'theme' ),
				"value" => 'Philipsburg' 
		);
	} else if ($deliverycountry == 'Saint Vincent and the Grenadines') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Kingstown', 'theme' ),
				"value" => 'Kingstown' 
		);
	} else if ($deliverycountry == 'Tortola') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Road Town', 'theme' ),
				"value" => 'Road Town' 
		);
	} else if ($deliverycountry == 'Trinidad and Tobago') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Port of Spain', 'theme' ),
				"value" => 'Port of Spain' 
		);
	} else if ($deliverycountry == 'Turks and Caicos') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Cockburn Town', 'theme' ),
				"value" => 'Cockburn Town' 
		);
	} else if ($deliverycountry == 'US Virgin Island') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Charlotte Amalie', 'theme' ),
				"value" => 'Charlotte Amalie' 
		);
	} else if ($deliverycountry == 'Venezuela') {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'NA', 'theme' ),
				"value" => 'NA' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Caracas', 'theme' ),
				"value" => 'Caracas' 
		);
	} else if ($deliverycountry == 'United States') {
		global $wpdb; // Accessing WP Database (non-WP Table) use code below.
		$stateterms = $wpdb->get_results ( "SELECT distinct state from wp_sp_zipcodes where state in ('FL', 'NJ', 'NY', 'GA', 'TX', 'MA', 'MD', 'PA') order by state asc" );
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
		foreach ( $stateterms as $stateterm )
			$stateitems [] = array (
					"text" => $stateterm->state,
					"value" => $stateterm->state 
			);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
	} else {
		$stateitems = array ();
		$stateitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
		
		$zipitems = array ();
		$zipitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
		
		$cityitems = array ();
		$cityitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
	}
	
	$array = array (
			$stateitems,
			$zipitems,
			$cityitems 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_get_destination_delivery_warehouse_location', 'get_destination_delivery_warehouse_location_fn' );
add_action ( 'wp_ajax_nopriv_get_destination_delivery_warehouse_location', 'get_destination_delivery_warehouse_location_fn' );
function get_destination_delivery_zip_fn() {
	$deliverystate = $_POST ['destinationdeliverystate'];
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$zipterms = $wpdb->get_results ( "SELECT distinct zip from wp_sp_zipcodes where state = '" . $deliverystate . "'" );
	$zipitems = array ();
	$zipitems [] = array (
			"text" => __ ( 'Select', 'theme' ),
			"value" => '' 
	);
	foreach ( $zipterms as $zipterm ) {
		$zipitems [] = array (
				"text" => $zipterm->zip,
				"value" => $zipterm->zip 
		);
	}
	
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select', 'theme' ),
			"value" => '' 
	);
	foreach ( $cityterms as $cityterm ) {
		$cityitems [] = array (
				"text" => __ ( 'Select', 'theme' ),
				"value" => '' 
		);
	}
	
	$array = array (
			$zipitems,
			$cityitems 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_get_destination_delivery_zip', 'get_destination_delivery_zip_fn' );
add_action ( 'wp_ajax_nopriv_get_destination_delivery_zip', 'get_destination_delivery_zip_fn' );
function get_destination_delivery_city_fn() {
	$deliveryzip = $_POST ['destinationdeliveryzip'];
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$cityterms = $wpdb->get_results ( "SELECT merge_cities from wp_sp_zipcodes where zip = $deliveryzip" );
	$result = "";
	foreach ( $cityterms as $cityterm ) {
		$result = $cityterm->merge_cities;
	}
	$cityitems = array ();
	$cityitems [] = array (
			"text" => __ ( 'Select', 'theme' ),
			"value" => '' 
	);
	$myArray = explode ( ', ', $result );
	foreach ( $myArray as &$value ) {
		$cityitems [] = array (
				"text" => $value,
				"value" => $value 
		);
		;
	}
	
	echo json_encode ( $cityitems );
	die ();
}
add_action ( 'wp_ajax_get_destination_delivery_city', 'get_destination_delivery_city_fn' );
add_action ( 'wp_ajax_nopriv_get_destination_delivery_city', 'get_destination_delivery_city_fn' );

// =============================================================

// Assign a class to the Package Description fields
add_filter ( "gform_pre_render", "populate_dropdown_package_description" );
add_filter ( "gform_admin_pre_render", "populate_dropdown_package_description" );
function populate_dropdown_package_description($form) {
	if ($form ["id"] != 52)
		return $form;
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$pkgdescriptionterms = $wpdb->get_results ( "SELECT distinct item_description from wp_sp_freight_class" );
	$pkgdescriptionitems = array ();
	$pkgdescriptionitems [] = array (
			"text" => __ ( 'Select ...', 'theme' ),
			"value" => '' 
	);
	foreach ( $pkgdescriptionterms as $pkgdescriptionterm )
		$pkgdescriptionitems [] = array (
				"text" => $pkgdescriptionterm->item_description,
				"value" => $pkgdescriptionterm->item_description 
		);
	
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 54) {
			$field ["cssClass"] = 'package_description gf_inline';
			$field ["choices"] = $pkgdescriptionitems;
		}
		if ($field ["id"] == 66) {
			$field ["cssClass"] = 'package_description gf_inline';
			$field ["choices"] = $pkgdescriptionitems;
		}
		if ($field ["id"] == 78) {
			$field ["cssClass"] = 'package_description gf_inline';
			$field ["choices"] = $pkgdescriptionitems;
		}
		if ($field ["id"] == 90) {
			$field ["cssClass"] = 'package_description gf_inline';
			$field ["choices"] = $pkgdescriptionitems;
		}
		if ($field ["id"] == 102) {
			$field ["cssClass"] = 'package_description gf_inline';
			$field ["choices"] = $pkgdescriptionitems;
		}
	}
	return $form;
}

// Get freight class
add_filter ( "gform_pre_render", "populate_dropdown_freight_class" );
add_filter ( "gform_admin_pre_render", "populate_dropdown_freight_class" );
function populate_dropdown_freight_class($form) {
	if ($form ["id"] != 52)
		return $form;
	$classitems = array ();
	$classitems [] = array (
			"text" => __ ( '...', 'theme' ),
			"value" => '' 
	);
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 55) {
			$field ["cssClass"] = 'freight_class gf_inline';
			$field ["choices"] = $classitems;
		}
		if ($field ["id"] == 67) {
			$field ["cssClass"] = 'freight_class gf_inline';
			$field ["choices"] = $classitems;
		}
		if ($field ["id"] == 79) {
			$field ["cssClass"] = 'freight_class gf_inline';
			$field ["choices"] = $classitems;
		}
		if ($field ["id"] == 91) {
			$field ["cssClass"] = 'freight_class gf_inline';
			$field ["choices"] = $classitems;
		}
		if ($field ["id"] == 103) {
			$field ["cssClass"] = 'freight_class gf_inline';
			$field ["choices"] = $classitems;
		}
	}
	return $form;
}
function get_freight_class_fn() {
	$commodity = $_POST ['packagedescription'];
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	$classterms = $wpdb->get_results ( "SELECT distinct class from wp_sp_freight_class where item_description = '" . $commodity . "'" );
	$classitems = array ();
	foreach ( $classterms as $classterm ) {
		$classitems [] = array (
				"text" => $classterm->class,
				"value" => $classterm->class 
		);
	}
	
	$array = array (
			$classitems 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_get_freight_class', 'get_freight_class_fn' );
add_action ( 'wp_ajax_nopriv_get_freight_class', 'get_freight_class_fn' );

// Get Package Type Class
add_filter ( "gform_pre_render", "populate_dropdown_package_type" );
add_filter ( "gform_admin_pre_render", "populate_dropdown_package_type" );
function populate_dropdown_package_type($form) {
	if ($form ["id"] != 52)
		return $form;
	
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 53) {
			$field ["cssClass"] = 'pkg_type gf_inline';
		}
		if ($field ["id"] == 65) {
			$field ["cssClass"] = 'pkg_type gf_inline';
		}
		if ($field ["id"] == 77) {
			$field ["cssClass"] = 'pkg_type gf_inline';
		}
		if ($field ["id"] == 89) {
			$field ["cssClass"] = 'pkg_type gf_inline';
		}
		if ($field ["id"] == 101) {
			$field ["cssClass"] = 'pkg_type gf_inline';
		}
	}
	return $form;
}

// Get Quantity Field Class
add_filter ( "gform_pre_render", "populate_quantity_class" );
add_filter ( "gform_admin_pre_render", "populate_quantity_class" );
function populate_quantity_class($form) {
	if ($form ["id"] != 52)
		return $form;
	
	foreach ( $form ["fields"] as &$field ) {
		if ($field ["id"] == 56) {
			$field ["cssClass"] = 'pkg_qty gf_inline';
		}
		if ($field ["id"] == 68) {
			$field ["cssClass"] = 'pkg_qty gf_inline';
		}
		if ($field ["id"] == 80) {
			$field ["cssClass"] = 'pkg_qty gf_inline';
		}
		if ($field ["id"] == 92) {
			$field ["cssClass"] = 'pkg_qty gf_inline';
		}
		if ($field ["id"] == 104) {
			$field ["cssClass"] = 'pkg_qty gf_inline';
		}
	}
	return $form;
}
function QuickQuoteDims_fn() {
	$pkgtype = $_POST ['fieldvalue'];
	
	if ($pkgtype == 'Barrel') {
		$length = 24;
		$width = 24;
		$height = 43;
		$weight = 300;
	}
	
	if ($pkgtype == 'EH Container') {
		$length = 36;
		$width = 22;
		$height = 22;
		$weight = 0;
	}
	
	if ($pkgtype == 'E Container') {
		$length = 42;
		$width = 29;
		$height = 26;
		$weight = 0;
	}
	
	$array = array (
			$length,
			$width,
			$height,
			$weight 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_QuickQuoteDims', 'QuickQuoteDims_fn' );
add_action ( 'wp_ajax_nopriv_QuickQuoteDims', 'QuickQuoteDims_fn' );

// QUICK QUOTE CALCULATION
function get_quick_quote_fn() {
	$fieldvalues = $_POST ['fieldvalues'];
	$inputvals = explode ( ", ", $fieldvalues );
	
	$allpkgType = $inputvals [0];
	$allpkgDescription = $inputvals [1];
	$allpkgClass = $inputvals [2];
	$allpkgQty = $inputvals [3];
	$allpkgValue = $inputvals [4];
	$allpkgWeight = $inputvals [5];
	$allpkgLength = $inputvals [6];
	$allpkgWidth = $inputvals [7];
	$allpkgHeight = $inputvals [8];
	$allhazardous = $inputvals [9];
	$pickupCountry = $inputvals [10];
	$pickupState = $inputvals [11];
	$pickupZip = $inputvals [12];
	$deliveryCountry = $inputvals [13];
	$deliveryState = $inputvals [14];
	$deliveryZip = $inputvals [15];
	$allpickupservices = $inputvals [16];
	$alldeliveryservices = $inputvals [17];
	$numberofpieces = $inputvals [18];
	$residentialPickup = $inputvals [19];
	$residentialDelivery = $inputvals [20];
	
	$pickupservices = explode ( "|", $allpickupservices );
	$deliveryservices = explode ( "|", $alldeliveryservices );
	
	$pkgTypes = explode ( "|", $allpkgType );
	$pkgDescriptions = explode ( "|", $allpkgDescription );
	$pkgClasses = explode ( "|", $allpkgClass );
	$pkgQties = explode ( "|", $allpkgQty );
	$pkgValues = explode ( "|", $allpkgValue );
	$pkgWeights = explode ( "|", $allpkgWeight );
	$pkgLengths = explode ( "|", $allpkgLength );
	$pkgWidths = explode ( "|", $allpkgWidth );
	$pkgHeights = explode ( "|", $allpkgHeight );
	$hazardous = explode ( "|", $allhazardous );
	
	$get_unique_pkgTypes = array_unique ( $pkgTypes, SORT_STRING );
	$unique_pkgTypes = rtrim ( implode ( ", ", $get_unique_pkgTypes ), ", " );
	
	$pkgType1 = $pkgTypes [0];
	$pkgDescription1 = $pkgDescriptions [0];
	$pkgClass1 = $pkgClasses [0];
	$pkgQty1 = $pkgQties [0];
	if ($pkgQty1 == '') {
		$pkgQty1 = 0;
	} else {
		$pkgQty1 = $pkgQty1;
	}
	$pkgValue1 = $pkgValues [0];
	$pkgValue1 = preg_replace ( '/[\$,]/', '', $pkgValue1 );
	if ($pkgValue1 == '') {
		$pkgValue1 = 0;
	} else {
		$pkgValue1 = $pkgValue1;
	}
	$pkgWeight1 = $pkgWeights [0];
	if ($pkgWeight1 == '') {
		$pkgWeight1 = 0;
	} else {
		$pkgWeight1 = $pkgWeight1;
	}
	$pkgLength1 = $pkgLengths [0];
	if ($pkgLength1 == '') {
		$pkgLength1 = 0;
	} else {
		$pkgLength1 = $pkgLength1;
	}
	$pkgWidth1 = $pkgWidths [0];
	if ($pkgWidth1 == '') {
		$pkgWidth1 = 0;
	} else {
		$pkgWidth1 = $pkgWidth1;
	}
	$pkgHeight1 = $pkgHeights [0];
	if ($pkgHeight1 == '') {
		$pkgHeight1 = 0;
	} else {
		$pkgHeight1 = $pkgHeight1;
	}
	$hazardous1 = $hazardous [0];
	$express_volWeight1 = number_format ( ((($pkgLength1 * $pkgWidth1 * $pkgHeight1) / 133) * $pkgQty1), 2, '.', '' );
	$volWeight1 = number_format ( ((($pkgLength1 * $pkgWidth1 * $pkgHeight1) / 166) * $pkgQty1), 2, '.', '' );
	$cubicft1 = number_format ( ((($pkgLength1 * $pkgWidth1 * $pkgHeight1) / 1728) * $pkgQty1), 2, '.', '' );
	
	$pkgType2 = $pkgTypes [1];
	$pkgDescription2 = $pkgDescriptions [1];
	$pkgClass2 = $pkgClasses [1];
	$pkgQty2 = $pkgQties [1];
	if ($pkgQty2 == '') {
		$pkgQty2 = 0;
	} else {
		$pkgQty2 = $pkgQty2;
	}
	$pkgValue2 = $pkgValues [1];
	$pkgValue2 = preg_replace ( '/[\$,]/', '', $pkgValue2 );
	if ($pkgValue2 == '') {
		$pkgValue2 = 0;
	} else {
		$pkgValue2 = $pkgValue2;
	}
	$pkgWeight2 = $pkgWeights [1];
	if ($pkgWeight2 == '') {
		$pkgWeight2 = 0;
	} else {
		$pkgWeight2 = $pkgWeight2;
	}
	$pkgLength2 = $pkgLengths [1];
	if ($pkgLength2 == '') {
		$pkgLength2 = 0;
	} else {
		$pkgLength2 = $pkgLength2;
	}
	$pkgWidth2 = $pkgWidths [1];
	if ($pkgWidth2 == '') {
		$pkgWidth2 = 0;
	} else {
		$pkgWidth2 = $pkgWidth2;
	}
	$pkgHeight2 = $pkgHeights [1];
	if ($pkgHeight2 == '') {
		$pkgHeight2 = 0;
	} else {
		$pkgHeight2 = $pkgHeight2;
	}
	$hazardous2 = $hazardous [1];
	$express_volWeight2 = number_format ( ((($pkgLength2 * $pkgWidth2 * $pkgHeight2) / 133) * $pkgQty2), 2, '.', '' );
	$volWeight2 = number_format ( ((($pkgLength2 * $pkgWidth2 * $pkgHeight2) / 166) * $pkgQty2), 2, '.', '' );
	$cubicft2 = number_format ( ((($pkgLength2 * $pkgWidth2 * $pkgHeight2) / 1728) * $pkgQty2), 2, '.', '' );
	
	$pkgType3 = $pkgTypes [2];
	$pkgDescription3 = $pkgDescriptions [2];
	$pkgClass3 = $pkgClasses [2];
	$pkgQty3 = $pkgQties [2];
	if ($pkgQty3 == '') {
		$pkgQty3 = 0;
	} else {
		$pkgQty3 = $pkgQty3;
	}
	$pkgValue3 = $pkgValues [2];
	$pkgValue3 = preg_replace ( '/[\$,]/', '', $pkgValue3 );
	if ($pkgValue3 == '') {
		$pkgValue3 = 0;
	} else {
		$pkgValue3 = $pkgValue3;
	}
	$pkgWeight3 = $pkgWeights [2];
	if ($pkgWeight3 == '') {
		$pkgWeight3 = 0;
	} else {
		$pkgWeight3 = $pkgWeight3;
	}
	$pkgLength3 = $pkgLengths [2];
	if ($pkgLength3 == '') {
		$pkgLength3 = 0;
	} else {
		$pkgLength3 = $pkgLength3;
	}
	$pkgWidth3 = $pkgWidths [2];
	if ($pkgWidth3 == '') {
		$pkgWidth3 = 0;
	} else {
		$pkgWidth3 = $pkgWidth3;
	}
	$pkgHeight3 = $pkgHeights [2];
	if ($pkgHeight3 == '') {
		$pkgHeight3 = 0;
	} else {
		$pkgHeight3 = $pkgHeight3;
	}
	$hazardous3 = $hazardous [2];
	$express_volWeight3 = number_format ( ((($pkgLength3 * $pkgWidth3 * $pkgHeight3) / 133) * $pkgQty3), 2, '.', '' );
	$volWeight3 = number_format ( ((($pkgLength3 * $pkgWidth3 * $pkgHeight3) / 166) * $pkgQty3), 2, '.', '' );
	$cubicft3 = number_format ( ((($pkgLength3 * $pkgWidth3 * $pkgHeight3) / 1728) * $pkgQty3), 2, '.', '' );
	
	$pkgType4 = $pkgTypes [3];
	$pkgDescription4 = $pkgDescriptions [3];
	$pkgClass4 = $pkgClasses [3];
	$pkgQty4 = $pkgQties [3];
	if ($pkgQty4 == '') {
		$pkgQty4 = 0;
	} else {
		$pkgQty4 = $pkgQty4;
	}
	$pkgValue4 = $pkgValues [3];
	$pkgValue4 = preg_replace ( '/[\$,]/', '', $pkgValue4 );
	if ($pkgValue4 == '') {
		$pkgValue4 = 0;
	} else {
		$pkgValue4 = $pkgValue4;
	}
	$pkgWeight4 = $pkgWeights [3];
	if ($pkgWeight4 == '') {
		$pkgWeight4 = 0;
	} else {
		$pkgWeight4 = $pkgWeight4;
	}
	$pkgLength4 = $pkgLengths [3];
	if ($pkgLength4 == '') {
		$pkgLength4 = 0;
	} else {
		$pkgLength4 = $pkgLength4;
	}
	$pkgWidth4 = $pkgWidths [3];
	if ($pkgWidth4 == '') {
		$pkgWidth4 = 0;
	} else {
		$pkgWidth4 = $pkgWidth4;
	}
	$pkgHeight4 = $pkgHeights [3];
	if ($pkgHeight4 == '') {
		$pkgHeight4 = 0;
	} else {
		$pkgHeight4 = $pkgHeight4;
	}
	$hazardous4 = $hazardous [3];
	$express_volWeight4 = number_format ( ((($pkgLength4 * $pkgWidth4 * $pkgHeight4) / 133) * $pkgQty4), 2, '.', '' );
	$volWeight4 = number_format ( ((($pkgLength4 * $pkgWidth4 * $pkgHeight4) / 166) * $pkgQty4), 2, '.', '' );
	$cubicft4 = number_format ( ((($pkgLength4 * $pkgWidth4 * $pkgHeight4) / 1728) * $pkgQty4), 2, '.', '' );
	
	$pkgType5 = $pkgTypes [4];
	$pkgDescription5 = $pkgDescriptions [4];
	$pkgClass5 = $pkgClasses [4];
	$pkgQty5 = $pkgQties [4];
	if ($pkgQty5 == '') {
		$pkgQty5 = 0;
	} else {
		$pkgQty5 = $pkgQty5;
	}
	$pkgValue5 = $pkgValues [4];
	$pkgValue5 = preg_replace ( '/[\$,]/', '', $pkgValue5 );
	if ($pkgValue5 == '') {
		$pkgValue5 = 0;
	} else {
		$pkgValue5 = $pkgValue5;
	}
	$pkgWeight5 = $pkgWeights [4];
	if ($pkgWeight5 == '') {
		$pkgWeight5 = 0;
	} else {
		$pkgWeight5 = $pkgWeight5;
	}
	$pkgLength5 = $pkgLengths [4];
	if ($pkgLength5 == '') {
		$pkgLength5 = 0;
	} else {
		$pkgLength5 = $pkgLength5;
	}
	$pkgWidth5 = $pkgWidths [4];
	if ($pkgWidth5 == '') {
		$pkgWidth5 = 0;
	} else {
		$pkgWidth5 = $pkgWidth5;
	}
	$pkgHeight5 = $pkgHeights [4];
	if ($pkgHeight5 == '') {
		$pkgHeight5 = 0;
	} else {
		$pkgHeight5 = $pkgHeight5;
	}
	$hazardous5 = $hazardous [4];
	$express_volWeight5 = number_format ( ((($pkgLength5 * $pkgWidth5 * $pkgHeight5) / 133) * $pkgQty5), 2, '.', '' );
	$volWeight5 = number_format ( ((($pkgLength5 * $pkgWidth5 * $pkgHeight5) / 166) * $pkgQty5), 2, '.', '' );
	$cubicft5 = number_format ( ((($pkgLength5 * $pkgWidth5 * $pkgHeight5) / 1728) * $pkgQty5), 2, '.', '' );
	
	$actual_pkgWeight1 = ceil ( $pkgWeight1 );
	$actual_pkgWeight2 = ceil ( $pkgWeight2 );
	$actual_pkgWeight3 = ceil ( $pkgWeight3 );
	$actual_pkgWeight4 = ceil ( $pkgWeight4 );
	$actual_pkgWeight5 = ceil ( $pkgWeight5 );
	
	$sum_pkgQty = ($pkgQty1 + $pkgQty2 + $pkgQty3 + $pkgQty4 + $pkgQty5);
	
	$sum_pkgValue = number_format ( ($pkgValue1 + $pkgValue2 + $pkgValue3 + $pkgValue4 + $pkgValue5), 2, '.', '' );
	if ($sum_pkgValue <= 100) {
		$insurance = number_format ( (1.25), 2, '.', '' );
		$caribbean_insurance = number_format ( (1.25), 2, '.', '' );
	} else {
		$insurance = number_format ( ((1.25 * $sum_pkgValue) / 100), 2, '.', '' );
		$caribbean_insurance = number_format ( ((1.25 * $sum_pkgValue) / 100), 2, '.', '' );
	}
	
	if ($residentialPickup == 'true') {
		$residentialPickup_cost = '5.00';
		$caribbean_residentialPickup_cost = '5.00';
	} else {
		$residentialPickup_cost = '0.00';
		$caribbean_residentialPickup_cost = '0.00';
	}
	
	if ($residentialDelivery == 'true') {
		$residentialDelivery_cost = '5.00';
		$caribbean_residentialDelivery_cost = '5.00';
	} else {
		$residentialDelivery_cost = '0.00';
		$caribbean_residentialDelivery_cost = '0.00';
	}
	
	$sum_pkgWeight = number_format ( ($pkgWeight1 + $pkgWeight2 + $pkgWeight3 + $pkgWeight4 + $pkgWeight5), 2, '.', '' );
	$sum_volWeight = number_format ( ($volWeight1 + $volWeight2 + $volWeight3 + $volWeight4 + $volWeight5), 2, '.', '' );
	$sum_express_volWeight = number_format ( ($express_volWeight1 + $express_volWeight2 + $express_volWeight3 + $express_volWeight4 + $express_volWeight5), 2, '.', '' );
	// $sum_cubicft = number_format(($cubicft1 + $cubicft2 + $cubicft3 + $cubicft4 + $cubicft5),2,'.','');
	
	$var_prefix1 = "pkgType";
	$var_prefix2 = "cubicft";
	$var_prefix3 = "hazardous";
	$var_prefix4 = "pkgQty";
	$var_prefix5 = "pkgWeight";
	$var_prefix6 = "volWeight";
	$var_prefix7 = "express_volWeight";
	
	$sum_cubicft = 0;
	$sum_cubicft_barrel = 0;
	$sum_cubicft_econtainer = 0;
	$sum_cubicft_ehcontainer = 0;
	$barrelItems = 0;
	$letterItems = 0;
	$documentItems = 0;
	$econtainerItems = 0;
	$ehcontainerItems = 0;
	$nonletterORdocumentItems = 0;
	$nonBarrelOrContainerItems = 0;
	$hazardItems = 0;
	$barrelhazardItems = 0;
	$econtainerhazardItems = 0;
	$ehcontainerhazardItems = 0;
	$nonBarrelOrContainerhazardItems = 0;
	$letterhazardItems = 0;
	$documenthazardItems = 0;
	$nonletterORdocumenthazardItems = 0;
	$barrelQty = 0;
	$letterQty = 0;
	$documentQty = 0;
	$econtainerQty = 0;
	$ehcontainerQty = 0;
	$nonletterORdocumentQty = 0;
	$nonBarrelOrContainerQty = 0;
	$nonBarrelOrContainerWeight = 0;
	$nonBarrelOrContainerVolWeight = 0;
	
	for($i = 1; $i <= 5; $i ++) {
		// Dynamically generate the variable name, i.e. pkgType1, pkgType2,...pkgType5
		$var_name1 = $var_prefix1 . $i;
		$var_name2 = $var_prefix2 . $i;
		$var_name3 = $var_prefix3 . $i;
		$var_name4 = $var_prefix4 . $i;
		$var_name5 = $var_prefix5 . $i;
		$var_name6 = $var_prefix6 . $i;
		$var_name7 = $var_prefix7 . $i;
		
		// Check Package Type and Keep a running count of each type
		if ($$var_name1 == "Barrel") {
			$sum_cubicft_barrel = number_format ( ($$var_name2 + $sum_cubicft_barrel), 2, '.', '' );
			$barrelQty = ($$var_name4 + $barrelQty);
			$barrelItems ++;
			
			if ($$var_name3 == 'true') {
				$barrelhazardItems ++;
			}
		} else if ($$var_name1 == "E Container") {
			$sum_cubicft_econtainer = number_format ( ($$var_name2 + $sum_cubicft_econtainer), 2, '.', '' );
			$econtainerQty = ($$var_name4 + $econtainerQty);
			$econtainerItems ++;
			
			if ($$var_name3 == 'true') {
				$econtainerhazardItems ++;
			}
		} else if ($$var_name1 == "EH Container") {
			$sum_cubicft_ehcontainer = number_format ( ($$var_name2 + $sum_cubicft_ehcontainer), 2, '.', '' );
			$ehcontainerQty = ($$var_name4 + $hecontainerQty);
			$ehcontainerItems ++;
			
			if ($$var_name3 == 'true') {
				$ehcontainerhazardItems ++;
			}
		} else {
			$sum_cubicft = number_format ( ($$var_name2 + $sum_cubicft), 2, '.', '' );
			if ($$var_name1 != "") {
				$nonBarrelOrContainerItems ++;
			}
			
			if ($$var_name3 == 'true') {
				$nonBarrelOrContainerhazardItems ++;
			}
		}
		
		// Check to see if package type is letter or document or other
		if ($$var_name1 == "Letter" || $$var_name1 == "Document") {
			if ($$var_name1 == "Letter") {
				$letterItems ++;
				$letterQty = ($$var_name4 + $letterQty);
				
				if ($$var_name3 == 'true') {
					$letterhazardItems ++;
				}
			}
			
			if ($$var_name1 == "Document") {
				$documentItems ++;
				$documentQty = ($$var_name4 + $documentQty);
				
				if ($$var_name3 == 'true') {
					$documenthazardItems ++;
				}
			}
		} else {
			if ($$var_name1 != "") {
				$nonletterORdocumentItems ++;
			}
			$nonletterORdocumentQty = ($$var_name4 + $nonletterORdocumentQty);
			
			if ($$var_name3 == 'true') {
				$nonletterORdocumenthazardItems ++;
			}
		}
		
		// Check for non Barrel, E/EH Containers
		if (! ($$var_name1 == "E Container" || $$var_name1 == "EH Container" || $$var_name1 == "Barrel")) {
			$nonBarrelOrContainerQty = ($$var_name4 + $nonBarrelOrContainerQty);
			$nonBarrelOrContainerWeight = ceil ( $$var_name5 + $nonBarrelOrContainerWeight );
			$nonBarrelOrContainerVolWeight = ceil ( $$var_name6 + $nonBarrelOrContainerVolWeight );
			
			if ($nonBarrelOrContainerWeight >= $nonBarrelOrContainerVolWeight) {
				$nonbarrelORcontainer_ChargeableWeight = $nonBarrelOrContainerWeight;
			} else {
				$nonbarrelORcontainer_ChargeableWeight = $nonBarrelOrContainerVolWeight;
			}
		}
	}
	
	$cntHazardous = $barrelhazardItems + $econtainerhazardItems + $ehcontainerhazardItems + $nonBarrelOrContainerhazardItems;
	
	$actual_weight = ceil ( $sum_pkgWeight );
	$express_actual_weight = ceil ( $sum_pkgWeight );
	$total_cubicft = $sum_cubicft + $sum_cubicft_barrel + $sum_cubicft_econtainer + $sum_cubicft_ehcontainer;
	
	if ($sum_pkgWeight >= $sum_express_volWeight) {
		$express_chargeable_weight = ceil ( $sum_pkgWeight );
	} else {
		$express_chargeable_weight = ceil ( $sum_express_volWeight );
	}
	
	if ($sum_pkgWeight >= $sum_volWeight) {
		$chargeable_weight = ceil ( $sum_pkgWeight );
	} else {
		$chargeable_weight = ceil ( $sum_volWeight );
	}
	
	$weight_difference = number_format ( ($sum_volWeight - $sum_pkgWeight), 2, '.', '' );
	/*
	 * if($weight_difference > 5){
	 * $mailbox_chargeable_weight = ceil($sum_volWeight);
	 * } else {
	 * if($sum_pkgWeight <= '0.00'){
	 * $mailbox_chargeable_weight = ceil($sum_volWeight);
	 * }else{
	 * $mailbox_chargeable_weight = ceil($sum_pkgWeight);
	 * }
	 * }
	 */
	
	$new_cubic_feet = ceil ( $sum_cubicft );
	$new_cubic_feet_barrel = ceil ( $sum_cubicft_barrel );
	$new_cubic_feet_econtainer = ceil ( $sum_cubicft_econtainer );
	$new_cubic_feet_ehcontainer = ceil ( $sum_cubicft_ehcontainer );
	
	if ($pickupState == 'NA') {
		$origin = $pickupCountry;
	} else {
		$origin = $pickupState;
	}
	
	switch ($origin) {
		case "FL" :
			// $new_origin = 'MIA';
			if ($pickupCountry == 'Miami US Warehouse') {
				$new_origin = 'MIA';
			} else if ($pickupCountry == 'Orlando US Warehouse') {
				$new_origin = 'ORL';
			} else {
				$new_origin = 'MIA';
			}
			break;
		case "MA" :
			$new_origin = 'BOS';
			break;
		case "TX" :
			$new_origin = 'HUS';
			break;
		case "GA" :
			$new_origin = 'ATL';
			break;
		default :
			$new_origin = $origin;
	}
	
	if ($deliveryState == 'NA') {
		$destination = $deliveryCountry;
	} else {
		$destination = $deliveryState;
	}
	
	switch ($destination) {
		case "FL" :
			$new_destination = 'MIA';
			break;
		case "MA" :
			$new_destination = 'BOS';
			break;
		case "TX" :
			$new_destination = 'HUS';
			break;
		case "GA" :
			$new_destination = 'ATL';
			break;
		default :
			$new_destination = $destination;
	}
	
	/* ---Get Delivery Type Rates--- */
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	
	$mailbox_difference = $wpdb->get_results ( "SELECT difference from wp_sp_volumetric_rules where delivery_type = 'US MailBox' and min_measure <= " . $actual_weight . " and max_measure >= " . $actual_weight );
	
	$mailbox_weight_difference = $mailbox_difference [0]->difference;
	$mailbox_weight_difference = number_format ( ($mailbox_weight_difference), 2, '.', '' );
	;
	if ($weight_difference > $mailbox_weight_difference) {
		$mailbox_chargeable_weight = ceil ( $sum_volWeight );
	} else {
		if ($sum_pkgWeight <= '0.00') {
			$mailbox_chargeable_weight = ceil ( $sum_volWeight );
		} else {
			$mailbox_chargeable_weight = ceil ( $sum_pkgWeight );
		}
	}
	
	$mailbox_rates = $wpdb->get_results ( "SELECT base_rate, additional_rate, markup from wp_sp_rates where delivery_type = 'US MailBox' and origin = '" . $new_origin . "' and destination = '" . $new_destination . "' and min_measure <= " . $chargeable_weight . " and max_measure >= " . $chargeable_weight );
	
	$mailbox_rates2 = $wpdb->get_results ( "SELECT base_rate, min_measure, max_measure from wp_sp_rates where delivery_type = 'US MailBox' and origin = '" . $new_origin . "' and destination = '" . $new_destination . "'" );
	
	$ocean_rates = $wpdb->get_results ( "SELECT base_rate, additional_rate, markup from wp_sp_rates where delivery_type = 'Ocean Cargo' and origin = '" . $new_origin . "' and destination = '" . $new_destination . "' and min_measure <= " . $new_cubic_feet . " and max_measure >= " . $new_cubic_feet );
	
	$barrel_ocean_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = 'Ocean Cargo Barrel' and origin = '" . $new_origin . "' and destination = '" . $new_destination . "'" );
	
	$econtainer_ocean_rates = $wpdb->get_results ( "SELECT base_rate, additional_rate, markup from wp_sp_rates where delivery_type = 'Ocean Cargo E Container' and origin = '" . $new_origin . "' and destination = '" . $new_destination . "'" );
	
	$ehcontainer_ocean_rates = $wpdb->get_results ( "SELECT base_rate from wp_sp_rates where delivery_type = 'Ocean Cargo EH Container' and origin = '" . $new_origin . "' and destination = '" . $new_destination . "'" );
	
	$air_rates = $wpdb->get_results ( "SELECT base_rate, additional_rate, FSC, markup from wp_sp_rates where delivery_type = 'Air Cargo' and origin = '" . $new_origin . "' and destination = '" . $new_destination . "'" );
	
	$small_package_rates = $wpdb->get_results ( "SELECT base_rate, additional_rate, markup from wp_sp_rates where delivery_type = 'Small Package' and origin = '" . $new_origin . "' and destination = '" . $new_destination . "' and min_measure <= " . $chargeable_weight . " and max_measure >= " . $chargeable_weight );
	
	$express_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = 'Express' and origin = '" . $new_origin . "' and destination = '" . $new_destination . "' and min_measure <= " . $express_chargeable_weight . " and max_measure >= " . $express_chargeable_weight );
	
	$express_letter_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = 'Express Letter' and origin = '" . $new_origin . "' and destination = '" . $new_destination . "'" );
	
	$express_document_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = concat('Express Document',$documentQty) and origin = '" . $new_origin . "' and destination = '" . $new_destination . "'" );
	
	$caribbean_same_day_express_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = 'Caribbean Same Day Express' and destination = '" . $new_destination . "' and min_measure <= " . $express_chargeable_weight . " and max_measure >= " . $express_chargeable_weight );
	
	$caribbean_two_day_express_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = 'Caribbean 2 Day Express' and destination = '" . $new_destination . "' and min_measure <= " . $express_chargeable_weight . " and max_measure >= " . $express_chargeable_weight );
	
	$caribbean_same_day_express_letter_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = 'Caribbean Same Day Express Letter' and destination = '" . $new_destination . "'" );
	
	$caribbean_two_day_express_letter_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = 'Caribbean 2 Day Express Letter' and destination = '" . $new_destination . "'" );
	
	$caribbean_same_day_express_document_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = concat('Caribbean Same Day Express Document',$documentQty) and destination = '" . $new_destination . "'" );
	
	$caribbean_two_day_express_document_rates = $wpdb->get_results ( "SELECT base_rate, markup from wp_sp_rates where delivery_type = concat('Caribbean 2 Day Express Document',$documentQty) and destination = '" . $new_destination . "'" );
	
	/* ---Get Domestic Rates for each Delivery Type--- */
	
	// $domestic_rates_smallpkg = $wpdb->get_results("SELECT base_rate, additional_rate, zones from wp_sp_rates where delivery_type like 'Domestic Small Package' and origin = " . $pickupZip);
	
	$domestic_rates_smallpkg = $wpdb->get_results ( "SELECT r.base_rate, r.additional_rate, r.zones from wp_sp_rates r inner join wp_sp_domestic_zones d on r.zones = d.zones where r.delivery_type like 'Domestic Small Package' and r.min_measure <= " . $actual_weight . " and r.max_measure >= " . $actual_weight . " and d.pickup_zip = " . $pickupZip . " and d.warehouse_state = '" . $origin . "'" );
	
	// $domestic_rates_air_ocean = $wpdb->get_results("SELECT base_rate, additional_rate, zones from wp_sp_rates where delivery_type like 'Domestic' and origin = " . $pickupZip);
	
	$domestic_rates_air_ocean = $wpdb->get_results ( "SELECT r.base_rate, r.additional_rate, r.zones from wp_sp_rates r inner join wp_sp_domestic_zones d on r.zones = d.zones where r.delivery_type like 'Domestic' and r.min_measure <= " . $actual_weight . " and r.max_measure >= " . $actual_weight . " and d.pickup_zip = " . $pickupZip . " and d.warehouse_state = '" . $origin . "'" );
	
	// $domestic_rates_barrel = $wpdb->get_results("SELECT base_rate, additional_rate, zones from wp_sp_rates where delivery_type like 'Domestic Barrel' and origin = " . $pickupZip);
	
	$domestic_rates_barrel = $wpdb->get_results ( "SELECT r.base_rate, r.additional_rate, r.zones from wp_sp_rates r inner join wp_sp_domestic_zones d on r.zones = d.zones where r.delivery_type like 'Domestic Barrel' and d.pickup_zip = " . $pickupZip . " and d.warehouse_state = '" . $origin . "'" );
	
	// $domestic_rates_express= $wpdb->get_results("SELECT base_rate, additional_rate, zones from wp_sp_rates where delivery_type like 'Domestic Express' and origin = " . $pickupZip);
	
	$domestic_rates_express = $wpdb->get_results ( "SELECT r.base_rate, r.additional_rate, r.zones from wp_sp_rates r inner join wp_sp_domestic_zones d on r.zones = d.zones where r.delivery_type like 'Domestic Express' and r.min_measure <= " . $actual_weight . " and r.max_measure >= " . $actual_weight . " and d.pickup_zip = " . $pickupZip . " and d.warehouse_state = '" . $origin . "'" );
	
	$us_domestic_rates = $wpdb->get_results ( "SELECT r.base_rate, r.additional_rate, r.zones from wp_sp_rates r inner join wp_sp_domestic_zones d on r.zones = d.zones where r.delivery_type like 'Domestic' and r.min_measure <= " . $actual_weight . " and r.max_measure >= " . $actual_weight . " and d.warehouse_state  = '" . $pickupState . "' and d.pickup_state = '" . $deliveryState . "' and d.pickup_zip = " . $deliveryZip );
	
	// ===================================================================================
	// ---GET DOMESTIC COMMODITY RATES FOR ALL PACKAGES
	// ===================================================================================
	// ---Small Package Commodity Script
	// ==================================
	$domestic_commodity_rates_smallpkg = $wpdb->get_results ( "SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight1 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass1 . "' and f.Item_Description = '" . $pkgDescription1 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic Small Package'  and r.min_measure <= " . $actual_pkgWeight1 . "  and r.max_measure >= " . $actual_pkgWeight1 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "' union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight2 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass2 . "' and f.Item_Description = '" . $pkgDescription2 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic Small Package'  and r.min_measure <= " . $actual_pkgWeight2 . "  and r.max_measure >= " . $actual_pkgWeight2 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "' union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight3 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass3 . "' and f.Item_Description = '" . $pkgDescription3 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic Small Package'  and r.min_measure <= " . $actual_pkgWeight3 . "  and r.max_measure >= " . $actual_pkgWeight3 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "' union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight4 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass4 . "' and f.Item_Description = '" . $pkgDescription4 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic Small Package'  and r.min_measure <= " . $actual_pkgWeight4 . "  and r.max_measure >= " . $actual_pkgWeight4 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "' union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight5 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass5 . "' and f.Item_Description = '" . $pkgDescription5 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic Small Package'  and r.min_measure <= " . $actual_pkgWeight5 . "  and r.max_measure >= " . $actual_pkgWeight5 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "'" );
	
	// ---Air & Ocean Cargo Commodity Script
	// ======================================
	$domestic_commodity_rates_air_ocean = $wpdb->get_results ( "SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight1 . "' as pkgweight, '" . $pkgType1 . "' as pkgtype, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass1 . "' and f.Item_Description = '" . $pkgDescription1 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic'  and r.min_measure <= " . $actual_pkgWeight1 . "  and r.max_measure >= " . $actual_pkgWeight1 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "' union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight2 . "' as pkgweight, '" . $pkgType2 . "' as pkgtype, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass2 . "' and f.Item_Description = '" . $pkgDescription2 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic'  and r.min_measure <= " . $actual_pkgWeight2 . "  and r.max_measure >= " . $actual_pkgWeight2 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "' union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight3 . "' as pkgweight, '" . $pkgType3 . "' as pkgtype, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass3 . "' and f.Item_Description = '" . $pkgDescription3 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic'  and r.min_measure <= " . $actual_pkgWeight3 . "  and r.max_measure >= " . $actual_pkgWeight3 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "' union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight4 . "' as pkgweight, '" . $pkgType4 . "' as pkgtype, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass4 . "' and f.Item_Description = '" . $pkgDescription4 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic'  and r.min_measure <= " . $actual_pkgWeight4 . "  and r.max_measure >= " . $actual_pkgWeight4 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "' union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight5 . "' as pkgweight, '" . $pkgType5 . "' as pkgtype, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass5 . "' and f.Item_Description = '" . $pkgDescription5 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic'  and r.min_measure <= " . $actual_pkgWeight5 . "  and r.max_measure >= " . $actual_pkgWeight5 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "'" );
	// ==============================================================================
	
	// ---Express Commodity Script
	// ==================================
	$domestic_commodity_rates_express = $wpdb->get_results ( "SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight1 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass1 . "' and f.Item_Description = '" . $pkgDescription1 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic Express'  and r.min_measure <= " . $actual_pkgWeight1 . "  and r.max_measure >= " . $actual_pkgWeight1 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "' union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight2 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass2 . "' and f.Item_Description = '" . $pkgDescription2 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic Express'  and r.min_measure <= " . $actual_pkgWeight2 . "  and r.max_measure >= " . $actual_pkgWeight2 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "' union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight3 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass3 . "' and f.Item_Description = '" . $pkgDescription3 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic Express'  and r.min_measure <= " . $actual_pkgWeight3 . "  and r.max_measure >= " . $actual_pkgWeight3 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "' union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight4 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass4 . "' and f.Item_Description = '" . $pkgDescription4 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic Express'  and r.min_measure <= " . $actual_pkgWeight4 . "  and r.max_measure >= " . $actual_pkgWeight4 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "' union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight5 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass5 . "' and f.Item_Description = '" . $pkgDescription5 . "') as commodity_rate from wp_sp_rates as r  inner join wp_sp_domestic_zones as d on r.zones = d.zones  where r.delivery_type like 'Domestic Express'  and r.min_measure <= " . $actual_pkgWeight5 . "  and r.max_measure >= " . $actual_pkgWeight5 . "  and d.pickup_zip = " . $pickupZip . "  and d.warehouse_state = '" . $origin . "'" );
	
	// ---US Domestic Commodity Script
	// ==================================
	$domestic_us_commodity_rates = $wpdb->get_results ( "SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight1 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass1 . "' and f.Item_Description = '" . $pkgDescription1 . "') as commodity_rate from wp_sp_rates r  inner join wp_sp_domestic_zones d  on r.zones = d.zones  where r.delivery_type like 'Domestic'  and r.min_measure <= " . $actual_pkgWeight1 . "  and r.max_measure >= " . $actual_pkgWeight1 . "  and d.warehouse_state  = '" . $pickupState . "'  and d.pickup_state = '" . $deliveryState . "'  and d.pickup_zip = " . $deliveryZip . " union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight2 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass2 . "' and f.Item_Description = '" . $pkgDescription2 . "') as commodity_rate from wp_sp_rates r  inner join wp_sp_domestic_zones d  on r.zones = d.zones  where r.delivery_type like 'Domestic'  and r.min_measure <= " . $actual_pkgWeight2 . "  and r.max_measure >= " . $actual_pkgWeight2 . "  and d.warehouse_state  = '" . $pickupState . "'  and d.pickup_state = '" . $deliveryState . "'  and d.pickup_zip = " . $deliveryZip . " union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight3 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass3 . "' and f.Item_Description = '" . $pkgDescription3 . "') as commodity_rate from wp_sp_rates r  inner join wp_sp_domestic_zones d  on r.zones = d.zones  where r.delivery_type like 'Domestic'  and r.min_measure <= " . $actual_pkgWeight3 . "  and r.max_measure >= " . $actual_pkgWeight3 . "  and d.warehouse_state  = '" . $pickupState . "'  and d.pickup_state = '" . $deliveryState . "'  and d.pickup_zip = " . $deliveryZip . " union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight4 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass4 . "' and f.Item_Description = '" . $pkgDescription4 . "') as commodity_rate from wp_sp_rates r  inner join wp_sp_domestic_zones d  on r.zones = d.zones  where r.delivery_type like 'Domestic'  and r.min_measure <= " . $actual_pkgWeight4 . "  and r.max_measure >= " . $actual_pkgWeight4 . "  and d.warehouse_state  = '" . $pickupState . "'  and d.pickup_state = '" . $deliveryState . "'  and d.pickup_zip = " . $deliveryZip . " union all SELECT r.base_rate, r.additional_rate, r.zones, '" . $actual_pkgWeight5 . "' as pkgweight, (select f.rate from wp_sp_freight_class as f where f.class = '" . $pkgClass5 . "' and f.Item_Description = '" . $pkgDescription5 . "') as commodity_rate from wp_sp_rates r  inner join wp_sp_domestic_zones d  on r.zones = d.zones  where r.delivery_type like 'Domestic'  and r.min_measure <= " . $actual_pkgWeight5 . "  and r.max_measure >= " . $actual_pkgWeight5 . "  and d.warehouse_state  = '" . $pickupState . "'  and d.pickup_state = '" . $deliveryState . "'  and d.pickup_zip = " . $deliveryZip );
	// ==============================================================================
	
	/* ---Get Transit Times--- */
	
	// $zones = $wpdb->get_results("SELECT distinct zones from wp_sp_rates where origin = " . $pickupZip);
	$zones = $wpdb->get_results ( "SELECT distinct zones from wp_sp_domestic_zones where pickup_zip = " . $pickupZip . " and warehouse_state =  '" . $origin . "'" );
	$transit_zone = $zones [0]->zones;
	
	if (strpos ( $transit_zone, 'FL' ) !== false) {
		if ($transit_zone == 'ZONE1 FL') {
			$transit_origin = 'Miami, FL';
		} else if ($transit_zone == 'ZONE2 FL') {
			$transit_origin = 'Miami, FL';
		} else {
			$transit_origin = 'Orlando, FL';
		}
	} else {
		$transit_origin = $new_origin;
	}
	
	$transit_times = $wpdb->get_results ( "SELECT transit_time, depart_days from wp_sp_transit_times where origin = '" . $transit_origin . "' and destination = '" . $new_destination . "'" );
	// Assign Transit Time to Variables
	// if($pickupCountry == "United States" || $pickupcountry == "Miami US Warehouse" || $pickupcountry == "Orlando US Warehouse" || $pickupcountry == "Brooklyn NY USA" || $pickupcountry == "Boston US Warehouse" || $pickupcountry == "Huston US Warehouse" || $pickupcountry == "Atlanta US Warehouse" || $pickupcountry == "Pooleville MD USA" || $pickupcountry == "Philadelphia US Warehouse" || $pickupcountry == "Kearny NJ USA"){
	if (empty ( $transit_times )) {
		$air_transit_time = 'N/A';
		$air_depart_days = 'N/A';
		
		$ocean_transit_time = 'N/A';
		$ocean_depart_days = 'N/A';
	} else {
		if ($pkgType1 == "Barrel" || $pkgType2 == "Barrel" || $pkgType3 == "Barrel" || $pkgType4 == "Barrel" || $pkgType5 == "Barrel") {
			$air_transit_time = 'N/A';
			$air_depart_days = 'N/A';
		} else {
			$air_transit_time = $transit_times [0]->transit_time;
			$air_depart_days = $transit_times [0]->depart_days;
		}
		
		$ocean_transit_time = $transit_times [1]->transit_time;
		$ocean_depart_days = $transit_times [1]->depart_days;
	}
	/*
	 * }else{
	 * $air_transit_time = 'N/A';
	 * $air_depart_days = 'N/A';
	 *
	 * $ocean_transit_time = 'N/A';
	 * $ocean_depart_days = 'N/A';
	 * }
	 */
	
	// Assign Rates to Variables
	$base_rate_mailbox = $mailbox_rates [0]->base_rate;
	$additional_rate_mailbox = $mailbox_rates [0]->additional_rate;
	$markup_mailbox = $mailbox_rates [0]->markup;
	
	$base_rate_ocean = $ocean_rates [0]->base_rate;
	$additional_rate_ocean = $ocean_rates [0]->additional_rate;
	$markup_ocean = $ocean_rates [0]->markup;
	
	$base_rate_ocean_barrel = $barrel_ocean_rates [0]->base_rate;
	$markup_ocean_barrel = $barrel_ocean_rates [0]->markup;
	
	$base_rate_ocean_econtainer = $econtainer_ocean_rates [0]->base_rate;
	$additional_rate_econtainer = $econtainer_ocean_rates [0]->additional_rate;
	$markup_ocean_econtainer = $econtainer_ocean_rates [0]->markup;
	
	$base_rate_ocean_ehcontainer = $ehcontainer_ocean_rates [0]->base_rate;
	
	$base_rate_air = $air_rates [0]->base_rate;
	$additional_rate_air = $air_rates [0]->additional_rate;
	$markup_air = $air_rates [0]->markup;
	$fsc_air = $air_rates [0]->FSC;
	
	$base_rate_small_package = $small_package_rates [0]->base_rate;
	$additional_rate_small_package = $small_package_rates [0]->additional_rate;
	$markup_small_package = $small_package_rates [0]->markup;
	
	$base_rate_express = $express_rates [0]->base_rate;
	$markup_express = $express_rates [0]->markup;
	
	$base_rate_express_letter = $express_letter_rates [0]->base_rate;
	$markup_express_letter = $express_letter_rates [0]->markup;
	
	$base_rate_express_document = $express_document_rates [0]->base_rate;
	$markup_express_document = $express_document_rates [0]->markup;
	
	$base_rate_caribbean_same_day_express = $caribbean_same_day_express_rates [0]->base_rate;
	$markup_caribbean_same_day_express = $caribbean_same_day_express_rates [0]->markup;
	
	$base_rate_caribbean_two_day_express = $caribbean_two_day_express_rates [0]->base_rate;
	$markup_caribbean_two_day_express = $caribbean_two_day_express_rates [0]->markup;
	
	$base_rate_caribbean_same_day_express_letter = $caribbean_same_day_express_letter_rates [0]->base_rate;
	$markup_caribbean_same_day_express_letter = $caribbean_same_day_express_letter_rates [0]->markup;
	
	$base_rate_caribbean_two_day_express_letter = $caribbean_two_day_express_letter_rates [0]->base_rate;
	$markup_caribbean_two_day_express_letter = $caribbean_two_day_express_letter_rates [0]->markup;
	
	$base_rate_caribbean_same_day_express_document = $caribbean_same_day_express_document_rates [0]->base_rate;
	$markup_caribbean_same_day_express_document = $caribbean_same_day_express_document_rates [0]->markup;
	
	$base_rate_caribbean_two_day_express_document = $caribbean_two_day_express_document_rates [0]->base_rate;
	$markup_caribbean_two_day_express_document = $caribbean_two_day_express_document_rates [0]->markup;
	
	$base_rate_domestic_smallpkg = $domestic_rates_smallpkg [0]->base_rate;
	$additional_rate_domestic_smallpkg = $domestic_rates_smallpkg [0]->additional_rate;
	$zones_domestic_smallpkg = $domestic_rates_smallpkg [0]->zones;
	
	// $domestic_calculated_rate_smallpkg = number_format(($chargeable_weight * $additional_rate_domestic_smallpkg),2,'.','');
	$domestic_calculated_rate_smallpkg = number_format ( ($actual_weight * $additional_rate_domestic_smallpkg), 2, '.', '' );
	if ($pickupCountry == 'Miami US Warehouse') {
		$domestic_cost_smallpkg = $base_rate_domestic_smallpkg;
	} else if ($pickupCountry == 'Orlando US Warehouse') {
		$domestic_cost_smallpkg = $base_rate_domestic_smallpkg;
	} else if ($pickupCountry == 'Brooklyn NY USA') {
		$domestic_cost_smallpkg = $base_rate_domestic_smallpkg;
	} else if ($pickupCountry == 'Boston US Warehouse') {
		$domestic_cost_smallpkg = $base_rate_domestic_smallpkg;
	} else if ($pickupCountry == 'Atlanta US Warehouse') {
		$domestic_cost_smallpkg = $base_rate_domestic_smallpkg;
	} else if ($pickupCountry == 'Pooleville MD USA') {
		$domestic_cost_smallpkg = $base_rate_domestic_smallpkg;
	} else if ($pickupCountry == 'Philadelphia US Warehouse') {
		$domestic_cost_smallpkg = $base_rate_domestic_smallpkg;
	} else if ($pickupCountry == 'Kearny NJ USA') {
		$domestic_cost_smallpkg = $base_rate_domestic_smallpkg;
	} else if ($pickupCountry == 'Huston US Warehouse') {
		$domestic_cost_smallpkg = $base_rate_domestic_smallpkg;
	} else {
		if ($domestic_calculated_rate_smallpkg >= $base_rate_domestic_smallpkg) {
			$domestic_cost_smallpkg = $domestic_calculated_rate_smallpkg;
		} else {
			$domestic_cost_smallpkg = $base_rate_domestic_smallpkg;
		}
	}
	
	$base_rate_domestic_air_ocean = $domestic_rates_air_ocean [0]->base_rate;
	$additional_rate_domestic_air_ocean = $domestic_rates_air_ocean [0]->additional_rate;
	$zones_domestic_air_ocean = $domestic_rates_air_ocean [0]->zones;
	
	// $domestic_calculated_rate_air_ocean = number_format(($chargeable_weight * $additional_rate_domestic_air_ocean),2,'.','');
	$domestic_calculated_rate_air_ocean = number_format ( ($actual_weight * $additional_rate_domestic_air_ocean), 2, '.', '' );
	if ($deliveryCountry != 'United States') {
		if ($pickupCountry == 'Miami US Warehouse') {
			$domestic_cost_air_ocean = $base_rate_domestic_air_ocean;
		} else if ($pickupCountry == 'Orlando US Warehouse') {
			$domestic_cost_air_ocean = $base_rate_domestic_air_ocean;
		} else if ($pickupCountry == 'Brooklyn NY USA') {
			$domestic_cost_air_ocean = $base_rate_domestic_air_ocean;
		} else if ($pickupCountry == 'Boston US Warehouse') {
			$domestic_cost_air_ocean = $base_rate_domestic_air_ocean;
		} else if ($pickupCountry == 'Atlanta US Warehouse') {
			$domestic_cost_air_ocean = $base_rate_domestic_air_ocean;
		} else if ($pickupCountry == 'Pooleville MD USA') {
			$domestic_cost_air_ocean = $base_rate_domestic_air_ocean;
		} else if ($pickupCountry == 'Philadelphia US Warehouse') {
			$domestic_cost_air_ocean = $base_rate_domestic_air_ocean;
		} else if ($pickupCountry == 'Kearny NJ USA') {
			$domestic_cost_air_ocean = $base_rate_domestic_air_ocean;
		} else if ($pickupCountry == 'Huston US Warehouse') {
			$domestic_cost_air_ocean = $base_rate_domestic_air_ocean;
		} else {
			if ($domestic_calculated_rate_air_ocean >= $base_rate_domestic_air_ocean) {
				$domestic_cost_air_ocean = $domestic_calculated_rate_air_ocean;
			} else {
				$domestic_cost_air_ocean = $base_rate_domestic_air_ocean;
			}
		}
	} else {
		if ($domestic_calculated_rate_air_ocean >= $base_rate_domestic_air_ocean) {
			$domestic_cost_air_ocean = $domestic_calculated_rate_air_ocean;
		} else {
			$domestic_cost_air_ocean = $base_rate_domestic_air_ocean;
		}
	}
	
	$base_rate_us_domestic = $us_domestic_rates [0]->base_rate;
	$additional_rate_us_domestic = $us_domestic_rates [0]->additional_rate;
	$zones_us_domestic = $us_domestic_rates [0]->zones;
	
	$domestic_us_calculated_rate = number_format ( ($actual_weight * $additional_rate_us_domestic), 2, '.', '' );
	if ($domestic_us_calculated_rate >= $base_rate_us_domestic) {
		$us_domestic_cost = $domestic_us_calculated_rate;
	} else {
		$us_domestic_cost = $base_rate_us_domestic;
	}
	
	$base_rate_domestic_barrel = $domestic_rates_barrel [0]->base_rate;
	$additional_rate_domestic_barrel = $domestic_rates_barrel [0]->additional_rate;
	$zones_domestic_barrel = $domestic_rates_barrel [0]->zones;
	
	// $domestic_calculated_rate_barrel = number_format(($chargeable_weight * $additional_rate_domestic_barrel),2,'.','');
	$domestic_calculated_rate_barrel = number_format ( ($actual_weight * $additional_rate_domestic_barrel), 2, '.', '' );
	if ($deliveryCountry != 'United States') {
		if ($pickupCountry == 'Miami US Warehouse') {
			$domestic_cost_barrel = $base_rate_domestic_barrel;
			// $domestic_cost_barrel = 0;
		} else if ($pickupCountry == 'Orlando US Warehouse') {
			$domestic_cost_barrel = $base_rate_domestic_barrel;
			// $domestic_cost_barrel = 0;
		} else if ($pickupCountry == 'Brooklyn NY USA') {
			$domestic_cost_barrel = $base_rate_domestic_barrel;
			// $domestic_cost_barrel = 0;
		} else if ($pickupCountry == 'Boston US Warehouse') {
			$domestic_cost_barrel = $base_rate_domestic_barrel;
			// $domestic_cost_barrel = 0;
		} else if ($pickupCountry == 'Atlanta US Warehouse') {
			$domestic_cost_barrel = $base_rate_domestic_barrel;
			// $domestic_cost_barrel = 0;
		} else if ($pickupCountry == 'Pooleville MD USA') {
			$domestic_cost_barrel = $base_rate_domestic_barrel;
			// $domestic_cost_barrel = 0;
		} else if ($pickupCountry == 'Philadelphia US Warehouse') {
			$domestic_cost_barrel = $base_rate_domestic_barrel;
			// $domestic_cost_barrel = 0;
		} else if ($pickupCountry == 'Kearny NJ USA') {
			$domestic_cost_barrel = $base_rate_domestic_barrel;
			// $domestic_cost_barrel = 0;
		} else if ($pickupCountry == 'Huston US Warehouse') {
			$domestic_cost_barrel = $base_rate_domestic_barrel;
			// $domestic_cost_barrel = 0;
		} else {
			if ($domestic_calculated_rate_barrel >= $base_rate_domestic_barrel) {
				$domestic_cost_barrel = $domestic_calculated_rate_barrel;
			} else {
				$domestic_cost_barrel = $base_rate_domestic_barrel;
			}
		}
	} else {
		if ($domestic_calculated_rate_barrel >= $base_rate_domestic_barrel) {
			$domestic_cost_barrel = $domestic_calculated_rate_barrel;
		} else {
			$domestic_cost_barrel = $base_rate_domestic_barrel;
		}
	}
	
	$base_rate_domestic_express = $domestic_rates_express [0]->base_rate;
	$additional_rate_domestic_express = $domestic_rates_express [0]->additional_rate;
	$zones_domestic_express = $domestic_rates_express [0]->zones;
	
	// $domestic_calculated_rate_express = number_format(($express_chargeable_weight * $additional_rate_domestic_express),2,'.','');
	$domestic_calculated_rate_express = number_format ( ($express_actual_weight * $additional_rate_domestic_express), 2, '.', '' );
	if ($domestic_calculated_rate_express >= $base_rate_domestic_express) {
		$domestic_cost_express = $domestic_calculated_rate_express;
	} else {
		$domestic_cost_express = $base_rate_domestic_express;
	}
	
	// ===================================================================================
	// ---Commodity Calculation for Small Package/Mail Box
	// ====================================================
	$smallpkg_commodity_cost = '0.00';
	
	for($i = 0; $i < count ( $domestic_commodity_rates_smallpkg ); $i ++) {
		$base_rate_smallpkg_commodity = $domestic_commodity_rates_smallpkg [$i]->base_rate;
		$additional_rate_smallpkg_commodity = $domestic_commodity_rates_smallpkg [$i]->additional_rate;
		$pkgweight_smallpkg_commodity = $domestic_commodity_rates_smallpkg [$i]->pkgweight;
		$smallpkg_rate_commodity = $domestic_commodity_rates_smallpkg [$i]->commodity_rate;
		
		$smallpkg_per_lb_rate = number_format ( ($additional_rate_smallpkg_commodity * $pkgweight_smallpkg_commodity), 2, '.', '' );
		
		if ($smallpkg_per_lb_rate >= $base_rate_smallpkg_commodity) {
			$smallpkg_domestic_commodity = $smallpkg_per_lb_rate;
		} else {
			$smallpkg_domestic_commodity = $base_rate_smallpkg_commodity;
		}
		
		$smallpkg_commodity_cost = number_format ( ($smallpkg_commodity_cost + ($smallpkg_domestic_commodity * ($smallpkg_rate_commodity / 100))), 2, '.', '' );
	}
	// ===================================================================================
	
	// ===================================================================================
	// ---Commodity Calculation for Air/Ocean Cargo
	// =============================================
	$air_ocean_commodity_cost = '0.00';
	
	for($i = 0; $i < count ( $domestic_commodity_rates_air_ocean ); $i ++) {
		$base_rate_air_ocean_commodity = $domestic_commodity_rates_air_ocean [$i]->base_rate;
		$additional_rate_air_ocean_commodity = $domestic_commodity_rates_air_ocean [$i]->additional_rate;
		$pkgweight_air_ocean_commodity = $domestic_commodity_rates_air_ocean [$i]->pkgweight;
		$pkgtype_air_ocean_commodity = $domestic_commodity_rates_air_ocean [$i]->pkgtype;
		$air_ocean_rate_commodity = $domestic_commodity_rates_air_ocean [$i]->commodity_rate;
		
		$air_ocean_per_lb_rate = number_format ( ($additional_rate_air_ocean_commodity * $pkgweight_air_ocean_commodity), 2, '.', '' );
		
		if ($air_ocean_per_lb_rate >= $base_rate_air_ocean_commodity) {
			$air_ocean_domestic_commodity = $air_ocean_per_lb_rate;
		} else {
			$air_ocean_domestic_commodity = $base_rate_air_ocean_commodity;
		}
		if ($pkgtype_air_ocean_commodity == 'E Container') {
			$air_ocean_commodity_cost = $air_ocean_commodity_cost + '0.00';
		} else if ($pkgtype_air_ocean_commodity == 'EH Container') {
			$air_ocean_commodity_cost = $air_ocean_commodity_cost + '0.00';
		} else if ($pkgtype_air_ocean_commodity == 'Barrel') {
			$air_ocean_commodity_cost = $air_ocean_commodity_cost + '0.00';
		} else {
			$air_ocean_commodity_cost = number_format ( ($air_ocean_commodity_cost + ($air_ocean_domestic_commodity * ($air_ocean_rate_commodity / 100))), 2, '.', '' );
		}
	}
	// ===================================================================================
	
	// ===================================================================================
	// ---Commodity Calculation for Express
	// ============================================
	$express_commodity_cost = '0.00';
	
	for($i = 0; $i < count ( $domestic_commodity_rates_express ); $i ++) {
		$base_rate_express_commodity = $domestic_commodity_rates_express [$i]->base_rate;
		$additional_rate_express_commodity = $domestic_commodity_rates_express [$i]->additional_rate;
		$pkgweight_express_commodity = $domestic_commodity_rates_express [$i]->pkgweight;
		$express_rate_commodity = $domestic_commodity_rates_express [$i]->commodity_rate;
		
		$express_per_lb_rate = number_format ( ($additional_rate_express_commodity * $pkgweight_express_commodity), 2, '.', '' );
		
		if ($express_per_lb_rate >= $base_rate_express_commodity) {
			$express_domestic_commodity = $express_per_lb_rate;
		} else {
			$express_domestic_commodity = $base_rate_express_commodity;
		}
		$express_commodity_cost = number_format ( ($express_commodity_cost + ($express_domestic_commodity * ($express_rate_commodity / 100))), 2, '.', '' );
	}
	// ===================================================================================
	
	// ===================================================================================
	// ---Commodity Calculation for US Domestic
	// ============================================
	$us_domestic_commodity_cost = '0.00';
	
	for($i = 0; $i < count ( $domestic_us_commodity_rates ); $i ++) {
		$base_rate_us_domestic_commodity = $domestic_us_commodity_rates [$i]->base_rate;
		$additional_rate_us_domestic_commodity = $domestic_us_commodity_rates [$i]->additional_rate;
		$pkgweight_us_domestic_commodity = $domestic_us_commodity_rates [$i]->pkgweight;
		$us_domestic_rate_commodity = $domestic_us_commodity_rates [$i]->commodity_rate;
		
		$us_domestic_per_lb_rate = number_format ( ($additional_rate_us_domestic_commodity * $pkgweight_us_domestic_commodity), 2, '.', '' );
		
		if ($us_domestic_per_lb_rate >= $base_rate_us_domestic_commodity) {
			$us_domestic_commodity = $us_domestic_per_lb_rate;
		} else {
			$us_domestic_commodity = $base_rate_us_domestic_commodity;
		}
		$us_domestic_commodity_cost = number_format ( ($us_domestic_commodity_cost + ($us_domestic_commodity * ($us_domestic_rate_commodity / 100))), 2, '.', '' );
	}
	// ===================================================================================
	
	// ===================================================================================
	// PICKUP ZIP?
	// ===================================================================================
	if ($pickupZip != 33166) {
		$warehouse_zip = 'No';
	} else {
		$warehouse_zip = 'Yes';
	}
	// ===================================================================================
	
	// ===================================================================================
	// MAILBOX CALCULATION
	// ===================================================================================
	if ($new_origin === 'MIA') {
		if (empty ( $mailbox_rates2 )) {
			$us_mailbox = 'N/A';
			$us_mailbox_freight_total = 'N/A';
		} else {
			if ($mailbox_chargeable_weight > 150 || $mailbox_chargeable_weight <= 0) {
				$us_mailbox = 'N/A';
				$us_mailbox_freight_total = 'N/A';
			} else {
				
				if ($cntHazardous > 0) {
					$us_mailbox = number_format ( ((($base_rate_mailbox + (($mailbox_chargeable_weight - 1) * $additional_rate_mailbox)) * 1.5) + (75.00 * $cntHazardous)), 2, '.', '' );
				} else {
					$us_mailbox = number_format ( ($base_rate_mailbox + (($mailbox_chargeable_weight - 1) * $additional_rate_mailbox)), 2, '.', '' );
				}
				
				$wgt = $mailbox_chargeable_weight;
				$us_mailbox_freight = '0.00';
				$pound_range_list = array ();
				$rates_list = array ();
				$freight_list = array ();
				$decremental_wgt = array ();
				
				for($i = 0; $i < count ( $mailbox_rates2 ); $i ++) {
					if ($wgt <= 0) {
						break;
					} else {
						$pound_range = (($mailbox_rates2 [$i]->max_measure - $mailbox_rates2 [$i]->min_measure) + 1);
						$pound_range_list [] = array (
								"value" => $pound_range 
						);
						$base_rate_mailbox2 = $mailbox_rates2 [$i]->base_rate;
						$rates_list [] = array (
								"value" => $base_rate_mailbox2 
						);
						if ($wgt >= $pound_range) {
							$use_weight = $pound_range;
						} else {
							$use_weight = $wgt;
						}
						$incremental_freight = ($use_weight * $base_rate_mailbox2);
						$freight_list [] = array (
								"value" => $incremental_freight 
						);
						$us_mailbox_freight = number_format ( ($us_mailbox_freight + ($use_weight * $base_rate_mailbox2)), 2, '.', '' );
						$wgt = $wgt - $use_weight;
						$decremental_wgt [] = array (
								"value" => $wgt 
						);
					}
				}
				
				if (($destination == "Dominica") && ($mailbox_chargeable_weight >= 2)) {
					$us_mailbox_freight = number_format ( ($us_mailbox_freight - 8), 2, '.', '' );
				} else {
					$us_mailbox_freight = number_format ( ($us_mailbox_freight), 2, '.', '' );
				}
				if ($cntHazardous > 0) {
					$us_mailbox_freight_total = number_format ( (($us_mailbox_freight * 1.5) + (75.00 * $cntHazardous)), 2, '.', '' );
				} else {
					$us_mailbox_freight_total = number_format ( ($us_mailbox_freight), 2, '.', '' );
				}
				
				// if($warehouse_zip == 'No'){
				if ($pickupCountry != 'Miami US Warehouse') {
					// $us_mailbox_freight_total = number_format(($us_mailbox_freight_total + $domestic_cost_air_ocean),2,'.','');
					$us_mailbox_freight_total = number_format ( ($us_mailbox_freight_total + $domestic_cost_smallpkg + $smallpkg_commodity_cost), 2, '.', '' );
				} else {
					$us_mailbox_freight_total = number_format ( ($us_mailbox_freight_total + $smallpkg_commodity_cost), 2, '.', '' );
				}
			}
		}
	} else {
		$us_mailbox = 'N/A';
		$us_mailbox_freight_total = 'N/A';
	}
	// ===================================================================================
	
	// ===================================================================================
	// OCEAN CALCULATION
	// ===================================================================================
	if ($barrelItems > 0) {
		if (empty ( $barrel_ocean_rates )) {
			$ocean_barrel = 'N/A';
		} else {
			/*
			 * if($BarrelhazardItems> 0){
			 * $ocean_barrel = number_format(((((($base_rate_ocean_barrel + $markup_ocean_barrel) + ((($base_rate_ocean_barrel + $markup_ocean_barrel) * 0.8) * ($barrelQty -1))) * 1.5) + (75.00 * $BarrelhazardItems)) + $domestic_cost_barrel),2,'.','');
			 * }else{
			 * $ocean_barrel = number_format((($base_rate_ocean_barrel + $markup_ocean_barrel) + ((($base_rate_ocean_barrel + $markup_ocean_barrel) * 0.8) * ($barrelQty -1)) + $domestic_cost_barrel),2,'.','');
			 * }
			 */
			if ($BarrelhazardItems > 0) {
				$ocean_barrel = number_format ( ((((($base_rate_ocean_barrel + $markup_ocean_barrel) * $barrelQty) * 1.5) + (75.00 * $BarrelhazardItems)) + ($domestic_cost_barrel + (($domestic_cost_barrel * 0.8) * ($barrelQty - 1)))), 2, '.', '' );
			} else {
				$ocean_barrel = number_format ( ((($base_rate_ocean_barrel + $markup_ocean_barrel) * $barrelQty) + ($domestic_cost_barrel + (($domestic_cost_barrel * 0.8) * ($barrelQty - 1)))), 2, '.', '' );
			}
		}
	} else {
		$ocean_barrel = 'N/A';
	}
	
	if ($econtainerItems > 0) {
		$domestic_cost_air_ocean_ec = 0;
		if (empty ( $econtainer_ocean_rates )) {
			$ocean_econtainer = 'N/A';
		} else {
			if ($econtainerhazardItems > 0) {
				$ocean_econtainer = number_format ( ((((($new_cubic_feet_econtainer * $additional_rate_econtainer) + ($markup_ocean_econtainer * $econtainerQty)) * 1.5) + (75.00 * $econtainerhazardItems)) + $domestic_cost_air_ocean_ec), 2, '.', '' );
			} else {
				$ocean_econtainer = number_format ( ((($new_cubic_feet_econtainer * $additional_rate_econtainer) + ($markup_ocean_econtainer * $econtainerQty)) + $domestic_cost_air_ocean_ec), 2, '.', '' );
			}
		}
	} else {
		$ocean_econtainer = 'N/A';
	}
	
	if ($ehcontainerItems > 0) {
		$domestic_cost_air_ocean_ehc = 0;
		if (empty ( $ehcontainer_ocean_rates )) {
			$ocean_ehcontainer = 'N/A';
		} else {
			if ($ehcontainerhazardItems > 0) {
				$ocean_ehcontainer = number_format ( (((($base_rate_ocean_ehcontainer * $ehcontainerQty) * 1.5) + (75.00 * $ehcontainerhazardItems)) + $domestic_cost_air_ocean_ehc), 2, '.', '' );
			} else {
				$ocean_ehcontainer = number_format ( (($base_rate_ocean_ehcontainer * $ehcontainerQty) + $domestic_cost_air_ocean_ehc), 2, '.', '' );
			}
		}
	} else {
		$ocean_ehcontainer = 'N/A';
	}
	
	if ($nonBarrelOrContainerItems > 0) {
		if (empty ( $ocean_rates )) {
			$ocean_cargo = 'N/A';
		} else {
			if ($nonBarrelOrContainerhazardItems > 0) {
				// $ocean_cargo = number_format((((($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean)) * 1.5) + (75.00 * $nonBarrelOrContainerhazardItems)) + $domestic_cost_air_ocean + 35),2,'.','');
				$ocean_cargo = number_format ( (((($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean)) * 1.5) + (75.00 * $nonBarrelOrContainerhazardItems)) + $domestic_cost_air_ocean + $air_ocean_commodity_cost), 2, '.', '' );
			} else {
				// $ocean_cargo = number_format((($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean)) + $domestic_cost_air_ocean + 35),2,'.','');
				$ocean_cargo = number_format ( (($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean)) + $domestic_cost_air_ocean + $air_ocean_commodity_cost), 2, '.', '' );
			}
		}
	} else {
		$ocean_cargo = 'N/A';
	}
	$total_ocean_freight = number_format ( ($ocean_barrel + $ocean_econtainer + $ocean_ehcontainer + $ocean_cargo), 2, '.', '' );
	$fullservice_barrel_ocean_freight = number_format ( ($total_ocean_freight + (50.00 * $barrelQty)), 2, '.', '' );
	// ===================================================================================
	
	// ===================================================================================
	// AIR CALCULATION
	// ===================================================================================
	if (empty ( $air_rates )) {
		$air_cargo = 'N/A';
	} else {
		if ($pkgType1 == "Barrel" || $pkgType2 == "Barrel" || $pkgType3 == "Barrel" || $pkgType4 == "Barrel" || $pkgType5 == "Barrel") {
			$air_cargo = 'N/A';
		} else {
			if ($chargeable_weight <= 0) {
				$air_cargo = 'N/A';
			} else {
				$air_cargo_per_lb = (($additional_rate_air + $fsc_air) * $chargeable_weight);
				if ($air_cargo_per_lb >= $base_rate_air) {
					$airway = 'Yes';
					if ($cntHazardous > 0) {
						$air_cargo = number_format ( ((($air_cargo_per_lb * 1.5) + (75.00 * $cntHazardous)) + $domestic_cost_air_ocean + $air_ocean_commodity_cost + 25), 2, '.', '' );
					} else {
						$air_cargo = number_format ( ($air_cargo_per_lb + $domestic_cost_air_ocean + $air_ocean_commodity_cost + 25), 2, '.', '' );
					}
				} else {
					$airway = 'No';
					if ($cntHazardous > 0) {
						$air_cargo = number_format ( ((($base_rate_air * 1.5) + (75.00 * $cntHazardous)) + $domestic_cost_air_ocean + $air_ocean_commodity_cost), 2, '.', '' );
					} else {
						$air_cargo = number_format ( ($base_rate_air + $domestic_cost_air_ocean + $air_ocean_commodity_cost), 2, '.', '' );
					}
				}
			}
		}
	}
	// ===================================================================================
	
	// ===================================================================================
	// SMALL PACKAGE CALCULATION
	// ===================================================================================
	if (empty ( $small_package_rates )) {
		$small_package = 'N/A';
	} else {
		if ($new_origin != 'MIA') {
			if ($chargeable_weight <= 50) {
				// $small_package = number_format((($base_rate_small_package + ($additional_rate_small_package * $chargeable_weight) + $markup_small_package)+ $domestic_cost_smallpkg),2,'.','');
				if ($cntHazardous > 0) {
					$small_package = number_format ( ((($base_rate_small_package + ($additional_rate_small_package * $chargeable_weight) + $markup_small_package) * 1.5) + (75.00 * $cntHazardous)), 2, '.', '' );
				} else {
					$small_package = number_format ( ($base_rate_small_package + ($additional_rate_small_package * $chargeable_weight) + $markup_small_package), 2, '.', '' );
				}
				$small_package = number_format ( ($small_package + $domestic_cost_smallpkg + $smallpkg_commodity_cost), 2, '.', '' );
			} else {
				$small_package = 'N/A';
			}
		} else {
			$small_package = 'N/A';
		}
	}
	// ===================================================================================
	
	// ===================================================================================
	// EXPRESS CALCULATION
	// ===================================================================================
	if ($chargeable_weight > 40) {
		$express_freight = 'N/A';
	} else {
		
		if (empty ( $express_letter_rates )) {
			$emptyarrays = true;
			$express_freight_letter = 0;
		} else {
			$emptyarrays = false;
			if ($letterhazardItems > 0) {
				$express_freight_letter = number_format ( (((($base_rate_express_letter + $markup_express_letter) * $letterQty) * 1.5) + (75.00 * letterhazardItems)), 2, '.', '' );
			} else {
				$express_freight_letter = number_format ( (($base_rate_express_letter + $markup_express_letter) * $letterQty), 2, '.', '' );
			}
		}
		
		if ($documentQty > 5) {
			$express_freight_document = 0;
		} else {
			if (empty ( $express_document_rates )) {
				$express_freight_document = 0;
			} else {
				if ($documenthazardItems > 0) {
					$express_freight_document = number_format ( ((($base_rate_express_document + $markup_express_document) * 1.5) + (75.00 * $documenthazardItems)), 2, '.', '' );
				} else {
					$express_freight_document = number_format ( ($base_rate_express_document + $markup_express_document), 2, '.', '' );
				}
			}
		}
		
		if (empty ( $express_rates )) {
			$express_freight_nonletterORdocument = 0;
		} else {
			if ($nonletterORdocumenthazardItems > 0) {
				$express_freight_nonletterORdocument = number_format ( ((($base_rate_express + $markup_express) * 1.5) + (75.00 * $nonletterORdocumenthazardItems)), 2, '.', '' );
			} else {
				$express_freight_nonletterORdocument = number_format ( (($base_rate_express + $markup_express)), 2, '.', '' );
			}
		}
		
		$express_freight_total = number_format ( ($express_freight_letter + $express_freight_document + $express_freight_nonletterORdocument), 2, '.', '' );
		if ($express_freight_total == '0.00') {
			$express_freight = 'N/A';
		} else {
			$express_freight = $express_freight_total;
		}
		
		if ($warehouse_zip == 'No') {
			$express_freight = number_format ( ($express_freight + $base_rate_domestic_express + $express_commodity_cost), 2, '.', '' );
		} else {
			$express_freight = number_format ( ($express_freight + $express_commodity_cost), 2, '.', '' );
		}
	}
	// ===================================================================================
	
	// ===================================================================================
	// DOMESTIC
	// ===================================================================================
	// if($pickupCountry == "United States" || $pickupcountry == "Miami US Warehouse" || $pickupcountry == "Orlando US Warehouse" || $pickupcountry == "Brooklyn NY USA" || $pickupcountry == "Boston US Warehouse" || $pickupcountry == "Huston US Warehouse" || $pickupcountry == "Atlanta US Warehouse" || $pickupcountry == "Pooleville MD USA" || $pickupcountry == "Philadelphia US Warehouse" || $pickupcountry == "Kearny NJ USA"){
	
	if ($deliveryCountry == "United States") {
		if ($cntHazardous > 0) {
			$domestic_freight = number_format ( (($us_domestic_cost * 1.5) + (75.00 * $cntHazardous) + $us_domestic_commodity_cost), 2, '.', '' );
			$domestic_expedited = number_format ( ((($us_domestic_cost + ($us_domestic_cost * 0.2)) * 1.5) + (75.00 * $cntHazardous) + $us_domestic_commodity_cost), 2, '.', '' );
		} else {
			$domestic_freight = number_format ( ($us_domestic_cost + $us_domestic_commodity_cost), 2, '.', '' );
			$domestic_expedited = number_format ( ($us_domestic_cost + ($us_domestic_cost * 0.2) + $us_domestic_commodity_cost), 2, '.', '' );
		}
	} else {
		$domestic_freight = 'N/A';
		$domestic_expedited = 'N/A';
	}
	/*
	 * }else{
	 * $domestic_freight = 'N/A';
	 * }
	 */
	
	// ===================================================================================
	// SMALL PACKAGE/MAILBOX OUTPUT
	// ===================================================================================
	/*
	 * if($small_package == 'N/A'){
	 * if($us_mailbox_freight == 'N/A'){
	 * $smallpkg_mailbox_freight = 'N/A';
	 * }else{
	 * $smallpkg_mailbox_freight = $us_mailbox_freight;
	 * }
	 * }else{
	 * $smallpkg_mailbox_freight = $small_package;
	 * }
	 */
	
	// ===================================================================================
	// CARIBBEAN EXPRESS CALCULATION
	// ===================================================================================
	if ($chargeable_weight > 50) {
		$caribbean_same_day_express_freight = 'N/A';
		$caribbean_two_day_express_freight = 'N/A';
	} else {
		
		if (empty ( $caribbean_same_day_express_letter_rates )) {
			$emptyarrays = true;
			$caribbean_same_day_express_freight_letter = 0;
		} else {
			$emptyarrays = false;
			if ($letterhazardItems > 0) {
				$caribbean_same_day_express_freight_letter = number_format ( (((($base_rate_caribbean_same_day_express_letter + $markup_caribbean_same_day_express_letter) * $letterQty) * 1.5) + (75.00 * letterhazardItems)), 2, '.', '' );
			} else {
				$caribbean_same_day_express_freight_letter = number_format ( (($base_rate_caribbean_same_day_express_letter + $markup_caribbean_same_day_express_letter) * $letterQty), 2, '.', '' );
			}
		}
		
		if (empty ( $caribbean_two_day_express_letter_rates )) {
			$emptyarray2 = true;
			$caribbean_two_day_express_letter_rates = 0;
		} else {
			$emptyarray2 = false;
			if ($letterhazardItems > 0) {
				$caribbean_two_day_express_letter_rates = number_format ( (((($base_rate_caribbean_two_day_express_letter + $markup_caribbean_two_day_express_letter) * $letterQty) * 1.5) + (75.00 * letterhazardItems)), 2, '.', '' );
			} else {
				$caribbean_two_day_express_letter_rates = number_format ( (($base_rate_caribbean_two_day_express_letter + $markup_caribbean_two_day_express_letter) * $letterQty), 2, '.', '' );
			}
		}
		
		if ($documentQty > 5) {
			$caribbean_same_day_express_freight_document = 0;
			$caribbean_two_day_express_freight_document = 0;
		} else {
			if (empty ( $caribbean_same_day_express_document_rates )) {
				$caribbean_same_day_express_freight_document = 0;
			} else {
				if ($documenthazardItems > 0) {
					$caribbean_same_day_express_freight_document = number_format ( ((($base_rate_caribbean_same_day_express_document + $markup_caribbean_same_day_express_document) * 1.5) + (75.00 * $documenthazardItems)), 2, '.', '' );
				} else {
					$caribbean_same_day_express_freight_document = number_format ( ($base_rate_caribbean_same_day_express_document + $markup_caribbean_same_day_express_document), 2, '.', '' );
				}
			}
			
			if (empty ( $caribbean_two_day_express_document_rates )) {
				$caribbean_two_day_express_freight_document = 0;
			} else {
				if ($documenthazardItems > 0) {
					$caribbean_two_day_express_freight_document = number_format ( ((($base_rate_caribbean_two_day_express_document + $markup_caribbean_two_day_express_document) * 1.5) + (75.00 * $documenthazardItems)), 2, '.', '' );
				} else {
					$caribbean_two_day_express_freight_document = number_format ( ($base_rate_caribbean_two_day_express_document + $markup_caribbean_two_day_express_document), 2, '.', '' );
				}
			}
		}
		
		if (empty ( $caribbean_same_day_express_rates )) {
			$caribbean_same_day_express_freight_nonletterORdocument = 0;
		} else {
			if ($nonletterORdocumenthazardItems > 0) {
				$caribbean_same_day_express_freight_nonletterORdocument = number_format ( ((($base_rate_caribbean_same_day_express + $markup_caribbean_same_day_express) * 1.5) + (75.00 * $nonletterORdocumenthazardItems)), 2, '.', '' );
			} else {
				$caribbean_same_day_express_freight_nonletterORdocument = number_format ( (($base_rate_caribbean_same_day_express + $markup_caribbean_same_day_express)), 2, '.', '' );
			}
		}
		
		if (empty ( $caribbean_two_day_express_rates )) {
			$caribbean_two_day_express_freight_nonletterORdocument = 0;
		} else {
			if ($nonletterORdocumenthazardItems > 0) {
				$caribbean_two_day_express_freight_nonletterORdocument = number_format ( ((($base_rate_caribbean_two_day_express + $markup_caribbean_two_day_express) * 1.5) + (75.00 * $nonletterORdocumenthazardItems)), 2, '.', '' );
			} else {
				$caribbean_two_day_express_freight_nonletterORdocument = number_format ( (($base_rate_caribbean_two_day_express + $markup_caribbean_two_day_express)), 2, '.', '' );
			}
		}
		
		$caribbean_same_day_express_freight_total = number_format ( ($caribbean_same_day_express_freight_letter + $caribbean_same_day_express_freight_document + $caribbean_same_day_express_freight_nonletterORdocument), 2, '.', '' );
		if ($caribbean_same_day_express_freight_total == '0.00') {
			$caribbean_same_day_express_freight = 'N/A';
		} else {
			$caribbean_same_day_express_freight = $caribbean_same_day_express_freight_total;
		}
		
		$caribbean_two_day_express_freight_total = number_format ( ($caribbean_two_day_express_freight_letter + $caribbean_two_day_express_freight_document + $caribbean_two_day_express_freight_nonletterORdocument), 2, '.', '' );
		if ($caribbean_two_day_express_freight_total == '0.00') {
			$caribbean_two_day_express_freight = 'N/A';
		} else {
			$caribbean_two_day_express_freight = $caribbean_two_day_express_freight_total;
		}
	}
	
	// ===================================================================================
	// TRANSIT TIMES & DAYS
	// ===================================================================================
	if ($express_freight == 'N/A') {
		$express_transit_time = 'N/A';
		$express_depart_days = 'N/A';
	} else {
		// if($new_destination == 'MIA'){
		if ($new_origin == 'MIA') {
			if ($deliveryCountry == 'United States') {
				$express_transit_time = 'N/A';
				$express_depart_days = 'N/A';
			} else {
				$express_transit_time = 'Overnight to 2';
				$express_depart_days = 'Daily/Mon-Fri';
			}
		} else {
			$express_transit_time = 'N/A';
			$express_depart_days = 'N/A';
		}
	}
	
	if ($caribbean_same_day_express_freight == 'N/A') {
		$caribbean_same_day_express_transit_time = 'N/A day(s)';
		$caribbean_same_day_express_depart_days = 'N/A';
	} else {
		if ($new_origin == $deliveryCountry) {
			$caribbean_same_day_express_transit_time = 'N/A day(s)';
			$caribbean_same_day_express_depart_days = 'N/A';
		} else {
			$caribbean_same_day_express_transit_time = 'Same day';
			$caribbean_same_day_express_depart_days = 'Daily/Mon-Fri';
		}
	}
	
	if ($caribbean_two_day_express_freight == 'N/A') {
		$caribbean_two_day_express_transit_time = 'N/A';
		$caribbean_two_day_express_depart_days = 'N/A';
	} else {
		if ($new_origin == $deliveryCountry) {
			$caribbean_two_day_express_transit_time = 'N/A';
			$caribbean_two_day_express_depart_days = 'N/A';
		} else {
			$caribbean_two_day_express_transit_time = '2';
			$caribbean_two_day_express_depart_days = 'Daily/Mon-Fri';
		}
	}
	
	if ($domestic_freight == 'N/A') {
		$domestic_transit_time = 'N/A';
		
		$domestic_expedited_transit_time = 'N/A';
		
		$domestic_depart_days = 'N/A';
	} else {
		if (! in_array ( $pickupCountry, array (
				"United States",
				"Miami US Warehouse",
				"Orlando US Warehouse",
				"Brooklyn NY USA",
				"Boston US Warehouse",
				"Huston US Warehouse",
				"Atlanta US Warehouse",
				"Pooleville MD USA",
				"Philadelphia US Warehouse",
				"Kearny NJ USA" 
		) ) && $deliveryCountry == "United States") {
			$domestic_transit_time = 'N/A';
			
			$domestic_expedited_transit_time = 'N/A';
			
			$domestic_depart_days = 'N/A';
		} else {
			$domestic_transit_time = '3 - 6';
			
			$domestic_expedited_transit_time = 'Overnight to 3';
			
			$domestic_depart_days = 'Daily/Mon-Fri';
		}
	}
	// ===================================================================================
	
	// ===================================================================================
	// PICKUP REQUEST CALCULATION CHARGES
	// ===================================================================================
	$array_length_pickup_services = count ( $pickupservices );
	for($i = 0; $i < $array_length_pickup_services; $i ++) {
		$pickup_request = $pickupservices [$i];
		if ($pickup_request == 'Fork Lift') {
			$pickup_cost_by_chargeableweight_forklift = number_format ( ($chargeable_weight * 0.10), 2, '.', '' );
			$pickup_cost_by_expresschargeableweight_forklift = number_format ( ($express_chargeable_weight * 0.10), 2, '.', '' );
			$pickup_cost_by_domestic_forklift = number_format ( ($actual_weight * 0.10), 2, '.', '' );
			if ($pickup_cost_by_chargeableweight_forklift > 25) {
				$pickup_forklift_cost = $pickup_cost_by_chargeableweight_forklift;
			} else {
				$pickup_forklift_cost = number_format ( (25), 2, '.', '' );
			}
			if ($pickup_cost_by_expresschargeableweight_forklift > 25) {
				$pickup_forklift_cost_express = $pickup_cost_by_expresschargeableweight_forklift;
			} else {
				$pickup_forklift_cost_express = number_format ( (25), 2, '.', '' );
			}
			if ($pickup_cost_by_domestic_forklift > 25) {
				$pickup_forklift_cost_domestic = $pickup_cost_by_domestic_forklift;
			} else {
				$pickup_forklift_cost_domestic = number_format ( (25), 2, '.', '' );
			}
		}
		
		if ($pickup_request == 'Lift Gate') {
			$pickup_cost_by_chargeableweight_liftgate = number_format ( ($chargeable_weight * 0.10), 2, '.', '' );
			$pickup_cost_by_expresschargeableweight_liftgate = number_format ( ($express_chargeable_weight * 0.10), 2, '.', '' );
			$pickup_cost_by_domestic_liftgate = number_format ( ($actual_weight * 0.10), 2, '.', '' );
			if ($pickup_cost_by_chargeableweight_liftgate > 25) {
				$pickup_liftgate_cost = $pickup_cost_by_chargeableweight_liftgate;
			} else {
				$pickup_liftgate_cost = number_format ( (25), 2, '.', '' );
			}
			if ($pickup_cost_by_expresschargeableweight_liftgate > 25) {
				$pickup_liftgate_cost_express = $pickup_cost_by_expresschargeableweight_liftgate;
			} else {
				$pickup_liftgate_cost_express = number_format ( (25), 2, '.', '' );
			}
			if ($pickup_cost_by_domestic_liftgate > 25) {
				$pickup_liftgate_cost_domestic = $pickup_cost_by_domestic_liftgate;
			} else {
				$pickup_liftgate_cost_domestic = number_format ( (25), 2, '.', '' );
			}
		}
		
		if ($pickup_request == 'Perishable') {
			if ($barrelItems > 0) {
				$pickup_perishable_barrel_cost_ocean = number_format ( (((($base_rate_ocean_barrel + $markup_ocean_barrel) + ((($base_rate_ocean_barrel + $markup_ocean_barrel) * 0.8) * ($barrelQty - 1))) * 0.5) + (75.00 * $barrelItems)), 2, '.', '' );
			} else {
				$pickup_perishable_barrel_cost_ocean = 0;
			}
			
			if ($eContainerItems > 0) {
				$pickup_perishable_econtainer_cost_ocean = number_format ( ((((($new_cubic_feet_econtainer * $additional_rate_econtainer) + $markup_ocean_econtainer)) * 0.5) + (75.00 * $eContainerItems)), 2, '.', '' );
			} else {
				$pickup_perishable_econtainer_cost_ocean = 0;
			}
			
			if ($ehcontainerItems > 0) {
				$pickup_perishable_ehcontainer_cost_ocean = number_format ( ((($base_rate_ocean_ehcontainer * $ehcontainerQty) * 0.5) + (75.00 * $ehcontainerItems)), 2, '.', '' );
			} else {
				$pickup_perishable_ehcontainer_cost_ocean = 0;
			}
			
			if ($nonBarrelOrContainerItems > 0) {
				$pickup_perishable_nonbarrelORcontainer_cost_ocean = number_format ( ((($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean)) * 0.5) + (75.00 * $nonBarrelOrContainerItems)), 2, '.', '' );
			} else {
				$pickup_perishable_nonbarrelORcontainer_cost_ocean = 0;
			}
			
			if ($letterItems > 0) {
				$pickup_perishable_letter_cost_express = number_format ( (((($base_rate_express_letter + $markup_express_letter) * $letterQty) * 0.5) + (75.00 * $letterItems)), 2, '.', '' );
			} else {
				$pickup_perishable_letter_cost_express = 0;
			}
			
			if ($documentItems > 0) {
				$pickup_perishable_document_cost_express = number_format ( ((($base_rate_express_document + $markup_express_document) * 0.5) + (75.00 * $documentItems)), 2, '.', '' );
			} else {
				$pickup_perishable_document_cost_express = 0;
			}
			
			if ($nonletterORdocumentItems > 0) {
				$pickup_perishable_nonletterORdocument_cost_express = number_format ( ((($base_rate_express + $markup_express) * 0.5) + (75.00 * $nonletterORdocumentItems)), 2, '.', '' );
			} else {
				$pickup_perishable_nonletterORdocument_cost_express = 0;
			}
			
			$air_cargo_by_lb = ($additional_rate_air + $fsc_air) * $chargeable_weight;
			if ($air_cargo_by_lb >= $base_rate_air) {
				$air_cost = $air_cargo_by_lb;
			} else {
				$air_cost = $base_rate_air;
			}
			
			$pickup_perishable_total_cost_ocean = number_format ( ($pickup_perishable_barrel_cost_ocean + $pickup_perishable_econtainer_cost_ocean + $pickup_perishable_ehcontainer_cost_ocean + $pickup_perishable_nonbarrelORcontainer_cost_ocean), 2, '.', '' );
			
			$pickup_perishable_total_cost_express = number_format ( ($pickup_perishable_letter_cost_express + $pickup_perishable_document_cost_express + $pickup_perishable_nonletterORdocument_cost_express), 2, '.', '' );
			
			$pickup_perishable_total_cost_mailbox = number_format ( (($us_mailbox_freight * 0.5) + (75.00 * $numberofpieces)), 2, '.', '' );
			
			$pickup_perishable_total_cost_air = number_format ( ((($air_cost * 0.5) + (75.00 * $numberofpieces))), 2, '.', '' );
			
			$pickup_perishable_total_cost_smallpackage = number_format ( ((($base_rate_small_package + $markup_small_package) * 0.5) + (75.00 * $numberofpieces)), 2, '.', '' );
			
			$pickup_perishable_total_cost_domestic = number_format ( (($domestic_cost_air_ocean * 0.5) + (75.00 * $numberofpieces)), 2, '.', '' );
		}
		
		if ($pickup_request == 'Keep Frozen') {
			if ($barrelItems > 0) {
				$pickup_frozencargo_barrel_cost_ocean = number_format ( (((($base_rate_ocean_barrel + $markup_ocean_barrel) + ((($base_rate_ocean_barrel + $markup_ocean_barrel) * 0.8) * ($barrelQty - 1))) * 0.5) + (75.00 * $barrelItems)), 2, '.', '' );
			} else {
				$pickup_frozencargo_barrel_cost_ocean = 0;
			}
			
			if ($eContainerItems > 0) {
				$pickup_frozencargo_econtainer_cost_ocean = number_format ( ((((($new_cubic_feet_econtainer * $additional_rate_econtainer) + $markup_ocean_econtainer)) * 0.5) + (75.00 * $eContainerItems)), 2, '.', '' );
			} else {
				$pickup_frozencargo_econtainer_cost_ocean = 0;
			}
			
			if ($ehcontainerItems > 0) {
				$pickup_frozencargo_ehcontainer_cost_ocean = number_format ( ((($base_rate_ocean_ehcontainer * $ehcontainerQty) * 0.5) + (75.00 * $ehcontainerItems)), 2, '.', '' );
			} else {
				$pickup_frozencargo_ehcontainer_cost_ocean = 0;
			}
			
			if ($nonBarrelOrContainerItems > 0) {
				$pickup_frozencargo_nonbarrelORcontainer_cost_ocean = number_format ( ((($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean)) * 0.5) + (75.00 * $nonBarrelOrContainerItems)), 2, '.', '' );
			} else {
				$pickup_frozencargo_nonbarrelORcontainer_cost_ocean = 0;
			}
			
			if ($letterItems > 0) {
				$pickup_frozencargo_letter_cost_express = number_format ( (((($base_rate_express_letter + $markup_express_letter) * $letterQty) * 0.5) + (75.00 * $letterItems)), 2, '.', '' );
			} else {
				$pickup_frozencargo_letter_cost_express = 0;
			}
			
			if ($documentItems > 0) {
				$pickup_frozencargo_document_cost_express = number_format ( ((($base_rate_express_document + $markup_express_document) * 0.5) + (75.00 * $documentItems)), 2, '.', '' );
			} else {
				$pickup_frozencargo_document_cost_express = 0;
			}
			
			if ($nonletterORdocumentItems > 0) {
				$pickup_frozencargo_nonletterORdocument_cost_express = number_format ( ((($base_rate_express + $markup_express) * 0.5) + (75.00 * $nonletterORdocumentItems)), 2, '.', '' );
			} else {
				$pickup_frozencargo_nonletterORdocument_cost_express = 0;
			}
			
			$air_cargo_by_lb = ($additional_rate_air + $fsc_air) * $chargeable_weight;
			if ($air_cargo_by_lb >= $base_rate_air) {
				$air_cost = $air_cargo_by_lb;
			} else {
				$air_cost = $base_rate_air;
			}
			
			$pickup_frozencargo_total_cost_ocean = number_format ( ($pickup_frozencargo_barrel_cost_ocean + $pickup_frozencargo_econtainer_cost_ocean + $pickup_frozencargo_ehcontainer_cost_ocean + $pickup_frozencargo_nonbarrelORcontainer_cost_ocean), 2, '.', '' );
			
			$pickup_frozencargo_total_cost_express = number_format ( ($pickup_frozencargo_letter_cost_express + $pickup_frozencargo_document_cost_express + $pickup_frozencargo_nonletterORdocument_cost_express), 2, '.', '' );
			
			$pickup_frozencargo_total_cost_mailbox = number_format ( (($us_mailbox_freight * 0.5) + (75.00 * $numberofpieces)), 2, '.', '' );
			
			$pickup_frozencargo_total_cost_air = number_format ( ((($air_cost * 0.5) + (75.00 * $numberofpieces))), 2, '.', '' );
			
			$pickup_frozencargo_total_cost_smallpackage = number_format ( ((($base_rate_small_package + $markup_small_package) * 0.5) + (75.00 * $numberofpieces)), 2, '.', '' );
			
			$pickup_frozencargo_total_cost_domestic = number_format ( (($domestic_cost_air_ocean * 0.5) + (75.00 * $numberofpieces)), 2, '.', '' );
		}
		
		if ($pickup_request == 'Inside Pickup') {
			if ($barrelItems > 0) {
				$pickup_insidepickup_cost_barrel = number_format ( (5 * $barrelQty), 2, '.', '' );
			} else {
				$pickup_insidepickup_cost_barrel = 0;
			}
			
			if ($econtainerItems > 0) {
				$pickup_insidepickup_cost_econtainer = number_format ( (5 * $econtainerQty), 2, '.', '' );
			} else {
				$pickup_insidepickup_cost_econtainer = 0;
			}
			
			if ($ehcontainerItems > 0) {
				$pickup_insidepickup_cost_ehcontainer = number_format ( (5 * $ehcontainerQty), 2, '.', '' );
			} else {
				$pickup_insidepickup_cost_ehcontainer = 0;
			}
			
			if ($nonBarrelOrContainerItems > 0) {
				$pickup_insidepickup_cost_nonbarrelORcontainer = number_format ( (0.05 * $nonbarrelORcontainer_ChargeableWeight), 2, '.', '' );
				$pickup_insidepickup_cost_nonbarrelORcontainer_domestic = number_format ( (0.05 * $nonBarrelOrContainerWeight), 2, '.', '' );
			} else {
				$pickup_insidepickup_cost_nonbarrelORcontainer = 0;
				$pickup_insidepickup_cost_nonbarrelORcontainer_domestic = 0;
			}
			
			$pickup_insidepickup_total_cost = number_format ( ($pickup_insidepickup_cost_barrel + $pickup_insidepickup_cost_econtainer + $pickup_insidepickup_cost_ehcontainer + $pickup_insidepickup_cost_nonbarrelORcontainer), 2, '.', '' );
			$pickup_insidepickup_total_cost_domestic = number_format ( ($pickup_insidepickup_cost_barrel + $pickup_insidepickup_cost_econtainer + $pickup_insidepickup_cost_ehcontainer + $pickup_insidepickup_cost_nonbarrelORcontainer_domestic), 2, '.', '' );
		}
		
		if ($pickup_request == 'Stairs') {
			if ($barrelItems > 0) {
				$pickup_stairs_cost_barrel = number_format ( (5 * $barrelQty), 2, '.', '' );
			} else {
				$pickup_stairs_cost_barrel = 0;
			}
			
			if ($econtainerItems > 0) {
				$pickup_stairs_cost_econtainer = number_format ( (5 * $econtainerQty), 2, '.', '' );
			} else {
				$pickup_stairs_cost_econtainer = 0;
			}
			
			if ($ehcontainerItems > 0) {
				$pickup_stairs_cost_ehcontainer = number_format ( (5 * $ehcontainerQty), 2, '.', '' );
			} else {
				$pickup_stairs_cost_ehcontainer = 0;
			}
			
			if ($nonBarrelOrContainerItems > 0) {
				$pickup_stairs_cost_nonbarrelORcontainer = number_format ( (0.05 * $nonbarrelORcontainer_ChargeableWeight), 2, '.', '' );
				$pickup_stairs_cost_nonbarrelORcontainer_domestic = number_format ( (0.05 * $nonBarrelOrContainerWeight), 2, '.', '' );
			} else {
				$pickup_stairs_cost_nonbarrelORcontainer = 0;
				$pickup_stairs_cost_nonbarrelORcontainer_domestic = 0;
			}
			
			$pickup_stairs_total_cost = number_format ( ($pickup_stairs_cost_barrel + $pickup_stairs_cost_econtainer + $pickup_stairs_cost_ehcontainer + $pickup_stairs_cost_nonbarrelORcontainer), 2, '.', '' );
			$pickup_stairs_total_cost_domestic = number_format ( ($pickup_stairs_cost_barrel + $pickup_stairs_cost_econtainer + $pickup_stairs_cost_ehcontainer + $pickup_stairs_cost_nonbarrelORcontainer_domestic), 2, '.', '' );
		}
		$pickup_request_cost_air = $pickup_forklift_cost + $pickup_liftgate_cost + $pickup_perishable_total_cost_air + $pickup_frozencargo_total_cost_air + $pickup_insidepickup_total_cost + $pickup_stairs_total_cost;
		$pickup_request_cost_ocean = $pickup_forklift_cost + $pickup_liftgate_cost + $pickup_perishable_total_cost_ocean + $pickup_frozencargo_total_cost_ocean + $pickup_insidepickup_total_cost + $pickup_stairs_total_cost;
		$pickup_request_cost_smallpackage = $pickup_forklift_cost + $pickup_liftgate_cost + $pickup_perishable_total_cost_smallpackage + $pickup_frozencargo_total_cost_smallpackage + $pickup_insidepickup_total_cost + $pickup_stairs_total_cost;
		$pickup_request_cost_mailbox = $pickup_forklift_cost + $pickup_liftgate_cost + $pickup_perishable_total_cost_mailbox + $pickup_frozencargo_total_cost_mailbox + $pickup_insidepickup_total_cost + $pickup_stairs_total_cost;
		$pickup_request_cost_express = $pickup_forklift_cost_express + $pickup_liftgate_cost_express + $pickup_perishable_total_cost_express + $pickup_frozencargo_total_cost_express + $pickup_insidepickup_total_cost + $pickup_stairs_total_cost;
		$pickup_request_cost_domestic = $pickup_forklift_cost_domestic + $pickup_liftgate_cost_domestic + $pickup_perishable_total_cost_domestic + $pickup_frozencargo_total_cost_domestic + $pickup_insidepickup_total_cost_domestic + $pickup_stairs_total_cost_domestic;
	}
	// ===================================================================================
	
	// ===================================================================================
	// DELIVERY REQUEST CALCULATION CHARGES
	// ===================================================================================
	$array_length_delivery_services = count ( $deliveryservices );
	for($i = 0; $i < $array_length_delivery_services; $i ++) {
		$delivery_request = $deliveryservices [$i];
		if ($delivery_request == 'Fork Lift') {
			$delivery_cost_by_chargeableweight_forklift = number_format ( ($chargeable_weight * 0.10), 2, '.', '' );
			$delivery_cost_by_expresschargeableweight_forklift = number_format ( ($express_chargeable_weight * 0.10), 2, '.', '' );
			$delivery_cost_by_domestic_forklift = number_format ( ($actual_weight * 0.10), 2, '.', '' );
			if ($delivery_cost_by_chargeableweight_forklift > 25) {
				$delivery_forklift_cost = $delivery_cost_by_chargeableweight_forklift;
			} else {
				$delivery_forklift_cost = number_format ( (25), 2, '.', '' );
			}
			if ($delivery_cost_by_expresschargeableweight_forklift > 25) {
				$delivery_forklift_cost_express = $delivery_cost_by_expresschargeableweight_forklift;
			} else {
				$delivery_forklift_cost_express = number_format ( (25), 2, '.', '' );
			}
			if ($delivery_cost_by_domestic_forklift > 25) {
				$delivery_forklift_cost_domestic = $delivery_cost_by_domestic_forklift;
			} else {
				$delivery_forklift_cost_domestic = number_format ( (25), 2, '.', '' );
			}
		}
		
		if ($delivery_request == 'Lift Gate') {
			$delivery_cost_by_chargeableweight_liftgate = number_format ( ($chargeable_weight * 0.10), 2, '.', '' );
			$delivery_cost_by_expresschargeableweight_liftgate = number_format ( ($express_chargeable_weight * 0.10), 2, '.', '' );
			$delivery_cost_by_domestic_liftgate = number_format ( ($actual_weight * 0.10), 2, '.', '' );
			if ($delivery_cost_by_chargeableweight_liftgate > 25) {
				$delivery_liftgate_cost = $delivery_cost_by_chargeableweight_liftgate;
			} else {
				$delivery_liftgate_cost = number_format ( (25), 2, '.', '' );
			}
			if ($delivery_cost_by_expresschargeableweight_liftgate > 25) {
				$delivery_liftgate_cost_express = $delivery_cost_by_expresschargeableweight_liftgate;
			} else {
				$delivery_liftgate_cost_express = number_format ( (25), 2, '.', '' );
			}
			if ($delivery_cost_by_domestic_liftgate > 25) {
				$delivery_liftgate_cost_domestic = $delivery_cost_by_domestic_liftgate;
			} else {
				$delivery_liftgate_cost_domestic = number_format ( (25), 2, '.', '' );
			}
		}
		
		if ($delivery_request == 'Perishable') {
			if ($barrelItems > 0) {
				$delivery_perishable_barrel_cost_ocean = number_format ( (((($base_rate_ocean_barrel + $markup_ocean_barrel) + ((($base_rate_ocean_barrel + $markup_ocean_barrel) * 0.8) * ($barrelQty - 1))) * 0.5) + (75.00 * $barrelItems)), 2, '.', '' );
			} else {
				$delivery_perishable_barrel_cost_ocean = 0;
			}
			
			if ($eContainerItems > 0) {
				$delivery_perishable_econtainer_cost_ocean = number_format ( ((((($new_cubic_feet_econtainer * $additional_rate_econtainer) + $markup_ocean_econtainer)) * 0.5) + (75.00 * $eContainerItems)), 2, '.', '' );
			} else {
				$delivery_perishable_econtainer_cost_ocean = 0;
			}
			
			if ($ehcontainerItems > 0) {
				$delivery_perishable_ehcontainer_cost_ocean = number_format ( ((($base_rate_ocean_ehcontainer * $ehcontainerQty) * 0.5) + (75.00 * $ehcontainerItems)), 2, '.', '' );
			} else {
				$delivery_perishable_ehcontainer_cost_ocean = 0;
			}
			
			if ($nonBarrelOrContainerItems > 0) {
				$delivery_perishable_nonbarrelORcontainer_cost_ocean = number_format ( ((($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean)) * 0.5) + (75.00 * $nonBarrelOrContainerItems)), 2, '.', '' );
			} else {
				$delivery_perishable_nonbarrelORcontainer_cost_ocean = 0;
			}
			
			if ($letterItems > 0) {
				$delivery_perishable_letter_cost_express = number_format ( (((($base_rate_express_letter + $markup_express_letter) * $letterQty) * 0.5) + (75.00 * $letterItems)), 2, '.', '' );
			} else {
				$delivery_perishable_letter_cost_express = 0;
			}
			
			if ($documentItems > 0) {
				$delivery_perishable_document_cost_express = number_format ( ((($base_rate_express_document + $markup_express_document) * 0.5) + (75.00 * $documentItems)), 2, '.', '' );
			} else {
				$delivery_perishable_document_cost_express = 0;
			}
			
			if ($nonletterORdocumentItems > 0) {
				$delivery_perishable_nonletterORdocument_cost_express = number_format ( ((($base_rate_express + $markup_express) * 0.5) + (75.00 * $nonletterORdocumentItems)), 2, '.', '' );
			} else {
				$delivery_perishable_nonletterORdocument_cost_express = 0;
			}
			
			$air_cargo_by_lb = ($additional_rate_air + $fsc_air) * $chargeable_weight;
			if ($air_cargo_by_lb >= $base_rate_air) {
				$air_cost = $air_cargo_by_lb;
			} else {
				$air_cost = $base_rate_air;
			}
			
			$delivery_perishable_total_cost_ocean = number_format ( ($delivery_perishable_barrel_cost_ocean + $delivery_perishable_econtainer_cost_ocean + $delivery_perishable_ehcontainer_cost_ocean + $delivery_perishable_nonbarrelORcontainer_cost_ocean), 2, '.', '' );
			
			$delivery_perishable_total_cost_express = number_format ( ($delivery_perishable_letter_cost_express + $delivery_perishable_document_cost_express + $delivery_perishable_nonletterORdocument_cost_express), 2, '.', '' );
			
			$delivery_perishable_total_cost_mailbox = number_format ( (($us_mailbox_freight * 0.5) + (75.00 * $numberofpieces)), 2, '.', '' );
			
			$delivery_perishable_total_cost_air = number_format ( ((($air_cost * 0.5) + (75.00 * $numberofpieces))), 2, '.', '' );
			
			$delivery_perishable_total_cost_smallpackage = number_format ( ((($base_rate_small_package + $markup_small_package) * 0.5) + (75.00 * $numberofpieces)), 2, '.', '' );
			
			$delivery_perishable_total_cost_domestic = number_format ( (($domestic_cost_air_ocean * 0.5) + (75.00 * $numberofpieces)), 2, '.', '' );
		}
		
		if ($delivery_request == 'Keep Frozen') {
			if ($barrelItems > 0) {
				$delivery_frozencargo_barrel_cost_ocean = number_format ( (((($base_rate_ocean_barrel + $markup_ocean_barrel) + ((($base_rate_ocean_barrel + $markup_ocean_barrel) * 0.8) * ($barrelQty - 1))) * 0.5) + (75.00 * $barrelItems)), 2, '.', '' );
			} else {
				$delivery_frozencargo_barrel_cost_ocean = 0;
			}
			
			if ($eContainerItems > 0) {
				$delivery_frozencargo_econtainer_cost_ocean = number_format ( ((((($new_cubic_feet_econtainer * $additional_rate_econtainer) + $markup_ocean_econtainer)) * 0.5) + (75.00 * $eContainerItems)), 2, '.', '' );
			} else {
				$delivery_frozencargo_econtainer_cost_ocean = 0;
			}
			
			if ($ehcontainerItems > 0) {
				$delivery_frozencargo_ehcontainer_cost_ocean = number_format ( ((($base_rate_ocean_ehcontainer * $ehcontainerQty) * 0.5) + (75.00 * $ehcontainerItems)), 2, '.', '' );
			} else {
				$delivery_frozencargo_ehcontainer_cost_ocean = 0;
			}
			
			if ($nonBarrelOrContainerItems > 0) {
				$delivery_frozencargo_nonbarrelORcontainer_cost_ocean = number_format ( ((($base_rate_ocean + ($new_cubic_feet * $additional_rate_ocean)) * 0.5) + (75.00 * $nonBarrelOrContainerItems)), 2, '.', '' );
			} else {
				$delivery_frozencargo_nonbarrelORcontainer_cost_ocean = 0;
			}
			
			if ($letterItems > 0) {
				$delivery_frozencargo_letter_cost_express = number_format ( (((($base_rate_express_letter + $markup_express_letter) * $letterQty) * 0.5) + (75.00 * $letterItems)), 2, '.', '' );
			} else {
				$delivery_frozencargo_letter_cost_express = 0;
			}
			
			if ($documentItems > 0) {
				$delivery_frozencargo_document_cost_express = number_format ( ((($base_rate_express_document + $markup_express_document) * 0.5) + (75.00 * $documentItems)), 2, '.', '' );
			} else {
				$delivery_frozencargo_document_cost_express = 0;
			}
			
			if ($nonletterORdocumentItems > 0) {
				$delivery_frozencargo_nonletterORdocument_cost_express = number_format ( ((($base_rate_express + $markup_express) * 0.5) + (75.00 * $nonletterORdocumentItems)), 2, '.', '' );
			} else {
				$delivery_frozencargo_nonletterORdocument_cost_express = 0;
			}
			
			$air_cargo_by_lb = ($additional_rate_air + $fsc_air) * $chargeable_weight;
			if ($air_cargo_by_lb >= $base_rate_air) {
				$air_cost = $air_cargo_by_lb;
			} else {
				$air_cost = $base_rate_air;
			}
			
			$delivery_frozencargo_total_cost_ocean = number_format ( ($delivery_frozencargo_barrel_cost_ocean + $delivery_frozencargo_econtainer_cost_ocean + $delivery_frozencargo_ehcontainer_cost_ocean + $delivery_frozencargo_nonbarrelORcontainer_cost_ocean), 2, '.', '' );
			
			$delivery_frozencargo_total_cost_express = number_format ( ($delivery_frozencargo_letter_cost_express + $delivery_frozencargo_document_cost_express + $delivery_frozencargo_nonletterORdocument_cost_express), 2, '.', '' );
			
			$delivery_frozencargo_total_cost_mailbox = number_format ( (($us_mailbox_freight * 0.5) + (75.00 * $numberofpieces)), 2, '.', '' );
			
			$delivery_frozencargo_total_cost_air = number_format ( ((($air_cost * 0.5) + (75.00 * $numberofpieces))), 2, '.', '' );
			
			$delivery_frozencargo_total_cost_smallpackage = number_format ( ((($base_rate_small_package + $markup_small_package) * 0.5) + (75.00 * $numberofpieces)), 2, '.', '' );
			
			$delivery_frozencargo_total_cost_domestic = number_format ( (($domestic_cost_air_ocean * 0.5) + (75.00 * $numberofpieces)), 2, '.', '' );
		}
		
		if ($delivery_request == 'Inside Delivery') {
			if ($barrelItems > 0) {
				$delivery_insidedelivery_cost_barrel = number_format ( (5 * $barrelQty), 2, '.', '' );
			} else {
				$delivery_insidedelivery_cost_barrel = 0;
			}
			
			if ($econtainerItems > 0) {
				$delivery_insidedelivery_cost_econtainer = number_format ( (5 * $econtainerQty), 2, '.', '' );
			} else {
				$delivery_insidedelivery_cost_econtainer = 0;
			}
			
			if ($ehcontainerItems > 0) {
				$delivery_insidedelivery_cost_ehcontainer = number_format ( (5 * $ehcontainerQty), 2, '.', '' );
			} else {
				$delivery_insidedelivery_cost_ehcontainer = 0;
			}
			
			if ($nonBarrelOrContainerItems > 0) {
				$delivery_insidedelivery_cost_nonbarrelORcontainer = number_format ( (0.05 * $nonbarrelORcontainer_ChargeableWeight), 2, '.', '' );
				$delivery_insidedelivery_cost_nonbarrelORcontainer_domestic = number_format ( (0.05 * $nonBarrelOrContainerWeight), 2, '.', '' );
			} else {
				$delivery_insidedelivery_cost_nonbarrelORcontainer = 0;
				$delivery_insidedelivery_cost_nonbarrelORcontainer_domestic = 0;
			}
			
			$delivery_insidedelivery_total_cost = number_format ( ($delivery_insidedelivery_cost_barrel + $delivery_insidedelivery_cost_econtainer + $delivery_insidedelivery_cost_ehcontainer + $delivery_insidedelivery_cost_nonbarrelORcontainer), 2, '.', '' );
			$delivery_insidedelivery_total_cost_domestic = number_format ( ($delivery_insidedelivery_cost_barrel + $delivery_insidedelivery_cost_econtainer + $delivery_insidedelivery_cost_ehcontainer + $delivery_insidedelivery_cost_nonbarrelORcontainer_domestic), 2, '.', '' );
		}
		
		if ($delivery_request == 'Stairs') {
			if ($barrelItems > 0) {
				$delivery_stairs_cost_barrel = number_format ( (5 * $barrelQty), 2, '.', '' );
			} else {
				$delivery_stairs_cost_barrel = 0;
			}
			
			if ($econtainerItems > 0) {
				$delivery_stairs_cost_econtainer = number_format ( (5 * $econtainerQty), 2, '.', '' );
			} else {
				$delivery_stairs_cost_econtainer = 0;
			}
			
			if ($ehcontainerItems > 0) {
				$delivery_stairs_cost_ehcontainer = number_format ( (5 * $ehcontainerQty), 2, '.', '' );
			} else {
				$delivery_stairs_cost_ehcontainer = 0;
			}
			
			if ($nonBarrelOrContainerItems > 0) {
				$delivery_stairs_cost_nonbarrelORcontainer = number_format ( (0.05 * $nonbarrelORcontainer_ChargeableWeight), 2, '.', '' );
				$delivery_stairs_cost_nonbarrelORcontainer_domestic = number_format ( (0.05 * $nonBarrelOrContainerWeight), 2, '.', '' );
			} else {
				$delivery_stairs_cost_nonbarrelORcontainer = 0;
				$delivery_stairs_cost_nonbarrelORcontainer_domestic = 0;
			}
			
			$delivery_stairs_total_cost = number_format ( ($delivery_stairs_cost_barrel + $delivery_stairs_cost_econtainer + $delivery_stairs_cost_ehcontainer + $delivery_stairs_cost_nonbarrelORcontainer), 2, '.', '' );
			$delivery_stairs_total_cost_domestic = number_format ( ($delivery_stairs_cost_barrel + $delivery_stairs_cost_econtainer + $delivery_stairs_cost_ehcontainer + $delivery_stairs_cost_nonbarrelORcontainer_domestic), 2, '.', '' );
		}
		$delivery_request_cost_air = $delivery_forklift_cost + $delivery_liftgate_cost + $delivery_perishable_total_cost_air + $delivery_frozencargo_total_cost_air + $delivery_insidedelivery_total_cost + $delivery_stairs_total_cost;
		$delivery_request_cost_ocean = $delivery_forklift_cost + $delivery_liftgate_cost + $delivery_perishable_total_cost_ocean + $delivery_frozencargo_total_cost_ocean + $delivery_insidedelivery_total_cost + $delivery_stairs_total_cost;
		$delivery_request_cost_smallpackage = $delivery_forklift_cost + $delivery_liftgate_cost + $delivery_perishable_total_cost_smallpackage + $delivery_frozencargo_total_cost_smallpackage + $delivery_insidedelivery_total_cost + $delivery_stairs_total_cost;
		$delivery_request_cost_mailbox = $delivery_forklift_cost + $delivery_liftgate_cost + $delivery_perishable_total_cost_mailbox + $delivery_frozencargo_total_cost_mailbox + $delivery_insidedelivery_total_cost + $delivery_stairs_total_cost;
		$delivery_request_cost_express = $delivery_forklift_cost_express + $delivery_liftgate_cost_express + $delivery_perishable_total_cost_express + $delivery_frozencargo_total_cost_express + $delivery_insidedelivery_total_cost + $delivery_stairs_total_cost;
		$delivery_request_cost_domestic = $delivery_forklift_cost_domestic + $delivery_liftgate_cost_domestic + $delivery_perishable_total_cost_domestic + $delivery_frozencargo_total_cost_domestic + $delivery_insidedelivery_total_cost_domestic + $delivery_stairs_total_cost_domestic;
	}
	// ===================================================================================
	if (in_array ( $pickupCountry, array (
			"United States",
			"Miami US Warehouse",
			"Orlando US Warehouse",
			"Brooklyn NY USA",
			"Boston US Warehouse",
			"Huston US Warehouse",
			"Atlanta US Warehouse",
			"Pooleville MD USA",
			"Philadelphia US Warehouse",
			"Kearny NJ USA" 
	) )) {
		$insurance = $insurance;
		$residentialPickup_cost = $residentialPickup_cost;
		$residentialDelivery_cost = $residentialDelivery_cost;
	} else {
		$insurance = '0.00';
		$residentialPickup_cost = '0.00';
		$residentialDelivery_cost = '0.00';
	}
	
	if ($small_package == 'N/A') {
		if ($us_mailbox_freight_total == 'N/A') {
			$smallpkg_mailbox_freight = 'N/A';
		} else {
			if ($deliveryCountry == 'United States') {
				$smallpkg_mailbox_freight = 'N/A';
			} else {
				$smallpkg_mailbox_freight = number_format ( ($us_mailbox_freight_total + $pickup_request_cost_mailbox + $delivery_request_cost_mailbox + $insurance + $residentialPickup_cost + $residentialDelivery_cost), 2, '.', '' );
			}
		}
	} else {
		if ($deliveryCountry == 'United States') {
			$smallpkg_mailbox_freight = 'N/A';
		} else {
			$smallpkg_mailbox_freight = number_format ( ($small_package + $pickup_request_cost_smallpackage + $delivery_request_cost_smallpackage + $insurance + $residentialPickup_cost + $residentialDelivery_cost), 2, '.', '' );
		}
	}
	
	if ($total_ocean_freight == 'N/A') {
		$total_ocean_freight = 'N/A';
		$total_barrel_ocean_freight = 'N/A';
		$fullservice_barrel_ocean_freight = 'N/A';
	} else {
		if ($deliveryCountry == 'United States') {
			$total_ocean_freight = 'N/A';
			$total_barrel_ocean_freight = 'N/A';
			$fullservice_barrel_ocean_freight = 'N/A';
		} else {
			if ($deliveryCountry == 'United States') {
				$total_ocean_freight = 'N/A';
				$total_barrel_ocean_freight = 'N/A';
				$fullservice_barrel_ocean_freight = 'N/A';
			} else {
				$total_ocean_freight = number_format ( ($total_ocean_freight + $pickup_request_cost_ocean + $delivery_request_cost_ocean + $insurance + $residentialPickup_cost + $residentialDelivery_cost), 2, '.', '' );
				$total_barrel_ocean_freight = number_format ( (($total_ocean_freight - $insurance)), 2, '.', '' );
				$fullservice_barrel_ocean_freight = number_format ( ($fullservice_barrel_ocean_freight + $pickup_request_cost_ocean + $delivery_request_cost_ocean + $residentialPickup_cost + $residentialDelivery_cost), 2, '.', '' );
			}
		}
	}
	
	if ($air_cargo == 'N/A') {
		$air_cargo = 'N/A';
	} else {
		if ($deliveryCountry == 'United States') {
			$air_cargo = 'N/A';
		} else {
			$air_cargo = number_format ( ($air_cargo + $pickup_request_cost_air + $delivery_request_cost_air + $insurance + $residentialPickup_cost + $residentialDelivery_cost), 2, '.', '' );
		}
		
		write_log("aircargo_rate");
		write_log(array($air_cargo));
		
	}
	
	if ($express_freight == 'N/A') {
		$express_freight = 'N/A';
	} else {
		/* if($new_destination == 'MIA'){ */
		if ($new_origin == 'MIA') {
			if ($deliveryCountry == 'United States') {
				$express_freight = 'N/A';
			} else {
				$express_freight = number_format ( ($express_freight + $pickup_request_cost_express + $delivery_request_cost_express + $insurance + $residentialPickup_cost + $residentialDelivery_cost), 2, '.', '' );
			}
		} else {
			$express_freight = 'N/A';
		}
	}
	
	if ($caribbean_same_day_express_freight == 'N/A') {
		$caribbean_same_day_express_freight = 'N/A';
	} else {
		if ($new_origin == $deliveryCountry) {
			$caribbean_same_day_express_freight = 'N/A';
		} else {
			$caribbean_same_day_express_freight = number_format ( ($caribbean_same_day_express_freight + $pickup_request_cost_express + $delivery_request_cost_express + $caribbean_insurance + $caribbean_residentialPickup_cost + $caribbean_residentialDelivery_cost), 2, '.', '' );
		}
	}
	
	if ($caribbean_two_day_express_freight == 'N/A') {
		$caribbean_two_day_express_freight = 'N/A';
	} else {
		if ($new_origin == $deliveryCountry) {
			$caribbean_two_day_express_freight = 'N/A';
		} else {
			$caribbean_two_day_express_freight = number_format ( ($caribbean_two_day_express_freight + $pickup_request_cost_express + $delivery_request_cost_express + $caribbean_insurance + $caribbean_residentialPickup_cost + $caribbean_residentialDelivery_cost), 2, '.', '' );
		}
	}
	
	if ($domestic_freight == 'N/A') {
		$domestic_freight = 'N/A';
		$domestic_expedited = 'N/A';
	} else {
		if (! in_array ( $pickupCountry, array (
				"United States",
				"Miami US Warehouse",
				"Orlando US Warehouse",
				"Brooklyn NY USA",
				"Boston US Warehouse",
				"Huston US Warehouse",
				"Atlanta US Warehouse",
				"Pooleville MD USA",
				"Philadelphia US Warehouse",
				"Kearny NJ USA" 
		) )) {
			$domestic_freight = 'N/A';
			$domestic_expedited = 'N/A';
		} else {
			$domestic_freight = number_format ( ($domestic_freight + $pickup_request_cost_domestic + $delivery_request_cost_domestic + $insurance + $residentialPickup_cost + $residentialDelivery_cost), 2, '.', '' );
			$domestic_expedited = number_format ( ($domestic_expedited + $pickup_request_cost_domestic + $delivery_request_cost_domestic + $insurance + $residentialPickup_cost + $residentialDelivery_cost), 2, '.', '' );
		}
	}
	
	if ($smallpkg_mailbox_freight == 'N/A') {
		$smallpk_mbox_transit_time = 'N/A';
		$smallpk_mbox_depart_days = 'N/A';
	} else {
		if ($deliveryCountry == 'United States') {
			$smallpk_mbox_transit_time = 'N/A';
			$smallpk_mbox_depart_days = 'N/A';
		} else {
			$smallpk_mbox_transit_time = $air_transit_time;
			$smallpk_mbox_depart_days = $air_depart_days;
		}
	} // ===================================================================================
	  // RETURN RESULTS FOR DISPLAY
	  // ===================================================================================
	$array = array (
			$us_mailbox,
			$ocean_cargo,
			$air_cargo,
			$small_package,
			$domestic_cost_smallpkg,
			$domestic_calculated_rate_barrel,
			$domestic_cost_air_ocean,
			$insurance,
			$warehouse_zip,
			$air_transit_time,
			$air_depart_days,
			$ocean_transit_time,
			$ocean_depart_days,
			$zones,
			$us_mailbox_freight_total,
			$express_freight,
			$domestic_cost_express,
			$express_chargeable_weight,
			$express_rates,
			$chargeable_weight,
			$sum_pkgWeight,
			$sum_volWeight,
			$total_cubicft,
			$domestic_freight,
			$smallpkg_mailbox_freight,
			$express_transit_time,
			$express_depart_days,
			$domestic_transit_time,
			$domestic_depart_days,
			$total_cubicft,
			$total_ocean_freight,
			$smallpk_mbox_transit_time,
			$smallpk_mbox_depart_days,
			$unique_pkgTypes,
			$sum_pkgQty,
			$fullservice_barrel_ocean_freight,
			$domestic_expedited,
			$domestic_expedited_transit_time,
			$total_barrel_ocean_freight,
			$caribbean_same_day_express_freight,
			$caribbean_two_day_express_freight,
			$caribbean_same_day_express_transit_time,
			$caribbean_two_day_express_transit_time 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_get_quick_quote', 'get_quick_quote_fn' );
add_action ( 'wp_ajax_nopriv_get_quick_quote', 'get_quick_quote_fn' );

// Display 24 products per page. Goes in functions.php
add_filter ( 'loop_shop_per_page', create_function ( '$cols', 'return 24;' ), 20 );

// Remove (Free) label on cart page for "Shipping and Handling" if cost is $0
function wc_change_cart_shipping_free_label($label) {
	$label = str_replace ( "(Free)", "(Freight & Customs Duties: Payable at Destination)", $label );
	return $label;
}
add_filter ( 'woocommerce_cart_shipping_method_full_label', 'wc_change_cart_shipping_free_label' );

/**
 * Gravity Wiz // Gravity Forms // Limit Submissions Per Time Period (by IP, User, Role, Form URL, or Field Value)
 *
 * Limit the number of times a form can be submitted per a specific time period. You modify this limit to apply to
 * the visitor's IP address, the user's ID, the user's role, a specific form URL, or the value of a specific field.
 * These "limiters" can be combined to create more complex limitations.
 *
 * @version 2.4
 * @author David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link http://gravitywiz.com/better-limit-submission-per-time-period-by-user-or-ip/
 */
class GW_Submission_Limit {
	var $_args;
	var $_notification_event;
	private static $forms_with_individual_settings = array ();
	function __construct($args) {
		
		// make sure we're running the required minimum version of Gravity Forms
		if (! property_exists ( 'GFCommon', 'version' ) || ! version_compare ( GFCommon::$version, '1.8', '>=' ))
			return;
		
		$this->_args = wp_parse_args ( $args, array (
				'form_id' => false,
				'limit' => 1,
				'limit_by' => 'ip', // 'ip', 'user_id', 'role', 'embed_url', 'field_value'
				'time_period' => 60 * 60 * 24, // integer in seconds or 'day', 'month', 'year' to limit to current day, month, or year respectively
				'limit_message' => __ ( 'Sorry, you have reached the submission limit for this form.' ),
				'apply_limit_per_form' => true,
				'enable_notifications' => false 
		) );
		
		if (! is_array ( $this->_args ['limit_by'] )) {
			$this->_args ['limit_by'] = array (
					$this->_args ['limit_by'] 
			);
		}
		
		if ($this->_args ['form_id']) {
			self::$forms_with_individual_settings [] = $this->_args ['form_id'];
		}
		
		add_action ( 'init', array (
				$this,
				'init' 
		) );
	}
	function init() {
		add_filter ( 'gform_pre_render', array (
				$this,
				'pre_render' 
		) );
		add_filter ( 'gform_validation', array (
				$this,
				'validate' 
		) );
		
		if ($this->_args ['enable_notifications']) {
			
			$this->enable_notifications ();
			
			add_action ( 'gform_after_submission', array (
					$this,
					'maybe_send_limit_reached_notifications' 
			), 10, 2 );
		}
	}
	function pre_render($form) {
		if (! $this->is_applicable_form ( $form ) || ! $this->is_limit_reached ( $form ['id'] )) {
			return $form;
		}
		
		$submission_info = rgar ( GFFormDisplay::$submission, $form ['id'] );
		
		// if no submission, hide form
		// if submission and not valid, hide form
		// unless 'field_value' limiter is applied
		if ((! $submission_info || ! rgar ( $submission_info, 'is_valid' )) && ! $this->is_limited_by_field_value ()) {
			add_filter ( 'gform_get_form_filter_' . $form ['id'], create_function ( '', 'return \'<div class="limit-message">' . $this->_args ['limit_message'] . '</div>\';' ) );
		}
		
		return $form;
	}
	function validate($validation_result) {
		if (! $this->is_applicable_form ( $validation_result ['form'] ) || ! $this->is_limit_reached ( $validation_result ['form'] ['id'] )) {
			return $validation_result;
		}
		
		$validation_result ['is_valid'] = false;
		
		if ($this->is_limited_by_field_value ()) {
			$field_ids = array_map ( 'intval', $this->get_limit_field_ids () );
			foreach ( $validation_result ['form'] ['fields'] as &$field ) {
				if (in_array ( $field ['id'], $field_ids )) {
					$field ['failed_validation'] = true;
					$field ['validation_message'] = $this->_args ['limit_message'];
				}
			}
		}
		
		return $validation_result;
	}
	public function is_limit_reached($form_id) {
		global $wpdb;
		
		$where = array ();
		$join = array ();
		
		$where [] = 'l.status = "active"';
		
		foreach ( $this->_args ['limit_by'] as $limiter ) {
			switch ($limiter) {
				case 'role' : // user ID is required when limiting by role
				case 'user_id' :
					$where [] = $wpdb->prepare ( 'l.created_by = %s', get_current_user_id () );
					break;
				case 'embed_url' :
					$where [] = $wpdb->prepare ( 'l.source_url = %s', GFFormsModel::get_current_page_url () );
					break;
				case 'field_value' :
					
					$values = $this->get_limit_field_values ( $form_id, $this->get_limit_field_ids () );
					
					// if there is no value submitted for any of our fields, limit is never reached
					if (empty ( $values )) {
						return false;
					}
					
					foreach ( $values as $field_id => $value ) {
						$table_slug = sprintf ( 'ld%s', str_replace ( '.', '_', $field_id ) );
						$join [] = "INNER JOIN {$wpdb->prefix}rg_lead_detail {$table_slug} ON {$table_slug}.lead_id = l.id";
						// $where[] = $wpdb->prepare( "CAST( {$table_slug}.field_number as unsigned ) = %f AND {$table_slug}.value = %s", $field_id, $value );
						$where [] = $wpdb->prepare ( "\n( ( {$table_slug}.field_number BETWEEN %s AND %s ) AND {$table_slug}.value = %s )", doubleval ( $field_id ) - 0.001, doubleval ( $field_id ) + 0.001, $value );
					}
					
					break;
				default :
					$where [] = $wpdb->prepare ( 'ip = %s', GFFormsModel::get_ip () );
			}
		}
		
		if ($this->_args ['apply_limit_per_form']) {
			$where [] = $wpdb->prepare ( 'l.form_id = %d', $form_id );
		}
		
		$time_period = $this->_args ['time_period'];
		$time_period_sql = false;
		
		if ($time_period === false) {
			// no time period
		} else if (intval ( $time_period ) > 0) {
			$time_period_sql = $wpdb->prepare ( 'date_created BETWEEN DATE_SUB(utc_timestamp(), INTERVAL %d SECOND) AND utc_timestamp()', $this->_args ['time_period'] );
		} else {
			switch ($time_period) {
				case 'per_day' :
				case 'day' :
					$time_period_sql = 'DATE( date_created ) = DATE( utc_timestamp() )';
					break;
				case 'per_month' :
				case 'month' :
					$time_period_sql = 'MONTH( date_created ) = MONTH( utc_timestamp() )';
					break;
				case 'per_year' :
				case 'year' :
					$time_period_sql = 'YEAR( date_created ) = YEAR( utc_timestamp() )';
					break;
			}
		}
		
		if ($time_period_sql) {
			$where [] = $time_period_sql;
		}
		
		$where = implode ( ' AND ', $where );
		$join = implode ( "\n", $join );
		
		$sql = "SELECT count( l.id )
                FROM {$wpdb->prefix}rg_lead l
                $join
                WHERE $where";
		
		$entry_count = $wpdb->get_var ( $sql );
		
		return $entry_count >= $this->get_limit ();
	}
	public function is_limited_by_field_value() {
		return in_array ( 'field_value', $this->_args ['limit_by'] );
	}
	public function get_limit_field_ids() {
		$limit = $this->_args ['limit'];
		
		if (is_array ( $limit )) {
			$field_ids = array (
					call_user_func ( 'array_shift', array_keys ( $this->_args ['limit'] ) ) 
			);
		} else {
			$field_ids = $this->_args ['fields'];
		}
		
		return $field_ids;
	}
	public function get_limit_field_values($form_id, $field_ids) {
		$form = GFAPI::get_form ( $form_id );
		$values = array ();
		
		foreach ( $field_ids as $field_id ) {
			
			$field = GFFormsModel::get_field ( $form, $field_id );
			$input_name = 'input_' . str_replace ( '.', '_', $field_id );
			$value = GFFormsModel::prepare_value ( $form, $field, rgpost ( $input_name ), $input_name, null );
			
			if (! rgblank ( $value )) {
				$values [$field_id] = $value;
			}
		}
		
		return $values;
	}
	public function get_limit() {
		$limit = $this->_args ['limit'];
		
		if ($this->is_limited_by_field_value ()) {
			$limit = is_array ( $limit ) ? array_shift ( $limit ) : intval ( $limit );
		} else if (in_array ( 'role', $this->_args ['limit_by'] )) {
			$limit = rgar ( $limit, $this->get_user_role () );
		}
		
		return intval ( $limit );
	}
	public function get_user_role() {
		$user = wp_get_current_user ();
		$role = array_shift ( $user->roles );
		
		return $role;
	}
	public function enable_notifications() {
		if (! class_exists ( 'GW_Notification_Event' )) {
			
			_doing_it_wrong ( 'GW_Inventory::$enable_notifications', __ ( 'Inventory notifications require the \'GW_Notification_Event\' class.' ), '1.0' );
		} else {
			
			$event_slug = implode ( array_filter ( array (
					"gw_submission_limit_limit_reached",
					$this->_args ['form_id'] 
			) ) );
			$event_name = GFForms::get_page () == 'notification_edit' ? __ ( 'Submission limit reached' ) : __ ( 'Event name is only populated on Notification Edit view; saves a DB call to get the form on every ' );
			
			$this->_notification_event = new GW_Notification_Event ( array (
					'form_id' => $this->_args ['form_id'],
					'event_name' => $event_name,
					'event_slug' => $event_slug 
			)
			// 'trigger' => array( $this, 'notification_event_listener' )
			 );
		}
	}
	public function maybe_send_limit_reached_notifications($entry, $form) {
		if ($this->is_applicable_form ( $form ) && $this->is_limit_reached ( $form ['id'] )) {
			$this->send_limit_reached_notifications ( $form, $entry );
		}
	}
	public function send_limit_reached_notifications($form, $entry) {
		$this->_notification_event->send_notifications ( $this->_notification_event->get_event_slug (), $form, $entry, true );
	}
	function is_applicable_form($form) {
		$form_id = isset ( $form ['id'] ) ? $form ['id'] : $form;
		$is_global_form = empty ( $this->_args ['form_id'] ) && ! in_array ( $form_id, self::$forms_with_individual_settings );
		$is_specific_form = $form_id == $this->_args ['form_id'];
		
		return $is_global_form || $is_specific_form;
	}
}
class GWSubmissionLimit extends GW_Submission_Limit {
}

// Configuration

// Basic Usage
new GW_Submission_Limit ( array (
		'form_id' => 55,
		'limit' => '1',
		'limit_by' => 'user_id',
		'time_period' => 'per_year',
		'limit_message' => 'You are limited to one entry per year.' 
) );

/**
 * Gravity Wiz // Gravity Perks // Get Service Charge based in Sum of Nested Form Fields
 *
 * Get the service charge base on sum of a column from a Gravity Forms List field.
 */
class Calculate_Service_Charge {
	private static $script_output = false;
	public function __construct($args = array()) {
		
		// make sure we're running the required minimum version of Gravity Forms
		if (! property_exists ( 'GFCommon', 'version' ) || ! version_compare ( GFCommon::$version, '1.8', '>=' ))
			return;
			
			// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args ( $args, array (
				'form_id' => false,
				'nested_form_field_id' => false,
				'nested_field_id' => false,
				'target_sum_field_id' => false,
				'target_service_charge_field_id' => false,
				'target_creditcard_charge_field_id' => false,
				'target_insurance_field_id' => false 
		) );
		
		extract ( $this->_args );
		
		// time for hooks
		add_action ( "gform_register_init_scripts_{$form_id}", array (
				$this,
				'register_init_script' 
		) );
		add_action ( "gform_pre_render_{$form_id}", array (
				$this,
				'maybe_output_script' 
		) );
	}
	public function register_init_script($form) {
		$args = array (
				'formId' => $this->_args ['form_id'],
				'nestedFormFieldId' => $this->_args ['nested_form_field_id'],
				'nestedFieldId' => $this->_args ['nested_field_id'],
				'targetsumFieldId' => $this->_args ['target_sum_field_id'],
				'targetservicechargeFieldId' => $this->_args ['target_service_charge_field_id'],
				'targetcreditcardchargeFieldId' => $this->_args ['target_creditcard_charge_field_id'],
				'targetinsuranceFieldId' => $this->_args ['target_insurance_field_id'] 
		);
		
		$script = 'new CalculateServiceCharge( ' . json_encode ( $args ) . ' );';
		$slug = "gpnf_column_sum_{$this->_args['form_id']}_{$this->_args['target_sum_field_id']}";
		
		GFFormDisplay::add_init_script ( $form ['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
	}
	public function maybe_output_script($form) {
		if (! self::$script_output)
			$this->script ();
		
		return $form;
	}
	public function script() {
		?>

<script type="text/javascript">
 
var CalculateServiceCharge;
 
( function( $ ){
 
CalculateServiceCharge = function( args ) {
 
var self = this;
 
// copy all args to current object: formId, fieldId
for( prop in args ) {
if( args.hasOwnProperty( prop ) )
self[prop] = args[prop];
}
 
self.init = function() {
 
var gpnf = $( '#gform_wrapper_' + self.formId ).data( 'GPNestedForms_' + self.nestedFormFieldId );
 
gpnf.viewModel.entries.subscribe( function( newValue ) {
self.updateSum( newValue, self.nestedFieldId, self.targetsumFieldId, self.targetservicechargeFieldId, self.targetcreditcardchargeFieldId, self.targetinsuranceFieldId, self.formId )
} );
 
self.updateSum( gpnf.viewModel.entries(), self.nestedFieldId, self.targetsumFieldId, self.targetservicechargeFieldId, self.targetcreditcardchargeFieldId, self.targetinsuranceFieldId, self.formId );
 
}
 
self.calculateSum = function( entries, fieldId ) {
 
var total = 0;
 
for( var i = 0; i < entries.length; i++ ) {
 
var count = gformToNumber( entries[i][fieldId] ? entries[i][fieldId] : 0 );
 
console.log( count );
 
if( ! isNaN( parseFloat( count ) ) )
total += parseFloat( count );
 
}
 
return total;
}
 
self.updateSum = function( entries, nestedFieldId, targetsumFieldId, targetservicechargeFieldId, targetcreditcardchargeFieldId, targetinsuranceFieldId, formId ) {

 
var total = self.calculateSum( entries, nestedFieldId );

/*Service Charge Calculation*/
if(total > 0.00 && total <= 50.00){
	servicecharge = 5.00;
}else if(total > 50.01){
	servicecharge = (total * (5/100));
}else{
	servicecharge = 0.00;
}

/*Credit Card Charge Calculation*/
if(total > 0.00 && total <= 50.00){
	creditcardcharge = 0.00;
}else if(total > 50.01){
	creditcardcharge = (total * (4.5/100));
}else{
	creditcardcharge = 0.00;
}

/*Insurance Calculation*/
if(total > 0.00 && total <= 100.00){
	insurance = 1.50;
}else if(total > 100.01){
	insurance = (total * (1.5/100));
}else{
	insurance = 0.00;
}
 
$( '#input_' + formId + '_' + targetsumFieldId ).val( total ).change();
$( '#input_' + formId + '_' + targetservicechargeFieldId ).val( servicecharge ).change();
$( '#input_' + formId + '_' + targetcreditcardchargeFieldId ).val( creditcardcharge ).change();
$( '#input_' + formId + '_' + targetinsuranceFieldId ).val( insurance ).change();
 
}
 
self.init();
 
}
 
} )( jQuery );
 
</script>

<?php
	}
}

// Configuration

new Calculate_Service_Charge ( array (
		'form_id' => 59,
		'nested_form_field_id' => 7,
		'nested_field_id' => 8,
		'target_sum_field_id' => 10,
		'target_service_charge_field_id' => 55,
		'target_creditcard_charge_field_id' => 56,
		'target_insurance_field_id' => 57 
) );

/**
 * Dynamically Populating User Role
 * http://gravitywiz.com/2012/04/30/dynamically-populating-user-role/
 */
add_filter ( 'gform_field_value_user_role', 'gform_populate_user_role' );
function gform_populate_user_role($value) {
	$user = wp_get_current_user ();
	$role = $user->roles;
	return reset ( $role );
}

// SHORT QUICK QUOTE CALCULATION
function get_short_quick_quote_fn() {
	$fieldvalues = $_POST ['fieldvalues'];
	$inputvals = explode ( ", ", $fieldvalues );
	
	$pkgDestination = $inputvals [0];
	$pkgWeight = $inputvals [1];
	$pkgValue = $inputvals [2];
	$pkgLength = $inputvals [3];
	$pkgWidth = $inputvals [4];
	$pkgHeight = $inputvals [5];
	
	if ($pkgLength == '') {
		$pkgLength = '0.00';
	} else {
		$pkgLength = $pkgLength;
	}
	if ($pkgWidth == '') {
		$pkgWidth = '0.00';
	} else {
		$pkgWidth = $pkgWidth;
	}
	if ($pkgHeight == '') {
		$pkgHeight = '0.00';
	} else {
		$pkgHeight = $pkgHeight;
	}
	
	$pkgOrigin = 'MIA';
	
	$pkgVolume = number_format ( (($pkgLength * $pkgWidth * $pkgHeight) / 166), 2, '.', '' );
	
	$weight_difference = number_format ( ($pkgVolume - $pkgWeight), 2, '.', '' );
	
	$actual_weight = ceil ( $pkgWeight );
	$volumetric_weight = ceil ( $pkgVolume );
	
	$pkgValue = str_replace ( '$', '', $pkgValue );
	$pkgValue = str_replace ( ',', '', $pkgValue );
	
	if ($pkgValue <= '100.00') {
		$insurance = number_format ( (1.25), 2, '.', '' );
		$insurance_preAlert = number_format ( (0.90), 2, '.', '' );
	} else {
		$insurance = number_format ( ((1.25 * $pkgValue) / 100), 2, '.', '' );
		$insurance_preAlert = number_format ( ((0.90 * $pkgValue) / 100), 2, '.', '' );
	}
	
	/* ---Get Delivery Type Rates--- */
	global $wpdb; // Accessing WP Database (non-WP Table) use code below.
	
	$mailbox_difference = $wpdb->get_results ( "SELECT difference from wp_sp_volumetric_rules where delivery_type = 'US MailBox' and min_measure <= " . $actual_weight . " and max_measure >= " . $actual_weight );
	
	if (empty ( $mailbox_difference )) {
		$mailbox_weight_difference = 0;
	} else {
		$mailbox_weight_difference = $mailbox_difference [0]->difference;
		$mailbox_weight_difference = number_format ( ($mailbox_weight_difference), 2, '.', '' );
	}
	
	if ($pkgLength >= 36 || $pkgWidth >= 36 || $pkgHeight >= 36) {
		if ($volumetric_weight >= $actual_weight) {
			$mailbox_chargeable_weight = $volumetric_weight;
		} else {
			if ($pkgWeight <= '0.00') {
				$mailbox_chargeable_weight = $volumetric_weight;
			} else {
				$mailbox_chargeable_weight = $actual_weight;
			}
		}
	} else {
		if ($weight_difference > $mailbox_weight_difference) {
			$mailbox_chargeable_weight = $volumetric_weight;
		} else {
			if ($pkgWeight <= '0.00') {
				$mailbox_chargeable_weight = $volumetric_weight;
			} else {
				$mailbox_chargeable_weight = $actual_weight;
			}
		}
	}
	
	$mailbox_rates2 = $wpdb->get_results ( "SELECT base_rate, min_measure, max_measure from wp_sp_rates where delivery_type = 'US MailBox' and origin = '" . $pkgOrigin . "' and destination = '" . $pkgDestination . "'" );
	
	// ===================================================================================
	// MAILBOX CALCULATION
	// ===================================================================================
	if (empty ( $mailbox_rates2 )) {
		$insurance = 'N/A';
		$insurance_preAlert = 'N/A';
		$us_mailbox_freight = 'N/A';
		$us_mailbox_freight_total = 'N/A';
		$us_mailbox_freight_total_preAlert = 'N/A';
	} else {
		if ($mailbox_chargeable_weight > 150 || $mailbox_chargeable_weight <= 0) {
			$insurance = 'N/A';
			$insurance_preAlert = 'N/A';
			$us_mailbox_freight = 'N/A';
			$us_mailbox_freight_total = 'N/A';
			$us_mailbox_freight_total_preAlert = 'N/A';
		} else {
			$wgt = $mailbox_chargeable_weight;
			$us_mailbox_freight = '0.00';
			$pound_range_list = array ();
			$rates_list = array ();
			$freight_list = array ();
			$decremental_wgt = array ();
			
			for($i = 0; $i < count ( $mailbox_rates2 ); $i ++) {
				if ($wgt <= 0) {
					break;
				} else {
					$pound_range = (($mailbox_rates2 [$i]->max_measure - $mailbox_rates2 [$i]->min_measure) + 1);
					$pound_range_list [] = array (
							"value" => $pound_range 
					);
					$base_rate_mailbox2 = $mailbox_rates2 [$i]->base_rate;
					$rates_list [] = array (
							"value" => $base_rate_mailbox2 
					);
					if ($wgt >= $pound_range) {
						$use_weight = $pound_range;
					} else {
						$use_weight = $wgt;
					}
					$incremental_freight = ($use_weight * $base_rate_mailbox2);
					$freight_list [] = array (
							"value" => $incremental_freight 
					);
					$us_mailbox_freight = number_format ( ($us_mailbox_freight + ($use_weight * $base_rate_mailbox2)), 2, '.', '' );
					$wgt = $wgt - $use_weight;
					$decremental_wgt [] = array (
							"value" => $wgt 
					);
				}
			}
			
			if (($pkgDestination == "Dominica") && ($mailbox_chargeable_weight >= 2)) {
				$us_mailbox_freight = number_format ( ($us_mailbox_freight - 8), 2, '.', '' );
			} else {
				$us_mailbox_freight = number_format ( ($us_mailbox_freight), 2, '.', '' );
			}
			
			$us_mailbox_freight_total = number_format ( ($us_mailbox_freight + $insurance), 2, '.', '' );
			$us_mailbox_freight_total_preAlert = number_format ( ($us_mailbox_freight + $insurance_preAlert), 2, '.', '' );
		}
	}
	// ===================================================================================
	
	// ===================================================================================
	// RETURN RESULTS FOR DISPLAY
	// ===================================================================================
	$array = array (
			$us_mailbox_freight,
			$insurance,
			$us_mailbox_freight_total,
			$insurance_preAlert,
			$us_mailbox_freight_total_preAlert,
			$pkgLength,
			$pkgWidth,
			$pkgHeight,
			$pkgVolume,
			$pkgWeight,
			$weight_difference,
			$mailbox_chargeable_weight,
			$pkgValue 
	);
	
	echo json_encode ( $array );
	die ();
}
add_action ( 'wp_ajax_get_short_quick_quote', 'get_short_quick_quote_fn' );
add_action ( 'wp_ajax_nopriv_get_short_quick_quote', 'get_short_quick_quote_fn' );

add_filter ( 'wp_footer', function () {
	?>

<script type="text/javascript">

		( function( $ ) {

			$( document ).bind( 'gform_post_render', function() {

				var $addEntryBtn = $( 'button.gpnf-add-entry' );

				$addEntryBtn.insertBefore( $addEntryBtn.siblings( 'table' ) );

			} );

		} )( jQuery );

	</script>

<?php
} );

/**
 * Dynamically populating premium date if the user chooses to sign up as premium
 */

/*
 * add_filter('gform_field_value_premium_date', 'populate_post_premium_date');
 * function populate_post_premium_date($value){
 * global $post;
 *
 * $author_email = get_the_author_meta('email', $post->post_author);
 *
 * return $author_email;
 * }
 *
 */

?>