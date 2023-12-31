# wienernetze-smartmeter-php
Read energy-consumption from Wiener Netze Smartmeters.

## Available Methods: 

- login(): Login with [Wiener Netze webpage](https://log.wien/auth/realms/logwien/protocol/openid-connect/auth?client_id=wn-smartmeter&redirect_uri=https%3A%2F%2Fsmartmeter-web.wienernetze.at%2F&state=3f041e87-b560-4a4f-8bd9-4a75db707bd3&response_mode=fragment&response_type=code&scope=openid&nonce=8870c4d9-c087-4b8b-9b83-380dd6be2aff) credentials
- getProfile(): Get your profile info
- welcome(): Get all Infos on the welcome-page
- getConsumption($meterpoint, $start, $end): Does no longer work. See getConsumptionByDay()
- getConsumptionByDay($meterpoint, $customerid, $day): Get energy-consumption by $day.
- getMeasurements($profile, $start, $end, $type): Get energy-consumption (full days) limited by start and end parameters
- getEvents($meterpoint, $start, $end): Get Events limited by start and end parameters
- createEvent($meterpoint, $name, $start, $end): Create Event
- deleteEvent($id): Delete event by id. The id is returned with getEvents().
- getLimits(): Get limits set by the user.
- createLimit($name, $end, $period, $threshold, $type, $meterpoint): Create new Limit.
- deleteLimit($id): Delete limit. The id is returned with getLimits().
- getNotifications($limit, $order): Gets notifications limited by $limit and ordered by $order.
- getMeterPoints(): Gets all Meterpoints assinged to your account ( full detail ).
- getMeterPointIds(): Gets all Meterpoints assinged to your account ( id's only ).

## Usage
```php
<?php
	require_once("smartmeter-vienna.class.php");
	$sm = new ViennaSmartmeter("[yourusername]", "[yourpassword]", $debug=false);
	
	if($sm->login()){
		$profile = $sm->getProfile();
		print_r($profile);

		$meterpoint = $profile->defaultGeschaeftspartnerRegistration->zaehlpunkt;
		$customerid = $profile->defaultGeschaeftspartnerRegistration->geschaeftspartner;

		$yesterday = date('Y-m-d',strtotime("-1 days"));

		$consumption = $sm->getConsumptionByDay($meterpoint, $customerid, $yesterday);
		print_r($consumption);
	}else{
		echo "WN login error.";
	}

```
## Requirements
- php-curl

## Disclaimer
This is not an official API of Wiener Netze.
