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
 *    the website\'s Questions/Answers functionality is powered by lampcms.com
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


namespace Lampcms\Controllers;


use \Lampcms\WebPage;
use \Lampcms\Question;
use \Lampcms\Request;
use \Lampcms\Responder;

/**
 *
 * This contoller received the
 * "Close" form from the question
 * View and then depending on permission
 * of Viewer will either mark question
 * as closed or send request to close question
 * to Moderators
 *
 * @author Dmitri Snytkine
 *
 */
class Close extends WebPage
{
	protected $membersOnly = true;

	protected $requireToken = true;

	protected $bRequirePost = true;

	protected $aRequired = array('qid', 'reason');

	protected $oQuestion;

	protected $requested = false;

	/**
	 *
	 * Subject of email sent
	 * to moderators
	 *  @todo translate string
	 */
	const SUBJECT = 'Request to close question';

	/**
	 *
	 * Body of email sent to moderators
	 * when request to delete is made

	 * @todo stranslate string
	 */
	const EMAIL_BODY = '
	User: %1$s
	Profile: %2$s
	
	Requesting to close question: %3$s
	
	Title: %4$s
	
	Intro: %5$s
	
	Reason: %6$s
	
	Note: %7$s
	
	';


	protected function main(){

		$this->getQuestion()
		->setClosed()
		->returnResult();
	}


	protected function getQuestion(){
		$a = $this->oRegistry->Mongo->QUESTIONS->findOne(
		array(
		'_id' => $this->oRequest['qid'])
		);

		if(empty($a)){
			throw new \Lampcms\Exception('Question not found');
		}

		$this->oQuestion = new Question($this->oRegistry, $a);

		return $this;
	}


	/**
	 *
	 * If moderator then close it,
	 * if Not moderator then if owner - request closing
	 * of question
	 *
	 * @return object $this
	 */
	protected function setClosed(){
		try{
			$this->checkAccessPermission('close_question');
		} catch (\Lampcms\AccessException $e){
			if(!\Lampcms\isOwner($this->oRegistry->Viewer, $this->oQuestion)){
				throw $e;
			}

			return $this->requestClose();
		}

		$reason = $this->oRequest['reason'].'. '.$this->oRequest['note'];
		d('reason: '.$reason);

		$this->oQuestion->setClosed($this->oRegistry->Viewer, $reason);
		$this->oRegistry->Dispatcher->post($this->oQuestion, 'onQuestionClosed');

		return $this;
	}


	/**
	 * Send our emails to moderators requesting
	 * to close this question
	 *
	 * @return object $this
	 */
	protected function requestClose(){
		$cur = $this->oRegistry->Mongo->USERS->find(array(
  			'role' => array('$in' => array('moderator', 'administrator'))
		), array('email'));

		d('found '.$cur->count().' moderators');

		if($cur && $cur->count() > 0){
			$aModerators = iterator_to_array($cur, false);
			d('$aModerators '.print_r($aModerators, 1));
			$Mailer = \Lampcms\Mailer::factory($this->oRegistry);
			$body = $this->makeBody();
			$Mailer->mail($aModerators, self::SUBJECT, $body);
		}

		$this->requested = true;

		return $this;
	}


	/**
	 * Make body of the email
	 * which will be sent to moderators
	 *
	 * @return string body of email
	 */
	protected function makeBody(){
		$vars = array(
		$this->oRegistry->Viewer->getDisplayName(),
		$this->oRegistry->Ini->SITE_URL.$this->oRegistry->Viewer->getProfileUrl(),
		$this->oQuestion->getUrl(),
		$this->oQuestion['title'],
		$this->oQuestion['intro'],
		$this->oRequest['reason'],
		$this->oRequest['note']
		);

		d('vars: '.print_r($vars, 1));

		$body = vsprintf(self::EMAIL_BODY, $vars);

		d('body '.$body);

		return $body;
	}


	protected function returnResult(){
		/**
		 * @todo translate string
		 */
		$message = 'Question closed';
		$requested = 'A request to close
		this question has been sent to moderators<br>
		The final decision about closing the question or leaving it open will be up to moderators';


		if(Request::isAjax()){
			$res = (!$this->requested) ? $message : $requested;
			$ret = array('alert' => $res);
			/**
			 * If item was actually deleted then
			 * add 'reload' => 2 to return
			 * which will cause page reload
			 * in 1.5 seconds.
			 */
			if(!$this->requested){
				$ret['reload'] = 1500;
			}


			Responder::sendJSON($ret);
		}

		Responder::redirectToPage($this->oResource->getUrl());
	}

}
