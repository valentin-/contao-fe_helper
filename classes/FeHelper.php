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


/**
 * Namespace
 */
namespace Vale\Contao;


/**
 * Class FeHelper
 *
 * @copyright  Valentin Sampl
 * @author     Valentin Sampl
 * @package    Devtools
 */
class FeHelper extends \Controller
{


	public function generateFeHelper()
	{
		global $objPage;


		if (!$permissions = static::checkLogin()) {
			return;
		}

		$pageTree = array();


		// $favourites = array(48,49);

		// $arrPages = array_unique(array_merge((array)$objPage->id, $favourites));

		$arrPages = array($objPage->id);

		foreach ($arrPages as $p) {
			$page = \PageModel::findByPk($p);
			$pageTree[$page->id] = static::generatePage($page);

			unset($_SESSION['fe_helper']['article_count']);
			$objArticles = \ArticleModel::findPublishedByPid($page->id);

			if($objArticles) {
				foreach ($objArticles as $article) {
					$pageTree[$page->id]['articles'][$article->id] = static::generateArticle($article);

					$objContents = \ContentModel::findPublishedByPidAndTable($article->id, 'tl_article');

					$contentCount = 1;
					foreach ($objContents as $content) {
						$pageTree[$page->id]['articles'][$article->id]['contents'][$content->id] = static::generateContent($content);
						$pageTree[$page->id]['articles'][$article->id]['contents'][$content->id]['count'] = $contentCount; 
						$contentCount++;
					}

				}
			}
		}

		$beLinks = array();

		$pageDetails = $this->getPageDetails($objPage->id);
		$objLayout = \LayoutModel::findByPk($pageDetails->layout);
		$objTheme = $objLayout->getRelated('pid');

		if($permissions) {
			foreach ($permissions as $permission) {

				switch ($permission) {
					case 'tl_module':
						$link = static::getBackendURL('themes', 'tl_module', $objTheme->id, null);
						break;
					case 'page':
						$link = static::getBackendURL('page', '', '', null);
						break;
					case 'article':
						$link = static::getBackendURL('article', '', '', null);
						break;
					case 'files':
						$link = static::getBackendURL('files', '', '', null);
						break;
					case 'layout':
						$link = static::getBackendURL('themes', 'tl_layout', $objLayout->id);
						break;
					case 'settings':
						$link = static::getBackendURL('settings', '', '', null);
						break;
					default:
						$link = '';
						break;
				}

				if($link) {
					$beLinks[$permission]['link'] = static::generateLink(static::getModuleTitle($permission),$link);
				}
			}
		}

		$GLOBALS['TL_JAVASCRIPT'][] = 'assets/jquery/core/' . $GLOBALS['TL_ASSETS']['JQUERY'] . '/jquery.min.js';
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/fe_helper/assets/js/noconflict.js';
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/fe_helper/assets/js/fe_helper.js';
		$GLOBALS['TL_HEAD'][] = '<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:400,700">';
		$GLOBALS['TL_CSS'][] = 'system/modules/fe_helper/assets/css/fe_helper.css';

		$objTemplate = new \FrontendTemplate('fe_helper');
		$objTemplate->pageTree = $pageTree;
		$objTemplate->beLinks = $beLinks;
		$html = $objTemplate->parse();

		$GLOBALS['TL_JQUERY'][] =
<<<JAVASCRIPT
	<script type="text/javascript">
	jQuery(function($){
		$('body').append('$html')
	})
	</script>
JAVASCRIPT;

		// echo '<pre>';
		// print_r($objPage);
		// echo '</pre>';

		// echo '<pre>';
		// print_r($objArticles);
		// echo '</pre>';

		// echo '<pre>';
		// print_r($permissions);
		// echo '</pre>';

	}

	protected static function generatePage($objPage) {

		$name = sprintf('%s', $objPage->title);
		$url = static::getBackendURL('page', null, $objPage->id);
		$link = static::generateLink($name,$url);

		return array(
			'name' => $name,
			'url' => $url,
			'link' => $link
		);
	}

	protected static function generateArticle($objArticle) {

		\System::loadLanguageFile('tl_article');

		$name = sprintf('%s (%s)', $objArticle->title, $GLOBALS['TL_LANG']['tl_article'][$objArticle->inColumn]);
		$url = static::getBackendURL('article', 'tl_content', $objArticle->id, null);
		$_SESSION['fe_helper']['article_count'][$objArticle->inColumn]++;
		$attributes[] = 'data-column="'.$objArticle->inColumn.'"';
		$attributes[] = 'data-article-index="'.$_SESSION['fe_helper']['article_count'][$objArticle->inColumn].'"';
		$link = static::generateLink($name,$url,true,$attributes);

		return array(
			'name' => $name,
			'url' => $url,
			'link' => $link
		);
	}

