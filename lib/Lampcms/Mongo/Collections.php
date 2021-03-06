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

namespace Lampcms\Mongo;

/**
 * This is not a class!
 *
 * The purpose of this file
 * is to define namespace-specific constants
 * that hold names of Mongo Collections
 * use in this project
 * as well as to provide some basic
 * documentation about these collections
 * via docblocks
 *
 * By using these constants instead of directly
 * using the names of collections developers have
 * a way of preventing inadvertantly
 * misspeling of a collection name
 *
 * If you designing a custom module that will be
 * requiring creation of a new Mongo Collection
 * you should define that collection name in this file
 *
 * @author Dmitri Snytkine
 *
 */

/**
 * Collection holds data necessary for 
 * creating sitemaps
 */
const SITEMAP_LATEST = 'SITEMAP_LATEST';

/**
 * Collection holds records of API CLIENTS
 * CLIENTS are created by users using the apiclient
 * controller
 * Admin can suspend a Client for any reason
 * by setting the suspender flag and entering reason
 * for suspension
 * the suspension data is stored as nested array of objects
 * holding hts, reason, resolved_hts
 * the flag is either true or false
 */
const API_CLIENTS = 'API_CLIENTS';

/**
 * This collection is very important
 * as it keeps track of the next value
 * of Auto Increment counters per collection
 *
 *
 */
const Autoincrements = 'Autoincrements';

/**
 * Holds Answers to question
 * each record has i_qid with is
 * the value if _id from QUESTIONS collection
 * privary key _id is an integer
 *
 */
const ANSWERS = 'ANSWERS';

const BANNED_IP = 'BANNED_IP';

/**
 * This collection does NOT
 * hold actual comments but
 * only meta data about comments
 *
 * Important data in this collection
 * is the "coll" which is the name of collection
 * where the actual comment is stored. ANSWERS or QUESTIONS
 * actual comments are stored as nested arrays
 * in ANSWERS and QUESTIONS collection
 *
 * Having this collection makes it possible
 * to quickly find the actual comment just
 * by the comment id - just find a record, then
 * find out if this is comment for QUESTION or ANSWER,
 * the id or QUESTION or ANSWER is the value if i_res
 * and the value of i_qid is the Question ID - so if this
 * comment is for the answer we will also know the question id
 * that the answer belongs to.
 *
 * get it's parent id (if it's a reply),
 * get id of user who posted it, get timestamp of comment,
 * ip address of where it came from as well as hash - the hash
 * (md5)
 * is used to prevent duplicate comments
 */
const COMMENTS = 'COMMENTS';

const COMMENTS_LIKES = 'COMMENTS_LIKES';

const C_Cache = 'C_Cache';

/**
 * Email addresses of users are stored
 * in this collection. Since user may have more than
 * one email address, (extra email addresses),
 * this collection has values of address and
 * i_uid - the value of USER id as well
 * as flags indicating whether of not this email
 * address has gravatar of gravatars site.
 *
 * Also when new user is registered, an activation
 * code is generated and stored in this collection
 * The code is emailed to user and then we use the data
 * in this collection to validate that user has clicked
 * on the account activation link.
 * Also activation links have expiration time, so time
 * of creation of activation code is also stored here as timestamp
 */
const EMAILS = 'EMAILS';

const LOGIN_ERROR = 'LOGIN_ERROR';

/**
 * User logins are stored
 * in this collection to keep
 * track on who logged in and when
 * and what type of login it was (by cookie, by
 * external authentication like Facebook, etc...)
 *
 * It also keeps Geo location data
 * for each login as well as useragent.
 * The data can be used for datamining
 * @var unknown_type
 */
const LOGIN_LOG = 'LOGIN_LOG';

const PASSWORD_CHANGE = 'PASSWORD_CHANGE';

const QUESTIONS = 'QUESTIONS';

const QUESTION_TAGS = 'QUESTION_TAGS';

/**
 * This collection holds value
 * of qid - Question ID
 * and uid - User id of user who viewed the question
 * as well as timestamp i_ts of when the user first
 * viewed the question.
 * This is the way we enforce one view count per user.
 *
 * For anonymous viewer (not logged in) the value
 * of session_id is used instead of user id - this way
 * one view per session is counted.
 */
const QUESTION_VIEWS = 'QUESTION_VIEWS';

const RELATED_TAGS = 'RELATED_TAGS';

const REPORTED_ITEMS = 'REPORTED_ITEMS';

/**
 * Every Question and Answer
 * are a "Resource" - just have different resource type
 * Even time a new Question or Answer is created,
 * a record is created in this collection and the
 * auto-increment value is generated for it.
 * Every Question and Answer has it's privary key _id equals
 * to corresponding key in the collection.
 *
 * This collection holds values of _id and string value of type
 * which is ANSWER or QUESTION, but can possibly be other
 * types of resources if we decide to use different resource types
 * later on.
 *
 * This collection also holds timestamps (in form on MongoDate)
 * of the time of resource creation.
 *
 */
const RESOURCE = 'RESOURCE';

/**
 * When user sends Q or A to Twitter
 * the Tweet status from Twitter API
 * is stored in this collection
 */
const TWEETS = 'TWEETS';

const UNANSWERED_TAGS = 'UNANSWERED_TAGS';

const USERS = 'USERS';

const USERS_FACEBOOK = 'USERS_FACEBOOK';

const USERS_TWITTER = 'USERS_TWITTER';

const USERS_GFC = 'USERS_GFC';

const USER_TAGS = 'USER_TAGS';

/**
 * USER_REFERRER contain the url
 * from which use initially came from on his first visit
 */
const USER_REFEREF = 'USER_REFEREF';

const VOTES = 'VOTES';

const VOTE_HACKS = 'VOTE_HACKS';

/**
 * Log for API Requests
 * Will hold data like api path,
 * ip, useragent, client_id, userid
 * 
 */
const API_ACCESS = 'API_ACCESS';

/**
 * This collection records per-day
 * count of API calls per user or per-ip
 * 
 * This data is then used to enforce the API
 * daily access limit based on userID or on IP address
 * 
 */
const API_ACCESS_COUNTER = 'API_ACCESS_COUNTER';


/**
 * This is a collection used during
 * tests. This is a temporary collection which
 * is created only during run of test suite and
 * then dropped at end of test
 * Enter description here ...
 */
const MY_MONGO_TEST_COLLECTION = 'MY_MONGO_TEST_COLLECTION';

/**
 * Another test collection, only used during run of tests
 * and then dropped at end of test
 */
const MY_TEST_COLLECTION = 'MY_TEST_COLLECTION';

/**
 * Collection for storing I18N Translation strings
 * _id is language code like 'en' or 'fr'
 * also has 'label' to full name of 
 * Language - this could be in that native
 * language or in English
 * 
 * It also holds actual collection of translation
 * strings as nested array of objects
 * 
 */
const TRANSLATION = 'TRANSLATION';


