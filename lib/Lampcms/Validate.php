<?php
/**
 *
 * License, TERMS and CONDITIONS
 *
 * This software is lisensed under the GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * Please read the license here : http://www.gnu.org/licenses/lgpl-3.0.txt
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * ATTRIBUTION REQUIRED
 * 4. All web pages generated by the use of this software, or at least
 * 	  the page that lists the recent questions (usually home page) must include
 *    a link to the http://www.lampcms.com and text of the link must indicate that
 *    the website's Questions/Answers functionality is powered by lampcms.com
 *    An example of acceptable link would be "Powered by <a href="http://www.lampcms.com">LampCMS</a>"
 *    The location of the link is not important, it can be in the footer of the page
 *    but it must not be hidden by style attibutes
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This product includes GeoLite data created by MaxMind,
 *  available from http://www.maxmind.com/
 *
 *
 * @author     Dmitri Snytkine <cms@lampcms.com>
 * @copyright  2005-2011 (or current year) ExamNotes.net inc.
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * @link       http://www.lampcms.com   Lampcms.com project
 * @version    Release: @package_version@
 *
 *
 */


namespace Lampcms;

/**
 * Class contains static validator functions
 *
 * @author Dmitri Snytkine
 *
 */
class Validate
{

	/**
	 * Validate form token against
	 * Session token
	 *
	 * @throws Lampcms\Exception on error
	 */
	public static function validateToken(){
		$token = $_REQUEST['token'];
		if(empty($token)){
			throw new Exception('Form token not found');
		}

		if(!isset($_SESSION) || empty($_SESSION['token'])){
			throw new Exception('Session token not set');
		}

		if($_SESSION['token'] !== $token){
			throw new Exception('Unable to validate form security token');
		}

		return true;
	}

	
	/**
	 * Validates a string so that it can contain only
	 * alphanumeric and hyphens '-' but hyphens
	 * cannot be the first or last char
	 * basically this is so that it can be a valid domain name
	 * it also checks that string must be between
	 * 3 and 20 chars long
	 *
	 * Validation Conditions:
	 * find one alphanumeric char, then 1-18 alphanumeric or -,
	 * then at least one alphanumeric
	 *
	 * The A modifier means start from beginning of line,
	 * otherwise the string like user+.something will validate
	 *
	 * Must return false if BAD true if GOOD
	 *
	 * @return bool true if validates false if validation fails
	 *
	 * @param string $string
	 */
	public static function username($string){
		d('$string: '.$string);
		$ret = (0 !== \preg_match('/([a-zA-Z0-9@])([a-zA-Z0-9\-]{1,18})([a-zA-Z0-9])$/A', $string, $m));

		d('ret '.$ret);

		return $ret;
	}

	
	/**
	 * Verifies that password contains
	 * at least one letter and at least one number
	 * and is at least 6 chars long
	 *
	 * @param string $pwd password to validate
	 *
	 * @return bool true if validation passes, false otherwise
	 */
	public static function enforcePwd($pwd){
		$res = preg_match('/[a-zA-Z]+/', $pwd, $matches);
		$res2 = preg_match('/\d+/', $pwd);

		d('$res: '.$res.' $res2 '.$res2);

		if ( (strlen($pwd) < 6) || (0 === $res) || (0 === $res2)) {
			d('failed to validate password');

			return false;
		}

		return true;
	}


	public static function email($email){

		if (false === \filter_var($email, FILTER_VALIDATE_EMAIL)) {

			return false;
		}

		$a = explode('@',$email);
		$domain = $a[1];
		d('domain: '.$domain);

		return (true === \checkdnsrr($domain, 'MX')  || true === \checkdnsrr($domain, 'A'));
	}


	/**
	 * Validate string DOB (Date of Birth)
	 * It supposed to follow this date format: YYYY/MM/DD
	 *
	 * @param string $string
	 *
	 * @return bool true if string is in valid YYYY/MM/DD format
	 * and the actual values of each part of string make sense
	 */
	public static function validateDob($string){
		$a = explode('/', $string);
		if(count($a) !== 3){
			d('invalid format '.$string);
				
			return false;
		}

		if(!is_numeric($a[0]) || !is_numeric($a[1]) || !is_numeric($a[2])){
			d('not numeric element in string '.$string);
			return false;
		}

		if((int)$a[0] < 1900){
			d('DOB too old '.$string);
			return false;
		}

		if ((date('Y') - (int)$a[0]) < 0){
			d('year from future: '.$string);
			return false;
		}

		if((int)$a[1] > 12 || (int)$a[1] < 1){
			d('month invalid month'.$string);
			return false;
		}

		if((int)$a[2] > 31 || (int)$a[2] < 1){
			d('day invalid day '.$string);
			return false;
		}

		return true;
	}

	
	/**
	 * tests a value for a specific type
	 *
	 * @param mixed $val any type of value like object, string, boolean, resource, etc.
	 * @param mixed string | array $type type of value that $var must be in order to satisfy the test
	 * @param string $strFunction function or method name that called this validator
	 *
	 * @return bool true
	 *
	 * @throws LampcmsDevException is variable type does not match the test condition
	 * also throws exception is test condition is not supported by the php is_ test
	 * or if either $val or $type are empty
	 */
	public final static function type($val, $type){

		$arrTypes = array(
							 'array',
                             'bool',
                             'float',
                             'int',
                             'integer',
                             'null',
                             'numeric',
                             'object',
                             'resource',
                             'string',
                             'unicode',
                             'buffer',
                             'scalar');

	

		/**
		 * Special case: for a resource we can validate
		 * the resource type
		 *
		 * In this case the strType should be an array with
		 * value being a string: name of resource type
		 * and a key must just be 'resource'
		 */
		if(is_array($type)){
			d('$type array: '.print_r($type, true));

			if(count($type) !== 1){
				throw new DevException('in $type is array it MUST have just one element');
			}

			$aType = $type;

			foreach($aType as $name => $restype){
				$type = (string)$name;
				$resourceType = (string)$restype;
			}
		}

		$type = \strtolower($type);
		d('$type: '.$type);

		if (!in_array($type, $arrTypes)) {
			throw new DevException($type.' is not one of the allowed values');
		}
		
		$strFn = 'is_'.$type;
		if (true !== $strFn ($val)) {

			throw new Exception('wrong type');
		}

		if(isset($resourceType)){
			if($resourceType !== $actualType = get_resource_type($val)){
				d( 'looking for type: '.$resourceType.'$actualType: '.$actualType);
				throw new DevException('Invalid resource type. Expected resource of type: '.$resourceType. ' got: '.$actualType);
			}
		}

		return true;
	}

}
