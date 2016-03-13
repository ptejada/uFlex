<?php

namespace ptejada\uFlex;

use ptejada\uFlex\Classes\Collection;
use ptejada\uFlex\Classes\Helper;
use ptejada\uFlex\Classes\Table;
use ptejada\uFlex\Service\Cookie;
use ptejada\uFlex\Service\Session;

/**
 * All in one user object use to authenticating, registering new users and other user actions
 * Note: Either start() or login() must be called at least once on your code per User instance
 *
 * @package ptejada\uFlex
 * @author  Pablo Tejada <pablo@ptejada.com>
 */
class User extends AbstractUser
{
    /**
     * Class Version
     *
     * @var string
     */
    const VERSION = '2.0.0';
    /** @var Table - The database table object */
    public $table;
    /** @var  Session - The namespace session object */
    public $session;
    /** @var  Cookie - The cookie for autologin */
    protected $cookie;
    /** @var  self */
    protected static $instance;

    public function __construct()
    {
        parent::__construct();

        if (!(static::$instance instanceof self)) {
            static::$instance = $this;
        }

        //Get the table DB object
        $this->table = Config::getConnection()->getTable(Config::get('user.table'));

        // Create and configure the auto login Cookie
        $cookieInfo   = Config::get('cookie');
        $this->cookie = Config::getCookie()->newCookie($cookieInfo->name);
        $this->cookie->setHost($cookieInfo->host);
        $this->cookie->setPath($cookieInfo->path);
        $this->cookie->setLifetime($cookieInfo->time);
        
        // Create and configure the user session
        $this->session = Config::getSession()->newSession(Config::get('session.name'));

        // Link the session with the user data
        if (is_null($this->session->data)) {
            $this->session->data = Config::get('user.default')->toArray();
        }
        
        $this->data =& $this->session->data->toArray();
        
        // Attempts to resume previous session
        $this->resume();
    }

    /**
     * @return User
     */
    public static function getInstance()
    {
        if (static::$instance instanceof self) {
            return static::$instance;
        } else {
            return static::$instance = new self();
        }
    }

    /**
     * Resumes an existing user session
     */
    protected function resume()
    {
        //Session Login
        if ($this->session->signed) {
            $this->log->debug('User Is signed in from session');
            if ($this->session->update) {
                $this->log->debug('Updating Session from database');

                //Get User From database because its info has change during current session
                $update = $this->table->getRow(array('ID' => $this->ID, 'Activated' => 1));
                if ($update) {
                    $this->session->data = $update->toArray();

                    //Update last_login
                    $this->logLogin();

                    //Cleaning session update flag
                    unset($this->session->update);
                } else {
                    $this->logout();
                    return false;
                }
            }
            return true;
        }

        //Cookies Login
        if ($confirmation = $this->cookie->getValue()) {
            $this->log->debug('Attempting Login with cookies');
            // TODO: implement Cookie auto login feature
        }
        
        return false;
    }
    
