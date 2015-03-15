<?php

namespace Vale;

class ArticleModel extends \Contao\ArticleModel {

	public static function findPublishedByPid($intId, array $arrOptions=array())
	{
		$t = static::$strTable;
		$arrColumns = array("$t.pid=?");

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		$arrOptions['order'] = "$t.sorting";

		return static::findBy($arrColumns, $intId, $arrOptions);
	}
}
