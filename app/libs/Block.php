<?php

class Block extends Database
{
    static public function blockPerson($blocker, $blockee, $reason = "")
    {
        if (empty($blocker) != true && empty($blockee) != true) {
            $blockerID = (int)Validation::santitize($blocker);
            $blockeeID = (int)Validation::santitize($blockee);
            $reasonClean = Validation::santitize($reason);
            $date = date("y:m:d h:i:s");

            if ($blockerID != "" && $blockeeID != "") {
                if (Users::checkExists($blockee) == 1) {
                    $db = new Database;
                    // Now check to see if the 'blocker is friends with the 'blockee', then unfriend them and delete every interaction with this person
                    if (Friends::checkFriendshipStatus(array('person1' => $blockerID, 'person2' => $blockeeID, 'check' => 'friendship')) == 1) {
                        // Then unfriend them by getting the friendship id
                        $friendshipID = Friends::getFriendshipId(array('person1' => $blockerID, 'person2' => $blockeeID));
                        if ($friendshipID != "") {
                            if (Friends::unfriend($friendshipID)) {
                                // Now block them
                                /* Procedure:
                                 * Step 1: Delete all the messages between the two
                                 * Step 2: Delete all the posts between the two
                                 */

                                // Delete Messages
                                $msgs = $db->prepare("DELETE FROM " . MESSAGES . " WHERE user_to='" . $blockeeID . "' AND user_from='" . $blockerID . "' OR user_to='" . $blockerID . "' AND user_from='" . $blockeeID . "'");
                                $msgs->execute();

                                // Make the block
                                $add = $db->prepare("INSERT INTO " . BLOCKS . " VALUES('','" . $blockerID . "','" . $blockeeID . "','" . $reasonClean . "','" . $date . "')");
                                $add->execute();

                                echo json_encode(array('code' => '1', 'status' => 'User Blocked'));
                                return false;
                            }
                        }
                    } else {

                        // Now block them
                        /* Procedure:
                         * Step 1: Delete all the messages between the two
                         * Step 2: Delete all the posts between the two
                         */

                        // Delete Messages
                        $msgs = $db->prepare("DELETE FROM " . MESSAGES . " WHERE user_to='" . $blockeeID . "' AND user_from='" . $blockerID . "' OR user_to='" . $blockerID . "' AND user_from='" . $blockeeID . "'");
                        $msgs->execute();

                        // Make the block
                        $add = $db->prepare("INSERT INTO " . BLOCKS . " VALUES('','" . $blockerID . "','" . $blockeeID . "','" . $reasonClean . "','" . $date . "')");
                        $add->execute();

                        echo json_encode(array('code' => '1', 'status' => 'User Blocked'));
                        return false;
                    }
                } else {
                    echo json_encode(array('code' => '0', 'status' => 'This person dosent exist!'));
                    return false;
                }
            }
        }
    }

    static public function unblockPerson($blockID)
    {

    }

    static public function checkBlockStatus($person1, $person2)
    {
        /* Procedure:
         * @param var $person1 - The variable that will be the logged in person
         * @param var $person2 - The variable that will be the other person
         *
         * Steps:
         *
         * Step 1: Find if the $person1 has blocked $person2
         * Step 2: Find if the $person2 has blocked $person1
         */
    }
}

?>