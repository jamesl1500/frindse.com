<?php
/*
 * Suggestions
 * ----
 * 
 */
class Suggestions extends Database
{
    static public function getRandomUsers()
    {
        // call the dataabse
        $db = new Database;

        // Get friends array
        $array = Friends::getFriendsArray(Sessions::get('uid'));
        
        // Query
        $query = $db->prepare("SELECT * FROM " . USERS . " WHERE activated='1' AND user_id  NOT IN ( '" . implode($array, "', '") . "' ) AND user_id !='" . Sessions::get('uid') . "' ORDER BY rand() LIMIT 3");
        $query->execute();

        if ($query->rowCount() != 0) 
        {
            // Fetch
            while ($fetch = $query->fetch(PDO::FETCH_ASSOC)) 
            {
                // get data
                $user_id = $fetch['user_id'];
                $salt = $fetch['user_salt'];

                $firstname = $fetch['firstname'];
                $lastname = $fetch['lastname'];
                $username = $fetch['username'];

                if (Sessions::get('uid') != $user_id) {
                    ?>
                    <div class="userMod borderedMod" id="userMod<?php echo $user_id; ?>">
                        <div class="modProfilePic">
                            <img src="<?php echo APP_URL; ?>users/data/<?php echo $salt; ?>/profile_picture" />
                        </div>
                        <div class="modInfo">
                            <h3><a href="<?php echo APP_URL; ?>profile/<?php echo $username; ?>"><?php echo ucwords($firstname); ?> <?php echo ucwords($lastname); ?></a></h3>
                            <h4>@<?php echo $username; ?></h4>
                        </div>
                    </div>
                    <?php
                }
            }
        } else if ($query->rowCount() == 0) {
            echo "<center><h3>No Suggestions</h3></center>";
        }
    }

    static public function displayRandomNewUsers()
    {
        // Find random users
        $db = new Database;

        $query = $db->prepare("SELECT username, profile_pic FROM " . USERS  ." WHERE activated='1' AND profile_pic != 'default_pic.jpg' ORDER BY RAND() LIMIT 17");
        $query->execute();

        if($query->rowCount() > 0)
        {
            while($fetch = $query->fetch(PDO::FETCH_ASSOC))
            {
                $profile_pic = $fetch['profile_pic'];
                $username = $fetch['username'];
                ?>
                
                <?php
            }
        }
    }

    static public function getRandomUsersByInterests()
    {

    }
}

?>