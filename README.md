# wienernetze-smartmeter-php
Read energy-consumption from Wiener Netze Smartmeters.

## Available Methods: 

- login(): Login with webpage credentials
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
- getMeterPoints(): Gets all Meterpoints assinged to your account.

## Usage
```php
<?php 
  require_once("smartmeter-vienna.class.php");
  $sm = new ViennaSmartmeter("[yourusername]", "[yourpassword]", $debug=false);
  $sm->login();
  $profile = $sm->getProfile();
  print_r($profile);

  $consumption = $sm->getConsumptionByDay($me->defaultGeschaeftspartnerRegistration->zaehlpunkt, $me->defaultGeschaeftspartnerRegistration->geschaeftspartner, date("Y-m-d"));
  print_r($consumption);

```
## Requirements
- php-curl

## Disclaimer
This is not an official API of Wiener Netze.
