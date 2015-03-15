<?php

foreach ($GLOBALS['TL_DCA']['tl_user']['palettes'] as $k => $palette) {

	if($k == '__selector__') continue;

	$GLOBALS['TL_DCA']['tl_user']['palettes'][$k] .= ';{fe_helper_legend},use_fe_helper';
}

$GLOBALS['TL_DCA']['tl_user']['fields']['use_fe_helper'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_user']['use_fe_helper'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'sql' => "char(1) NOT NULL default '1'"
);