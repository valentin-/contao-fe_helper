<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   fe_helper
 * @author    Valentin Sampl
 * @license   MIT
 * @copyright Valentin Sampl
 */

$GLOBALS['TL_HOOKS']['generatePage'][] = array('Vale\Contao\FeHelper', 'generateFeHelper');