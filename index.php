<?php
require_once("smartmeter-vienna.class.php");
$sm = new ViennaSmartmeter("[username]", "[password]", false);
if ($sm->login()) {
	$me = $sm->getProfile();

	print_r($me);

	$yesterday = date('Y-m-d', strtotime("-1 days"));
	$consumption = $sm->getConsumptionByDay($me->defaultGeschaeftspartnerRegistration->zaehlpunkt, $me->defaultGeschaeftspartnerRegistration->geschaeftspartner, $yesterday);
	print_r($consumption);
} else {
	echo "WN login error.";
}
	
	//$measurements = $sm->getMeasurements($me->registration->zaehlpunkt, $yesterday, $yesterday, "QUARTER_HOUR");
	//print_r($measurements);
	
	
	//$events = $sm->getEvents($me->registration->zaehlpunkt, "2022-10-01 00:00:00", "2022-10-31 23:59:59");
	//print_r($events);

	//$sm->createEvent($me->registration->zaehlpunkt, "AUTOCREATEEVENT", "2022-10-16 13:00:00");

	//$res = $sm->deleteEvent($me->registration->zaehlpunkt, "6708");

	//print_r($res);

	//$limit = $sm->createLimit("APILIMIT", "2022-10-31 23:59:59", "d", "10000", "gt", $me->registration->zaehlpunkt);
	//print_r($limit);

	//$limits = $sm->getLimits();
	//print_r($limits);

	//$res = $sm->deleteLimit("12678965");
	//print_r($res);

	//$notifications = $sm->getNotifications("50", "DESC");
	//print_r($notifications);
