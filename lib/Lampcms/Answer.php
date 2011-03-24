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
 *
 * Class represents on item
 * from the ANSWERS collection
 *
 * @author Dmitri Snytkine
 *
 */
class Answer extends MongoDoc implements Interfaces\Answer, Interfaces\UpDownRatable, Interfaces\CommentedResource
{

	public function __construct(Registry $oRegistry, array $a = array()){

		parent::__construct($oRegistry, 'ANSWERS', $a);
	}


	/**
	 * (non-PHPdoc)
	 * @see LampcmsResourceInterface::getResourceTypeId()
	 */
	public function getResourceTypeId(){

		return 'ANSWER';
	}

	/**
	 * Set value of 'accepted' to true
	 * and update i_lm_ts
	 *
	 * @return object $this
	 */
	public function setAccepted(){
		$this->offsetSet('accepted', true);
		$this->touch();

		return $this;
	}

	/**
	 * Set value of 'accepted' to false
	 * and update i_lm_ts
	 *
	 * @return object $this
	 */
	public function unsetAccepted(){
		$this->offsetSet('accepted', false);
		$this->touch();

		return $this;
	}


	/**
	 * (non-PHPdoc)
	 * @see ResourceInterface::getResourceId()
	 */
	public function getResourceId(){

		return $this->offsetGet('_id');
	}


	/**
	 * (non-PHPdoc)
	 * @see LampcmsResourceInterface::getDeletedTime()
	 */
	public function getDeletedTime(){

		return $this->offsetGet('i_del_ts');
	}


	/**
	 * Get username of answerer
	 *
	 * @return string
	 */
	public function getUsername(){
		return $this->offsetGet('username');
	}

	/**
	 *
	 * Mark this item as deleted but only
	 * if not already marked as deleted
	 *
	 *
	 * @param object User $user user marking this
	 * item as deleted
	 *
	 * @param string $reason optional reason for delete
	 *
	 * @return object $this
	 */
	public function setDeleted(User $user, $reason = null){
		if(0 === $this->getDeletedTime()){
			$this->offsetSet('i_del_ts', time());
			$this->offsetSet('a_deleted',
			array(
			'username' => $user->getDisplayName(),
			'i_uid' => $user->getUid(),
			'av' => $user->getAvatarSrc(),
			'reason' => $reason,
			'hts' => date('F j, Y g:i a T')
			)
			);

			$this->touch();
		}

		return $this;
	}


	/**
	 *
	 * Adds a_edited array of data to Question
	 *
	 * @param User $user
	 * @param string $reason reason for editing
	 *
	 * @return object $this
	 */
	public function setEdited(User $user, $reason = ''){

		$aEdited = $this->offsetGet('a_edited');
		if(empty($aEdited) || !is_array($aEdited)){
			$aEdited = array();
		}

		$aEdited[] = array(
			'username' => $user->getDisplayName(),
			'i_uid' => $user->getUid(),
			'av' => $user->getAvatarSrc(),
			'reason' => $reason,
			'hts' => date('F j, Y g:i a T'));

		$this->offsetSet('a_edited', $aEdited);

		$this->touch();

		return $this;
	}


	/**
	 * (non-PHPdoc)
	 * @see LampcmsResourceInterface::getOwnerId()
	 */
	public function getOwnerId(){

		return $this->offsetGet('i_uid');
	}


	/**
	 * (non-PHPdoc)
	 * @see LampcmsResourceInterface::getLastModified()
	 */
	public function getLastModified(){

		return $this->offsetGet('i_lm_ts');
	}


	/**
	 * Updates last modified timestamp
	 * A replacement for updateLastModified() method
	 *
	 * @return object $this
	 */
	public function touch(){
		$this->offsetSet('i_lm_ts', time());

		return $this;
	}


	/**
	 * (non-PHPdoc)
	 * @see UpDownRatable::addUpVote()
	 */
	public function addUpVote($inc = 1){

		if(1 !== $inc && (-1 !== $inc)){
			throw \InvalidArgumentException('$inc can only be 1 or -1. Was: '.$inc);
		}

		$tmp = (int)$this->offsetGet('i_up');
		$score = (int)$this->offsetGet('i_votes');
		$total = ($score + $inc);

		$this->offsetSet('i_up',  max(0, ($tmp + $inc)) );
		$this->offsetSet('i_votes',  $total );

		/**
		 * Plural extension handling
		 */
		$v_s = (1 === abs($total) ) ? '' : 's';
		$this->offsetSet('v_s', $v_s);

		return $this;
	}