    /**
     * Restore the a user session or Login a with given credentials.
     *
     * @api
     *
     * @param string $identifier - Username or Email
     * @param string $password   - Clear text password
     * @param bool   $autoLogin  - Flag whether to remember the user
     *
     * @return bool
     */
    public function login($identifier = '', $password = '', $autoLogin = false)
    {
        $this->log->channel('login');

        if ($this->resume()) {
            return true;
        }

       
        //Credentials Login
        if ($identifier && $password) {
            if (preg_match(Config::getValidator()->getFieldRules('Email')->pattern, $identifier)) {
                //Login using email
                $getBy = 'Email';
            } else {
                //Login using Username
                $getBy = 'Username';
            }

            $this->log->debug('Credentials received');
        } else {
            if ($identifier && !$password) {
                // TODO: Throw exception
                $this->log->error(7);
            }
            return false;
        }

        $this->log->debug('Querying Database to authenticate user');

        //Query Database for user
        $userFile = $this->table->getRow(array($getBy => $identifier));

        if ($userFile && !$this->isSigned()) {
            // Update the user data
            $this->dataUpdates = $userFile; // TODO: Why?

            /*
             * Compared the generated hash with the stored one
             * If it matches then the user will be logged in
             */
            $this->session->signed = Config::getAuth()->verifyPassword($password, $userFile->Password, $this);

            // Clear the updates stack
            $this->dataUpdates = new Collection(); // TODO: Why?
        } else {
            if (!$this->isSigned() && $password) {
                $this->log->formError('Password', $this->errorList[10]);
                return false;
            }
        }

        if ($this->isSigned()) {
            //If Account is not Activated
            if ($userFile->Activated == 0) {
                if ($userFile->LastLogin == 0) {
                    //Account has not been activated
                    $this->log->formError('Password',$this->errorList[8]);
                } else {
                    if (!$userFile->Confirmation) {
                        //Account has been deactivated
                        $this->log->formError('Password',$this->errorList[9]);
                    } else {
                        //Account deactivated due to a password reset or reactivation request
                        $this->log->formError('Password',$this->errorList[14]);
                    }
                }
                // Remove the signed flag
                $this->session->signed = 0;
                return false;
            }

            $this->session->data->update($userFile->toArray());

            //If auto Remember User
            if ($autoLogin) {
                // TODO: Implement the auto login cookie
                $this->cookie->setValue('SomethingUnique');
                $this->cookie->add();
            }

            //Update last_login
            $this->logLogin();

            //Done
            $this->log->debug('User Logged in Successfully');
            return true;
        } else {
            if ($password) {
                // Removes the autologin cookie
                $this->cookie->destroy();
                $this->log->formError('Password', 10);
            }
            return false;
        }
    }

    /**
     * Logs user last login in database
     *
     * @ignore
     */
    protected function logLogin()
    {
        //Update last_login
        $time = time();
        $sql = "UPDATE _table_ SET LastLogin=:stamp WHERE ID=:id";
        if ($this->table->runQuery($sql, array('stamp' => $time, 'id' => $this->ID))) {
            $this->log->debug('Last Login updated');
        }
    }

    /**
     * Logout the user
     * Logs out the current user and deletes any autologin cookies
     *
     * @return void
     */
    function logout()
    {
        if (!$this->cookie->destroy()) {
            $this->log->debug('The Autologin cookie could not be deleted');
        }

        // Destroy the session
        $this->session->destroy();

        //Import default user object
        $this->data = $this->config->userDefaultData->toArray();

        $this->log->debug('User Logged out');
    }

    /**
     * Check if a user currently signed-in
     *
     * @return bool
     */
    public function isSigned()
    {
        return (bool) $this->session->signed;
    }

