<?php
require './auth.php';

if (!empty($ppa_data)) {

	$ppa_data = json_decode(stripslashes($ppa_data), true);

	if (defined('PAYPALAUTH_DEBUG')) {
		x_log_add('paypalauth', print_r($ppa_data, true));
	}

	$_profileAttributes = array(
		// Name types
		'payerid' => 'https://www.paypal.com/webapps/auth/schema/payerID',
		'email' => 'http://axschema.org/contact/email',
		'firstname' => 'http://axschema.org/namePerson/first',
		'lastname' => 'http://axschema.org/namePerson/last',
		//'fullname' => 'http://schema.openid.net/contact/fullname',
		'verifiedAccount' => 'https://www.paypal.com/webapps/auth/schema/verifiedAccount',
	);

	$_addressAttributes = array(
		'firstname' => 'http://axschema.org/namePerson/first',
		'lastname' => 'http://axschema.org/namePerson/last',
		'zipcode' => 'http://axschema.org/contact/postalCode/home',
		'country' => 'http://axschema.org/contact/country/home',
		'address' => 'http://schema.openid.net/contact/street1',
		'address2' => 'http://schema.openid.net/contact/street2',
		'city' => 'http://axschema.org/contact/city/home',
		'state' => 'http://axschema.org/contact/state/home',
		'phone' => 'http://axschema.org/contact/phone/default',
	);

	if ($ppa_data['attributes'] && $_profileAttributes) {
		foreach ($_profileAttributes as $k => $v) {
			if ($ppa_data['attributes'][$v]) {
				$profile[$k] = $ppa_data['attributes'][$v];
			}
		}
		$profile['openid_identity'] = $ppa_data['openid_identity'];
	}

	if ($ppa_data['attributes'] && $_addressAttributes) {
		foreach ($_addressAttributes as $k => $v) {
			if ($ppa_data['attributes'][$v]) {
				$address[$k] = $ppa_data['attributes'][$v];
			}
		}
	}

	x_session_register('ppa_payerId');

	$_tmp = func_ppa_check_user($profile['payerid'], $profile['openid_identity']);

	if ($_tmp['error'] == 'no_user_data') {
		$_userid = func_ppa_create_user($profile['payerid'], $profile, $address);
		$_tmp['status'] = true;
	} elseif ($_tmp['status'] == true) {
		$_userid = $_tmp['userid'];
	}

	if ($_userid && $_tmp['status'] == true) {
		func_ppa_login_user($_userid);

		$ppa_payerId = $profile['payerid'];

		func_header_location('home.php');
	} else {
		func_ppa_logout_n_redirect();
	}
}

func_header_location('home.php');
?>
