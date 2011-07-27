<?php
/**
 * Elastica tools
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Oleg Zinchenko <olegz@default-value.com>
 */
class Elastica_Util
{
	/**
	 * Replace the following reserved words: AND OR NOT
	 * and
	 * escapes the following terms: + - && || ! ( ) { } [ ] ^ " ~ * ? : \
	 *
	 * @param string $term Query term to replare and escape
	 * @return string Replaced and escaped query term
	 * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Boolean%20operators
	 * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Escaping%20Special%20Characters
	 */
	public static function replaceBooleanWordsAndEscapeTerm($term) {

		$result = $term;
		$result = self::replaceBooleanWords($result);
		$result = self::escapeTerm($result);

		return $result;
	}

	/**
	 * Escapes the following terms (because part of the query language)
	 * + - && || ! ( ) { } [ ] ^ " ~ * ? : \
	 *
	 * @param string $term Query term to escape
	 * @return string Escaped query term
	 * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Escaping%20Special%20Characters
	 */
	public static function escapeTerm($term) {

		$result = $term;
		// \ escaping has to be first, otherwise escaped later once again
		$chars = array('\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':');

		foreach ($chars as $char) {
			$result = str_replace($char, '\\' . $char, $result);
		}

		return $result;
	}

	/**
	 * Replace the following reserved words (because part of the query language)
	 * AND OR NOT
	 *
	 * @param string $term Query term to replace
	 * @return string Replaced query term
	 * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Boolean%20operators
	 */
	public static function replaceBooleanWords($term) {

		$replacementMap = array('AND'=>'&&', 'OR'=>'||', 'NOT'=>'!');
		$result = strtr($term, $replacementMap);

		return $result;
	}
}
