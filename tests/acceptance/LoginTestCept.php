<?php


return;

// Make sure you've added your site URL to acceptance.suite.yml
// @see http://codeception.com/docs/03-AcceptanceTests#PHP-Browser
$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Ensure WordPress Login Works' );

// Let's start on the login page
$I->amOnPage( wp_login_url() );

// Populate the login form's user id field
$I->fillField( 'input#user_login', 'beapi' );

// Popupate the login form's password field
$I->fillField( 'input#user_pass', 'beapi!56' );

// Submit the login form
$I->click( __( 'Log In' ) );

// Validate the successful loading of the Dashboard
$I->see( __( 'Dashboard' ) );