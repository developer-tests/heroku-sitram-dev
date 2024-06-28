<?php

/**
 * EasyPost Carrier Services and subservices
 */
return array(

	// Domestic & International

	'USPS'        => array(

		// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.

		'services' => array(

			'FirstPackage'                          => 'First-Class Package (USPS)',

			'First'                                 => 'First-Class Mail (USPS)',

			'Priority'                              => 'Priority Mail&#0174; (USPS)',

			'Express'                               => 'Priority Mail Express&#8482; (USPS)',

			'ParcelSelect'                          => 'USPS Parcel Select (USPS)',

			'LibraryMail'                           => 'Library Mail Parcel (USPS)',

			'MediaMail'                             => 'Media Mail Parcel (USPS)',

			'CriticalMail'                          => 'USPS Critical Mail (USPS)',

			'FirstClassMailInternational'           => 'First Class Mail International (USPS)',

			'FirstClassPackageInternationalService' => 'First Class Package Service&#8482; International (USPS)',

			'PriorityMailInternational'             => 'Priority Mail International&#0174; (USPS)',

			'ExpressMailInternational'              => 'Express Mail International (USPS)',
		),
	),
	'FedEx'       => array(

		'services' => array(

			'FIRST_OVERNIGHT'        => 'First Overnight (FedEx)',

			'PRIORITY_OVERNIGHT'     => 'Priority Overnight (FedEx)',

			'STANDARD_OVERNIGHT'     => 'Standard Overnight (FedEx)',

			'FEDEX_2_DAY_AM'         => 'FedEx 2 Day AM (FedEx)',

			'FEDEX_2_DAY'            => 'FedEx 2 Day (FedEx)',

			'FEDEX_EXPRESS_SAVER'    => 'FedEx Express Saver (FedEx)',

			'GROUND_HOME_DELIVERY'   => 'FedEx Ground Home Delivery (FedEx)',

			'FEDEX_GROUND'           => 'FedEx Ground (FedEx)',

			'FEDEX_INTERNATIONAL_PRIORITY' => 'FedEx International Priority (FedEx)',

			'INTERNATIONAL_ECONOMY'  => 'FedEx International Economy (FedEx)',

			'INTERNATIONAL_FIRST'    => 'FedEx International First (FedEx)',

			'FEDEX_INTERNATIONAL_CONNECT_PLUS' => 'FedEX International Connect Plus (FedEx)',
		),
	),
	'UPS'         => array(

		'services' => array(

			'Ground'            => 'Ground (UPS)',

			'3DaySelect'        => '3 Day Select (UPS)',

			'2ndDayAirAM'       => '2nd Day Air AM (UPS)',

			'2ndDayAir'         => '2nd Day Air (UPS)',

			'NextDayAirSaver'   => 'Next Day Air Saver (UPS)',

			'NextDayAirEarlyAM' => 'Next Day Air Early AM (UPS)',

			'NextDayAir'        => 'Next Day Air (UPS)',

			'Express'           => 'Express (UPS)',

			'Expedited'         => 'Expedited (UPS)',

			'ExpressPlus'       => 'Express Plus (UPS)',

			'UPSSaver'          => 'UPS Saver (UPS)',

			'UPSStandard'       => 'UPS Standard (UPS)',
		),
	),
	'UPSDAP'      => array(

		'services' => array(

			'Ground'            => 'Ground (UPSDAP)',

			'3DaySelect'        => '3 Day Select (UPSDAP)',

			'2ndDayAirAM'       => '2nd Day Air AM (UPSDAP)',

			'2ndDayAir'         => '2nd Day Air (UPSDAP)',

			'NextDayAirSaver'   => 'Next Day Air Saver (UPSDAP)',

			'NextDayAirEarlyAM' => 'Next Day Air Early AM (UPSDAP)',

			'NextDayAir'        => 'Next Day Air (UPSDAP)',

			'Express'           => 'Express (UPSDAP)',

			'Expedited'         => 'Expedited (UPSDAP)',

			'ExpressPlus'       => 'Express Plus (UPSDAP)',

			'UPSSaver'          => 'UPS Saver (UPSDAP)',

			'UPSStandard'       => 'UPS Standard (UPSDAP)',
		),
	),
	'UPSSurePost' => array(

		'services' => array(

			'SurePostOver1Lb'            => 'SurePost Over1Lb (UPSSurePost)',

			'SurePostUnder1Lb'           => 'SurePost Under1Lb (UPSSurePost)',

			'SurePostBoundPrintedMatter' => 'SurePost Bound Printed Matter (UPSSurePost) ',

			'SurePostMedia'              => 'SurePost Media (UPSSurePost)',

		),
	),
	'CanadaPost'  => array(

		'services' => array(

			'ExpeditedParcel'                 => 'Expedited Parcel (CanadaPost)',

			'Priority'                        => 'Priority (CanadaPost)',

			'RegularParcel'                   => 'Regular Parcel (CanadaPost)',

			'Xpresspost'                      => 'Xpresspost (CanadaPost)',

			'ExpeditedParcelUSA'              => 'Expedited Parcel USA (CanadaPost)',

			'PriorityWorldwideParcelUSA'      => 'Priority Worldwide Parcel USA (CanadaPost)',

			'PriorityWorldwidePakUSA'         => 'Priority Worldwide Pak USA (CanadaPost)',

			'PriorityWorldwideEnvelopeIntl'   => 'Priority Worldwide Envelope Intl (CanadaPost)',

			'SmallPacketUSAAir'               => 'Small Packet USA Air (CanadaPost)',

			'TrackedPacketUSA'                => 'Tracked Packet USA (CanadaPost)',

			'XpresspostUSA'                   => 'Xpresspost USA (CanadaPost)',

			'PriorityWorldwidePakIntl'        => 'Priority Worldwide Pak Intl (CanadaPost)',

			'InternationalParcelSurface'      => 'International Parcel Surface (CanadaPost)',

			'PriorityWorldwideParcelIntl'     => 'Priority Worldwide Parcel Intl (CanadaPost)',

			'SmallPacketInternationalSurface' => 'Small Packet International Surface (CanadaPost)',

			'SmallPacketInternationalAir'     => 'Small Packet International Air (CanadaPost)',

			'TrackedPacketInternational'      => 'Tracked Packet International (CanadaPost)',

			'XpresspostInternational'         => 'Xpresspost International (CanadaPost)',

		),
	),
);

