<?php

namespace AppBundle\Service;

use RMS\PushNotificationsBundle\Message\iOSMessage;
use RMS\PushNotificationsBundle\Message\AndroidMessage;
use AppBundle\Entity\MobileLog;


use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;
use sngrl\PhpFirebaseCloudMessaging\Notification as FirebaseNotification;


class Mobile
{
    protected $container;
    protected $logger;

    public function __construct($container,$logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    public function pushNotification($user, $title, $message, $eventId = false, $articleId = false, $silent = 'off', $associationId = false, $merchantId = false, $store = 'off', $participantNbre = 'no', $communityId = false)
    {

        $this->logger->info($user->getId());

        $devicetokens = $user->getDeviceTokens();
        foreach ($devicetokens as $devicetoken) {
           // $this->_push($user, $messageDatas, $devicetoken->getDeviceToken(), $devicetoken->getType(), $eventId, $articleId, $silent, $associationId, $merchantId, $store, $participantNbre);
             $this->_push($user, $title,$message, $devicetoken->getDeviceToken(), $devicetoken->getType(), $eventId, $articleId, $silent, $associationId, $merchantId, $store, $participantNbre);


        }



    }

    private function _push($user, $title,$messageDatas, $deviceToken, $deviceType, $eventId = false, $articleId = false, $silent = false, $associationId = false, $merchantId = false, $store = false, $participantNbre = 'no',$communityId = null)
    {
        //$server_key = 'AIzaSyCoX7vmFqRkimaohDeXgIGpoeLeneC8HuU';
        //$server_key = 'AIzaSyDnVK6ZznIrhZwivmd3YVb3Hct0_XaGDH8';
        $server_key = 'AAAANkhssjk:APA91bHcJh8pRhbNNyPpZwzrmC9HpaYR7eYbFXYJXqNcdtGmDIKHjORqLG52b6c8aMN9_NzVboNx2jDdAToaaqpi8RT-Z9CbkLcsJFmVgDhi13OVj1yRm-9KLSdjG74jX9MDtzn7Avs6';
        $client = new Client();
        $client->setApiKey($server_key);
        $this->logger->info($deviceToken);
        $this->logger->info($deviceType);


        if ($deviceToken) {


            $note = new FirebaseNotification($title, $messageDatas);
            $note->setBadge(1);

            $message = new Message();
            $message->setPriority('high');





            if ($eventId && $participantNbre == 'no') {
                $datas = array("message" => $messageDatas, "title" => $title, "eventId" => $eventId, "silent" => $silent, "content-available" => 1);
            } elseif ($articleId) {
                $datas = array("message" => $messageDatas, "title" => $title, "articleId" => $articleId, "silent" => $silent, "content-available" => 1);
            } elseif ($associationId) {
                $datas = array("message" => $messageDatas, "title" => $title, "associationId" => $associationId, "silent" => $silent, "content-available" => 1);
            } elseif ($merchantId) {
                $datas = array("message" => $messageDatas, "title" => $title, "merchantId" => $merchantId, "silent" => $silent, "content-available" => 1);
            } elseif ($silent == 'on') {
                $datas = array("message" => $messageDatas, "title" => $title, "silent" => $silent, "content-available" => 1, 'sound' => "");
            } elseif ($participantNbre == 'yes') {
                $datas = array("message" => $messageDatas, "title" => $title, "eventId" => $eventId, "content-available" => 1, 'sound' => "", "participantNbre" => $participantNbre);
            } elseif ($communityId) {
                $datas = array("message" => $messageDatas, "title" => $title, "communityId" => $communityId, "silent" => $silent, "content-available" => 1);
            } elseif ($store == 'on') {
                $datas = array("message" => $messageDatas, "title" => $title, "store" => $store, "content-available" => 1);
            } else {
                $datas = array("message" => $messageDatas, "title" => $title, "silent" => $silent, "content-available" => 1);
            }

            $message->addRecipient(new Device($deviceToken));
            $message
                ->setNotification($note)
                ->setData($datas);

        }


        if (isset($message)) {
            try {
                // $push = $this->container->get('rms_push_notifications')->send($message);
                $push = $client->send($message);
            } catch (\Exception $e) {
                return array('error' => $e);
                //echo 'Message: ' .$e->getMessage();
            }

            /*if (isset($message) && $deviceType == 'ios') {
                //$push = $this->container->get('rms_push_notifications')->send($message);

            }*/
            if ($push) {
                $em = $this->container->get('doctrine')->getManager();
                $log = new MobileLog();
                $log->setUser($user);
                $log->setContent($messageDatas);
                $log->setTitle($title);
                $log->setType($deviceType);
                $em->persist($log);
                $em->flush();
            }

            return $push;
        }
    }



    // Sends Push notification for Android users
    public function android($data, $reg_id) {

        $API_ACCESS_KEY = $this->container->getParameter('android_api_key');
        $url = 'https://android.googleapis.com/gcm/send';
        $message = $data;

        $headers = array(
            'Authorization: key=' .$API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $fields = array(
            'registration_ids' => array($reg_id),
            'data' => $message,
        );

        return $this->useCurl($url, $headers, json_encode($fields));
    }


    private function useCurl($url, $headers, $fields = null) {
        // Open connection
        $ch = curl_init();
        if ($url) {
            // Set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if ($fields) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            }

            // Execute post
            $result = curl_exec($ch);
            $this->logger->info($result);
            /*dump($result);
            exit;*/
            if ($result === FALSE) {
                curl_close($ch);
                die('Curl failed: ' . curl_error($ch));
            }
            curl_close($ch);

            // Close connection


            return $result;
        }
    }

}
