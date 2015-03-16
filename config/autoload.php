<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Vale',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Models
	'Vale\ArticleModel'              => 'system/modules/fe_helper/models/ArticleModel.php',

	// Classes
	'Vale\Contao\FrontendHelperUser' => 'system/modules/fe_helper/classes/FrontendHelperUser.php',
	'Vale\Contao\FeHelper'           => 'system/modules/fe_helper/classes/FeHelper.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'frontend_helper' => 'system/modules/fe_helper/templates',
));
