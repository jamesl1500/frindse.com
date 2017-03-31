<?php

class Emailer
{
    static public function Email($data = array())
    {
        if (!empty($data)) {
            $to = $data['to'];
            $subject = Validation::santitize($data['subject']);
            $body = $data['body'];
            $db = new Database;

            // Additional headers
            $headers = 'To: <' . $to . '>' . "\n";
            $headers .= 'From: ' . SITE_NAME . ' <james@' . SITE_NAME . '.com>' . "\n";
            $headers .= "MIME-Version: 1.0\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1";

            // Send the messages
            if (mail($to, $subject, $body, $headers)) {
                $insert = $db->prepare("INSERT INTO emails_sent VALUES('','{$to}','{$subject}')");
                $insert->execute();
            }
        }
    }
}

?>