	protected static function generateContent($objContent) {

		\System::loadLanguageFile('tl_content');
		\System::loadLanguageFile('fe_helper');

		$title = $objContent->type;

		if($GLOBALS['TL_LANG']['CTE'][$objContent->type][0]) {
			$title = $GLOBALS['TL_LANG']['CTE'][$objContent->type][0];
		} elseif($GLOBALS['TL_LANG']['tl_content'][$objContent->type][0]) {
			$title = $GLOBALS['TL_LANG']['tl_content'][$objContent->type][0];
		}
		// $title = $GLOBALS['TL_LANG']['tl_content'][$objContent->type][0] ? $GLOBALS['TL_LANG']['tl_content'][$objContent->type][0] : $objContent->type;

		if($objContent->type == 'module') {
			$objModule = \ModuleModel::findByPk($objContent->module);
			$title .= ': '.$objModule->name;

		} elseif($objContent->type == 'article') {
			$objArticle = \ArticleModel::findByPk($objContent->articleAlias);
			$title .= ': '.$objArticle->title;
		}

		$name = sprintf('%s', $title);
		$url = static::getBackendURL('article', 'tl_content', $objContent->id);
		$link = static::generateLink($name,$url);

		return array(
			'name' => $name,
			'url' => $url,
			'link' => $link,
			'newslist' => static::getNewsList($objModule),
			'news' => static::getNewsDetail($objModule)
		);
	}

	protected static function getNewsList($objModule) {

		if(!$objModule) {
			return;
		}

		if($objModule->type == 'newslist') {
			$archives = deserialize($objModule->news_archives);

			if(is_array($archives)) {
				$objNews = \NewsModel::findPublishedByPids($archives);

				$arrOptions = array();
				if($objNews) {
					foreach ($objNews as $news) {
						$url = static::getBackendURL('news', 'tl_content', $news->id, null);
						$arrOptions[$url] = $news->headline;
					}

					$field = 'fe_helper_newslist';

					$arrData = array(
						'label' => &$GLOBALS['TL_LANG']['fe_helper'][$field],
						'inputType' => 'select',
						'options' => $arrOptions,
						'eval' => array(
							'tableless'=>true,
							'includeBlankOption'=>true,
							'blankOptionLabel'=>&$GLOBALS['TL_LANG']['fe_helper']['newslist_blankoption'],
							'onchange'=>'fe_helper_select(this)'
						), 
					);

					$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
					$varValue = $_SESSION['isotope_export'][$field];
					$objWidget = new $strClass($strClass::getAttributesFromDca($arrData, $field, $varValue, '', '', $this));

					return $objWidget->parse();
				}
			}
		}
	}

	protected static function getNewsDetail($objModule) {

		if(!$objModule) {
			return;
		}

		$objNews = \NewsModel::findByAlias(\Input::get('items'));

		$title = $GLOBALS['TL_LANG']['fe_helper']['news'].': '.$objNews->headline;

		if($objNews) {
			return static::generateLink($title,static::getBackendURL('news', 'tl_content', $objNews->id, null));
		}

	}


	protected static function generateLink($name, $url, $blank = true, $attributes = array()) {

		$link = '<a href="'.$url.'"';
		if($blank) {
			$link .= ' target="_blank"';
		} 
		if($attributes) {
			$link .= implode(' ', $attributes);
		}
		$link .= '>'.$name.'</a>';

		return $link;
	}

	protected static function getModuleTitle($module) {
		\System::loadLanguageFile('modules');

		if(is_array($GLOBALS['TL_LANG']['MOD'][$module])) {
			return $GLOBALS['TL_LANG']['MOD'][$module][0];
		}
		if($GLOBALS['TL_LANG']['MOD'][$module]) {
			return $GLOBALS['TL_LANG']['MOD'][$module];
		}
		return $module;

	}

	/**
 	 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
	 * create backend edit URL
	 *
	 * @param  string $do
	 * @param  string $table
	 * @param  string $id
	 * @param  string $act
	 * @return string
	 */
	protected static function getBackendURL($do, $table, $id, $act = 'edit', array $params = array())
	{

		if(is_dir('system/modules/rico_pagemanager')) {
			if($do == 'page') $do = 'rico_pagemanager';
			if($do == 'article') $do = 'rico_pagemanager';
		}

		return \Environment::getInstance()->url.'/contao/main.php'
			. '?do=' . $do
			. ($table ? '&table=' . $table : '')
			. ($act ? '&act=' . $act : '')
			. ($id ? '&id=' .  $id : '')
			. (count($params) ? '&' . http_build_query($params) : '')
			. '&rt=' . REQUEST_TOKEN;
	}


	/**
 	 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
	 * checks if a Backend User is logged in
	 *
	 * @return array|boolean false if the user isn't logged in otherwise the permissions array
	 */
	public static function checkLogin()
	{
		// Do not create a user instance if there is no authentication cookie
		if (!\Input::cookie('BE_USER_AUTH') || TL_MODE !== 'FE') {
			return false;
		}

		$User = FrontendHelperUser::getInstance();

		if (!$User->authenticate()) {
			return false;
		}

		if (!$User->use_fe_helper) {
			return false;
		}

		if ($User->isAdmin) {
			return array('page', 'article', 'tl_module', 'files', 'layout', 'settings');
		}

		$permissions = array();

		if ($User->hasAccess('tpl_editor', 'modules')) {
			$permissions[] = 'tpl_editor';
		}

		if (count($permissions)) {
			return $permissions;
		}

		return false;
	}
}
