<?php if ( !defined( 'ABSPATH' ) ) exit;

function wcpl_getBankTransferPaymentTypes()
{
	$result = array(
		'AB' => array(
			'label' => 'Alior Bank'
		),
		'AS' => array(
			'label' => 'T-Mobile Usługi Bankowe'
		),
		'MT' => array(
			'label' => 'mTransfer'
		),
		'IN' => array(
			'label' => 'Inteligo'
		),
		'IP' => array(
			'label' => 'iPKO'
		),
		'MI' => array(
			'label' => 'Millenium'
		),
		'CA' => array(
			'label' => 'Credit Agricole'
		),
		'PP' => array(
			'label' => 'Poczta Polska'
		),
		'PCZ' => array(
			'label' => 'Bank Pocztowy'
		),
		'IB' => array(
			'label' => 'Idea Bank'
		),
		'PO' => array(
			'label' => 'Pekao S.A.'
		),
		'GB' => array(
			'label' => 'Getin Bank'
		),
		'IG' => array(
			'label' => 'ING Bank Śląski'
		),
		'WB' => array(
			'label' => 'Santander Bank'
		),
		'PB' => array(
			'label' => 'Bank BGŻ BNP PARIBAS'
		),
		'CT' => array(
			'label' => 'Citi'
		),
		'PL' => array(
			'label' => 'Plus Bank'
		),
		'NP' => array(
			'label' => 'Noble Pay'
		),
		'BS' => array(
			'label' => 'Bank Spółdzielczy'
		),
		'NB' => array(
			'label' => 'NestBank'
		),
		'PBS' => array(
			'label' => 'Podkarpacki Bank Spółdzielczy'
		),
		'SGB' => array(
			'label' => 'Spółdzielcza Grupa Bankowa'
		),
//		'BP' => array(
//			'label' => 'Bank BPH'
//		),
		'OH' => array(
			'label' => 'Other bank'
		),
		'BLIK' => array(
			'label' => 'BLIK'
		),
	);

	return $result;
}
