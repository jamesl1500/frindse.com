<?php

class Search extends Database
{
    static public function main($data)
    {
        if (!empty($data) && $data != "") {
            $valid_text = Validation::santitize($data['searchUser']);
            $response = array();
            $payload = array();
            $counter = 0;
            $c_counter = 0;

            // Create Payloads
            $payload['users_payload'] = array();
            $payload['cliques_payload'] = array();

            if ($valid_text != "") {
                $db = new Database;

                // Search Users

                //Render the type of search whether the person is searching a person with a firstname or a firstname and a lastname
                $value = explode(' ', $valid_text);

                if (isset($value[0]) && !isset($value[1])) {
                    $query = $db->prepare("SELECT * FROM " . USERS . " WHERE firstname LIKE '%" . $value[0] . "%' OR lastname LIKE '%" . $value[0] . "%' OR username LIKE '%" . $value[0] . "%'");
                } else if (isset($value[0]) && isset($value[1])) {
                    $query = $db->prepare("SELECT * FROM " . USERS . " WHERE firstname LIKE '%" . $value[0] . "%' AND lastname LIKE '%" . $value[1] . "%' OR firstname LIKE '%" . $value[1] . "%' AND lastname LIKE '%" . $value[0] . "%'");
                }

                if ($query->execute()) {
                    if ($query->rowCount() > 0) {
                        $payload['users_payload']['count'] = $query->rowCount();
                        $payload['users_payload']['show_all_users'] = true;

                        while (($fetch = $query->fetch(PDO::FETCH_ASSOC)) and ($counter < 13)) {

                            // Make user payload
                            if ($fetch['activated'] == 1 && Block::checkBlockStatus(Sessions::get('uid'), $fetch['user_id']) == 0) {
                                $payload['users_payload']['payload'][md5($fetch['user_salt'])] = array(
                                    'user_id' => $fetch['user_id'],
                                    'first_name' => ucwords($fetch['firstname']),
                                    'last_name' => ucwords($fetch['lastname']),
                                    'username' => $fetch['username'],
                                    'profile_picture' => $fetch['profile_pic'],
                                    'banner_picture' => $fetch['banner_pic'],
                                    'salt' => $fetch['user_salt']
                                );
                            } else {
                                $payload['users_payload']['count'] - 1;
                            }
                            $payload['code'] = 1;
                            $counter++;
                        }
                    } else {
                        // Make the users payload equal to nothing
                        $payload['users_payload']['count'] = 0;
                        $payload['users_payload']['show_all_users'] = false;
                        $payload['users_payload']['payload'] = "";

                        $payload['code'] = 1;
                    }
                } else {
                    $response['code'] = 0;
                    $response['status'] = "Error has occurred!";
                    $response['show_results_more'] = false;
                    echo json_encode($response);
                    return false;
                }

                // Search Cliques
                $query2 = $db->prepare("SELECT * FROM " . CLIQUES . " WHERE c_username LIKE '%" . $data['searchUser'] . "%' OR c_name LIKE '%" . $data['searchUser'] . "%'");
                if ($query2->execute()) {
                    if ($query2->rowCount() > 0) {
                        $payload['cliques_payload']['count'] = $query2->rowCount();

                        while (($fetch2 = $query2->fetch(PDO::FETCH_ASSOC)) and ($c_counter < 13)) {
                            // Make the cliques payload
                            if ($fetch2['c_active'] == 1) {
                                $payload['cliques_payload']['payload'][md5($fetch2['c_unique_id'])] = array(
                                    'clique_id' => $fetch2['c_id'],
                                    'clique_name' => $fetch2['c_name'],
                                    'clique_username' => $fetch2['c_username'],
                                    'clique_bio' => $fetch2['c_bio'],
                                    'c_x' => $fetch2['c_unique_id'],
                                    'clique_profile_pic' => $fetch2['c_profile_pic'],
                                    'clique_banner_pic' => $fetch2['c_banner_pic'],
                                    'clique_privacy' => $fetch2['c_privacy'],
                                    'count_members' => Clique::numberOfMembers($fetch2['c_unique_id'])
                                );
                            } else {
                                $payload['cliques_payload']['count'] - 1;
                            }
                            $payload['code'] = 1;
                            $counter++;
                        }
                    } else {
                        // Make the users payload equal to nothing
                        $payload['cliques_payload']['count'] = 0;
                        $payload['cliques_payload']['payload'] = "";

                        $payload['code'] = 1;
                    }
                } else {
                    $response['code'] = 0;
                    $response['status'] = "Error has occurred!";
                    $response['show_results_more'] = false;
                    echo json_encode($response);
                    return false;
                }
                echo json_encode($payload);
                return false;
            }
        } else {
            $response['code'] = 0;
            $response['status'] = "Please enter something!";
            $response['show_results_more'] = false;
            echo json_encode($response);
            return false;
        }
    }
}