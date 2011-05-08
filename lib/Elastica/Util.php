<?php
/**
 * Elastica tools
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Elastica_Util
{
	/**
	 * Escapes the following terms (because part of the query language)
	 * + - && || ! ( ) { } [ ] ^ " ~ * ? : \
	 *
	 * @param string $term Query term to escape
	 * @return string Escaped query term
	 * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Escaping%20Special%20Characters
	 */
	public static function escapeTerm($term) {

		// \ escaping has to be first, otherwise escaped later once again
		$chars = array('\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':');

		foreach ($chars as $char) {
			$term = str_replace($char, '\\' . $char, $term);
		}

		return $term;
	}
}