    /**
     * Register A New User
     * Takes two parameters, the first being required
     *
     * @access public
     * @api
     *
     * @param array|Collection $info       An associative array, the index being the field name(column in database)and the value
     *                                     its content(value)
     * @param bool             $activation Default is false, if true the user will need required further steps to activate account
     *                                     Otherwise the account will be activated if registration succeeds
     *
     * @return string|bool Returns activation hash if second parameter $activation is true
     *                        Returns true if second parameter $activation is false
     *                        Returns false on Error
     */
    public function register($info, $activation = false)
    {
        $this->log->channel('registration'); //Index for Errors and Reports

        /*
         * Prevent a signed user from registering a new user
         * NOTE: If a signed user needs to register a new user
         * use the User::manageUser() function to create a new user
         * object which then can then be use to register a new user
         */
        if ($this->isSigned()) {
            $this->log->error(15);
            return false;
        }

        //Saves Registration Data in Class
        $this->dataUpdates = $info = Helper::getCollection($info);
        
        Config::getValidator()->validateAll($info);

        //Set Registration Date
        $info->RegDate = time();

        /*
         * Built in actions for special fields
         */

        //Hash Password
        if ($info->Password) {
            $info->Password = Config::getAuth()->hashPassword($info->Password);
        }

        //Check for Email in database
        if ($info->Email) {
            if ($this->table->isUnique('Email', $info->Email, 16)) {
                return false;
            }
        }

        //Check for Username in database
        if ($info->Username) {
            if ($this->table->isUnique('Username', $info->Username, 17)) {
                return false;
            }
        }

        //Check for errors
        if ($this->log->hasError()) {
            return false;
        }

        //User Activation
        if (!$activation) {
            //Activates user upon registration
            $info->Activated = 1;
        }

        //Prepare Info for SQL Insertion
        $data = array();
        $into = array();
        foreach ($info->toArray() as $index => $val) {
            // TODO: Skip all match fields from the validation
            if (!preg_match("/2$/", $index)) { //Skips double fields
                $into[] = $index;
                //For the statement
                $data[$index] = $val;
            }
        }

        // Construct the fields
        $intoStr = implode(', ', $into);
        $values = ':' . implode(', :', $into);

        //Prepare New User Query
        $sql = "INSERT INTO _table_ ({$intoStr})
                VALUES({$values})";

        //Enter New user to Database
        if ($this->table->runQuery($sql, $data)) {
            $this->log->debug('New User has been registered');
            // Update the new ID internally
            $this->data['ID'] = $info->ID = $this->table->getLastInsertedID();
            if ($activation) {
                // TODO: Handle the confirmation in the initial insertion and use UserTokens for the confirmation
                // Generate a user specific hash
                $info->Confirmation = $this->hash->generate($info->ID);
                // Update the newly created user with the confirmation hash
                $this->update(array('Confirmation' => $info->Confirmation));
                // Return the confirmation hash
                return $info->Confirmation;
            } else {
                return true;
            }
        } else {
            $this->log->error(1);
            return false;
        }
    }

    /**
     * Validates and updates any field in the database for the current user
     * Similar to the register method function in structure,
     * this Method validates and updates any field in the database
     *
     * @api
     *
     * @param array|Collection $updates An associative array,
     *                                  the index being the field name(column in database)
     *                                  and the value its content(value)
     *
     * @return bool Returns true on success anf false on error
     */
    public function update($updates = null)
    {
        $this->log->channel('update');

        if (!is_null($updates)) {
            //Save Updates Data in Class
            $this->dataUpdates = $updates = Helper::getCollection($updates);
        } else {
            if ($this->dataUpdates instanceof Collection && !$this->dataUpdates->isEmpty()) {
                // Use the updates from the queue
                $updates = $this->dataUpdates;
            } else {
                // No updates
                return false;
            }
        }

        //Validate All Fields
        Config::getValidator()->validateAll($updates);

        /*
         * Built in actions for special fields
         */

        //Hash Password
        if ($updates->Password) {
            $updates->Password = Config::getAuth()->hashPassword($updates->Password);
        }

        //Check for Email in database
        if ($updates->Email) {
            if ($updates->Email != $this->Email) {
                if ($this->table->isUnique('Email', $updates->Email, 'This Email is Already in Use')) {
                    return false;
                }
            }
        }

        //Check for errors
        if ($this->log->hasError()) {
            return false;
        }

        //Prepare Info for SQL Insertion
        $data = array();
        $set = array();
        foreach ($updates->toArray() as $index => $val) {
            // TODO: Skip confirmation fields from the the validation scheme
            if (!preg_match('/2$/', $index)) { //Skips double fields
                $set[] = "{$index}=:{$index}";
                //For the statement
                $data[$index] = $val;
            }
        }

        $set = implode(', ', $set);

        //Prepare User Update Query
        $sql = "UPDATE _table_ SET {$set}  WHERE ID=:id";
        $data['ID'] = $this->ID;

        //Check for Changes
        if ($this->table->runQuery($sql, $data)) {
            $this->log->debug('Information Updated');

            if ($this->clone === 0) {
                $this->session->update = true;
            }

            // Update the current object with the updated information
            $this->data = array_merge($this->data, $updates->toArray());

            // Clear the updates stack
            $this->dataUpdates = new Collection();

            return true;
        } else {
            $this->log->error(2);
            return false;
        }
    }