	/**
	 * (non-PHPdoc)
	 * @see UpDownRatable::addDownVote()
	 */
	public function addDownVote($inc = 1){

		if(1 !== $inc && (-1 !== $inc)){
			throw \InvalidArgumentException('$inc can only be 1 or -1. Was: '.$inc);
		}

		$tmp = (int)$this->offsetGet('i_down');
		$score = (int)$this->offsetGet('i_votes');
		$total = ($score - $inc);

		$this->offsetSet('i_down', max(0, ($tmp + $inc)) );
		$this->offsetSet('i_votes',  $total);

		/**
		 * Plural extension handling
		 */
		$v_s = (1 === abs($total) ) ? '' : 's';
		$this->offsetSet('v_s', $v_s);

		return $this;
	}


	/**
	 * (non-PHPdoc)
	 * @see UpDownRatable::getVotesArray()
	 */
	public function getVotesArray(){

		$a = array(
		'up' => $this->offsetGet('i_up'), 
		'down' => $this->offsetGet('i_down'),
		'score' => $this->offsetGet('i_votes'));

		return $a;
	}


	/**
	 * (non-PHPdoc)
	 * @see UpDownRatable::getScore()
	 */
	public function getScore(){

		return $this->offsetGet('i_votes');
	}

	/**
	 * Get full (absolute) url for this question,
	 * including the http and our domain
	 * add the #answer to the url, but this has challenges
	 * with pagination and some challanges with url rewrite rules
	 *
	 * For example if this answer is not of the first page of the question
	 * then the # anchor will not point to valid answer.
	 * It's not easy to determine on which page this answer is (currently)
	 *
	 * @return string url for this question
	 */
	public function getUrl(){

		return $this->oRegistry->Ini->SITE_URL.'/q'.$this->offsetGet('i_qid').'/#ans'.$this->offsetGet('_id');
	}


	/**
	 * (non-PHPdoc)
	 * @see Lampcms\Interfaces.CommentedResource::addComment()
	 */
	public function addComment(CommentParser $oComment){
		$aKeys = array('_id', 'i_uid', 'i_prnt', 'username', 'avtr', 'b_owner', 'b', 't', 'ts');

		$aComments = $this->getComments();
		d('aComments: '.print_r($aComments, 1));
		/**
		 * Only keep the keys that we need
		 * get rid of keys like hash, i_res
		 * because we don't need them here
		 */
		$aComment = $oComment->getArrayCopy();
		$aComment = array_intersect_key($aComment, array_flip($aKeys));

		$aComments[] = $aComment;

		$this->offsetSet('comments', $aComments);
		$this->increaseCommentsCount();

		return $this;

	}


	/**
	 * (non-PHPdoc)
	 * @see Lampcms\Interfaces.CommentedResource::getCommentsCount()
	 */
	public function getCommentsCount(){
		$aComments = $this->getComments();

		return count($aComments);
	}


	/**
	 *
	 * Enter description here ...
	 */
	public function increaseCommentsCount(){
		/**
		 * Now increase comments count
		 */
		$commentsCount = $this->getCommentsCount();
		d('$commentsCount '.$commentsCount);

		$this->offsetSet('i_comments', ($commentsCount + 1) );

		return $this;
	}


	/**
	 * Remove one comment from array of comments
	 * then re-save the new array of comments
	 * the numerical keys of array will be reset
	 * Also i_comments value will be updated to the
	 * new count of comments
	 *
	 * (non-PHPdoc)
	 * @see Lampcms\Interfaces.CommentedResource::deleteComment()
	 */
	public function deleteComment($id){

		if(!$this->checkOffset('comments')){
			e('This question does not have any comments');

			return $this;
		}

		$aComments = $this->offsetGet('comments');

		for($i = 0; $i<count($aComments); $i+=1){
			if($aComments[$i]['_id'] == $id){
				d('unsetting comment: '.$i);
				array_splice($aComments, $i, 1);
				break;
			}
		}

		$newCount = count($aComments);
		if( 0 === $newCount){
			$this->offsetUnset('comments');
		} else {
			$this->offsetSet('comments', $aComments);
		}

		$this->offsetSet('i_comments', $newCount );

		return $this;
	}


	/**
	 * Getter for 'comments' element
	 * @return array of comments or empty array if
	 * 'comments' element not present in the object
	 *
	 */
	public function getComments(){
		return $this->getFallback('comments', array());
	}

	
	/**
	 * Get id of question for this answer
	 *
	 * @return int id of question for which this is an answer
	 */
	public function getQuestionId(){
		return (int)$this->offsetGet('i_qid');
	}
	
	
	/**
	 * Get uid of user who asked the question
	 * for which this is the answer
	 * This is useful during adding a comment
	 * to an asnwer where we need to know wheather of not
	 * the comment comes from the original asker.
	 *
	 * @return int id of question owner
	 */
	public function getQuestionOwnerId(){
		return (int)$this->offsetGet('i_quid');
	}

}
