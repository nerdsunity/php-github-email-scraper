<?php
/*
 * Disclaimer
 * This script should be used for learning purposes only.
 * By downloading and running this script you take every responsibility for wrong or illegal uses of it.
 * Please read Github Terms of Service for more information: https://help.github.com/articles/github-terms-of-service/
 */

/*
 * MIT License

 * Copyright (c) 2016 NerdsUnity

 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE
 * FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require("httpful.phar");

/* THIS IS A BUG */
->;This\$isA\$Bug;<-

function _is_valid_email ($email = "")
{
    return preg_match('/^[.\w-]+@([\w-]+\.)+[a-zA-Z]{2,6}$/', $email);
}

function done($usersAdded, $createdDate, $page){
    global $usersLanguage;

    echo "USERS ADDED: " . $usersAdded . "\n";
    echo "IN: " . $usersLanguage . "\n";
    echo "LAST DATE PARSED: " . $createdDate . "\n";
    echo "LAST PAGE PARSED: " . $page . "\n";
    die;
}

function addUsers($page, $createdDate, $usersAdded = 0){
    global $usersToGet;
    global $usersLanguage;
    global $password;
    global $username;

    $page;
    $createdDate;
    $usersAdded;

    if($usersAdded < $usersToGet){
        try{
            $randomizer = mt_rand(0,2);
            sleep($randomizer);

            $queryUrl = 'https://api.github.com/search/users?page='.$page.'&per_page=100&q=repos%3A>-1+language%3A'.$usersLanguage.'+created%3A'.$createdDate.'+in%3Aemail';
            $queryUsers = "ok";
            $users = \Httpful\Request::get($queryUrl)
                ->authenticateWith($username, $password)
                ->expectsJson()
                ->send();

        }catch(Exception $e){
            $queryUsers = $e->getMessage();
        }

        if(isset($users->body->message) || $queryUsers != "ok"){
            /* API ISSUE */
            if(isset($users->body->message)) {
                echo $users->body->msessage . "\n";
            }else{
                echo $queryUsers . "\n";
            }
            die();
        }

        if(isset($users->body->items) && count($users->body->items) > 0){
            //adds users
            $countUserLoop = 0;
            $countLoops = count($users->body->items);
            foreach($users->body->items as $user){
                if($usersAdded < $usersToGet){
                    try{
                        $randomizer = mt_rand(0,1);
                        sleep($randomizer);

                        $queryUser = "ok";
                        $user = \Httpful\Request::get($user->url)
                            ->authenticateWith($username, $password)
                            ->expectsJson()
                            ->send();
                    }catch(Exception $e){
                        $queryUser = $e->getMessage();
                    }


                    if(isset($user->body->message) || $queryUser != "ok"){
                        /* API ISSUE */
                        if(isset($user->body->message)) {
                            echo "SOMETHING IS WRONG IN THE USER RESPONSE\n";
                            echo $user->body->message . "\n";
                        }else{
                            echo "SOMETHING IS WRONG IN THE USER QUERY\n";
                            echo $queryUser . "\n";
                        }
                        die;
                    }

                    $userObj = $user->body;
                    if(_is_valid_email($userObj->email)){
                        ECHO "NEW USER FOUND: " . $userObj->email . "\n";
                        $usersAdded++;
                    }

                    if($countUserLoop == ($countLoops - 1)){
                        $newPage = ($page + 1);
                        echo "SKIP TO NEW PAGE: " . $newPage . "\n";
                        addUsers($newPage, $createdDate, $usersAdded);
                        break;
                    }
                    $countUserLoop++;
                }else{
                    done($usersAdded, $createdDate, $page);
                }
            }
        }else{
            $newDate = date('Y-m-d', strtotime($createdDate . "+1 days"));
            echo "SKIP TO NEW DATE: " . $newDate . "\n";
            addUsers(1, $newDate, $usersAdded);
        }
    }else{
        done($usersAdded, $createdDate, $page);
    }
}


/* INITIALIZING */
$username = $argv[1];
$password = $argv[2];
$usersToGet = $argv[3];
$usersLanguage = $argv[4];

if(isset($argv[5]) && isset($argv[6])){
    $startPage = $argv[5];
    $startDate = $argv[6];
}else{
    $startPage = 1;
    $startDate = "2007-12-31";
}

addUsers($startPage, $startDate);