    /**
     * Method to reset password, Returns confirmation code to reset password
     *
     * @access public
     * @api
     *
     * @param string $email User email to reset password
     *
     * @return Collection|bool On Success it returns a Collection with the user's (Email,Username,ID,Confirmation)
     *                        which could then be use to construct the confirmation URL and Email.
     *                        On Failure it returns false
     */
    public function resetPassword($email)
    {
        $this->log->channel('resetPassword');

        $user = $this->table->getRow(array('Email' => $email));

        if ($user) {
            if (!$user->Activated && !$user->Confirmation) {
                //The Account has been manually disabled and can't reset password
                $this->log->error(9);
                return false;
            }

            $data = array(
                'ID'           => $user->ID,
                // TODO: Use a UserToken
                'Confirmation' => $this->hash->generate($user->ID),
            );

            $this->table->runQuery('UPDATE _table_ SET Confirmation=:Confirmation WHERE ID=:ID', $data);

            return new Collection(
                array(
                    'Email'        => $email,
                    'Username'     => $user->Username,
                    'ID'           => $user->ID,
                    'Confirmation' => $data['Confirmation']
                )
            );
        } else {
            $this->log->formError('Email', $this->errorList[4]);
            return false;
        }
    }

    /**
     * Changes a Password with a Confirmation hash from the pass_reset method
     * This is for users that forget their passwords to change the signed in user password use ->update()
     *
     * @access public
     * @api
     *
     * @param string $hash      hash returned by the pass_reset() method
     * @param array  $newPass   An array with indexes 'password' and 'password2' Example:
     *                          array(
     *                          [password] => pass123
     *                          [password2] => pass123
     *                          )
     *
     * @return bool Returns true on a successful password change. Returns false on error
     */
    public function newPassword($hash, $newPass)
    {
        $this->log->channel('newPassword');

        list($uid, $partial) = $this->hash->examine($hash);

        if ($uid && $user = $this->table->getRow(array('ID' => $uid, 'Confirmation' => $hash))) {
            $this->dataUpdates = new Collection($newPass);
            if (!$this->validateAll()) {
                return false;
            } //There are validations error

            $this->dataUpdates = $user;

            // Generate the password hash
            $pass = $this->hash->generateUserPassword($this, $newPass['Password']);

            $sql = "UPDATE _table_ SET `Password`=:pass, Confirmation='', Activated=1 WHERE Confirmation=:confirmation AND ID=:id";
            $data = array(
                'id'           => $uid,
                'pass'         => $pass,
                'confirmation' => $hash
            );

            if ($this->table->runQuery($sql, $data)) {
                $this->log->debug('Password has been changed');
                return true;
            }
        }

        //Error
        $this->log->error(5);
        return false;
    }

    /**
     * Activates Account with a hash
     * Takes Only and Only the URL parameter of a confirmation page
     * which would be the hash returned by the register() method
     *
     * @access public
     * @api
     *
     * @param string $hash Hash returned in the register method
     *
     * @return bool Returns true account activation and false on failure
     */
    public function activate($hash)
    {
        $this->log->channel('activation');

        $info = $this->hash->examine($hash);

        if ($info && is_array($info)) {
            list($uid, $partial) = $info;

            $user = $this->manageUser($uid);

            if ($user->ID) {
                if ($user->Confirmation === $hash) {

                    $user->Activated = 1;
                    $user->Confirmation = '';

                    // Updates the flag on the database
                    if ($user->update()) {
                        $this->log->debug('Account has been Activated');
                        return true;
                    }
                } else {
                    $this->log->debug('The activation hash does not match the DB record');
                }
            } else {
                $this->log->debug("Unable to find user with ID $uid to activate");
            }
        }

        /*
         * Execution will end up here if something goes wrong
         */
        $this->log->error(3);
        return false;
    }

    public function manageUser($id)
    {
        return Config::getManager()->getUser($id);
    }
}
