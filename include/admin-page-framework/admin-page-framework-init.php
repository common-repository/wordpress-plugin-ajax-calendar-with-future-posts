<?php
/* 
	Plugin Name: Admin Page Framework Demo Plugin
	Plugin URI: http://en.michaeluno.jp/admin-page-framework
	Description: Demonstrates the features of the Admin Page Framework class.
	Author: Michael Uno
	Author URI: http://michaeluno.jp
	Version: 1.0.4.2
	Requirements: PHP 5.2.4 or above, WordPress 3.2 or above.
*/

	/*
	 * Brief Instruction: How to Use the Framework - Basic Five Steps
	 * 	1. Include the library.
	 * 	2. Extend the library class.
	 * 	3. Define the SetUp() method. Include the following methods in the definition. Decide the page title and the slug.
	 * 		SetRootMenu() - use it to specify the root menu.
	 * 	 	AddSubMenu() - use it to specify the sub menu and the page. This page will be the actual page your users will be going to access.
	 * 			IMPORTANT: Decide the page slug without hyphens and dots. This is very important since the page slug serves as the callback methods name.
	 * 		for other methods and more details, visit, http://en.michaeluno.jp/admin-page-framework/methods/
	 * 	4. Define callback methods.
	 * 	5. Instantiate the extended class.
	 * 	
	 * To get started, visit http://en.michaeluno.jp/admin-page-framework/get-started . It has the simplest example so you'll see how it works.
	 * */ 
 
// Step 1. Include the library
if ( !class_exists( 'Admin_Page_Framework' ) ) 
	include_once( dirname( __FILE__ ) . '/classes/admin-page-framework.php' );

// Step 2. Extend the class
class APF_AdminPageFramework_ACF extends Admin_Page_Framework {

	// Step 3. Define the setup method to set how many pages, page titles and icons etc.
	function SetUp() {
					
		// Create the root menu - specifies to which parent menu we are going to add sub pages.
		$this->SetRootMenu( 'Settings' );	
		
		// Add the sub menus and the pages
		// You need to decide what page title and the slug to use. 
		// Important: do not use dots and hyphens in the page slug. Alphabets and numbers and underscores only! 
		// You are going to use the page slug later on for the callback method.
		 
		$this->AddSubMenu(
			'Ajax Calendar',	// page and menu title
			'ajax_calendar_main',		// page slug - this will be the option name saved in the database
			plugins_url( 'img/demo_01_32x32.png', __FILE__ )
		);	// set the screen icon, it should be 32 x 32.
	 
		$this->AddFormSections( array(
			array(  
					'pageslug' => 'ajax_calendar_main',
				 
					'id' => 'Settings', 
					'title' => 'Settings',
				/*	'description' => 'These are text type fields.',*/
					'fields' => array(array( 
							'id' => 'ajax_months',
							'title' => 'Use Ajax', 
							'tip' => '',
							/*'description' => 'Check box\'s label can be a string, not an array.',*/
							'type' => 'checkbox',
							'label' => 'Ajax powered Next and previous month link   .',	// notice that the label key is not an array
							'default' => False
						)
			)) ));
		 
		
		 
		 
	}
 
	function do_ajax_calendar_main() {	

		// Disable the below output in the fifth tab.
		if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'fifthtab' ) return;
	
		submit_button();	// the save button
//print_r(get_option( 'ajax_calendar_future_options' ));
		// Show the saved option value. The option key is the string passed to the first parameter to the constructor of the class (at the end of this plugin).
		// If the option key is not set, the page slug will be used. 
	/*	if ( $options = get_option( 'ajax_calendar_future_options' ) )
			echo '<h3>Saved values</h3> '
				. $this->DumpArray( ( array ) $options );	// DumpArray will be useful to output array contents.*/
				
	}	 
	
}

// Step 5. Instantiate the class object.
if ( is_admin() )
	new APF_AdminPageFramework_ACF( 
		'ajax_calendar_future_options',	// the first parameter specifies the option key to use. If not set, each page slug will be used for the key.
		__FILE__	// this tells the framework the caller script path so that the script info will be embedded in the footer.
	);	

 
 