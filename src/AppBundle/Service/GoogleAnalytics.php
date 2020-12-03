<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\Container;

class GoogleAnalytics
{
    protected $container;
    protected $client;
    protected $analytics;
    protected $dateStartMin = '2017-09-01';

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->client = new \Google_Client();
        //$this->client->setApplicationName("Analytics Reporting");
        $this->client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $this->client->setAuthConfig($this->container->getParameter('googleanalytics_private_key_json'));
        $this->analytics = new \Google_Service_Analytics($this->client);
    }

    public function getSessionsAndDuration($profileId, $startAt = null, $endAt = null)
    {
        $datas = $this->_getData($profileId, $startAt, $endAt, 'ga:sessions,ga:sessionDuration', array('dimensions' => 'ga:date'));
        if ($datas === false) {
            return array();
        }
        return $datas->getRows();
    }

    public function getDeviceType($profileId, $startAt = null, $endAt = null)
    {
        $datas = $this->_getData($profileId, $startAt, $endAt, 'ga:sessions', array('dimensions' => 'ga:mobileDeviceBranding'));
        if ($datas === false) {
            return array();
        }
        return $datas->getRows();
    }

    public function getSessionsAndUser($profileId, $startAt = null, $endAt = null)
    {
        $datas = $this->_getData($profileId, $startAt, $endAt, 'ga:sessions,ga:users', array('dimensions' => 'ga:date'));
        if ($datas === false) {
            return array();
        }
        return $datas->getRows();
    }

    public function getSessions($profileId, $startAt = null, $endAt = null)
    {
        $datas = $this->_getData($profileId, $startAt, $endAt, 'ga:sessions', array('dimensions' => 'ga:date'));
        if ($datas === false) {
            return array();
        }
        return $datas->getRows();
    }

    public function getSessionsAndMedium($profileId, $startAt = null, $endAt = null)
    {
        $datas = $this->_getData($profileId, $startAt, $endAt, 'ga:sessions', array('dimensions' => 'ga:medium'));
        if ($datas === false) {
            return array();
        }
        return $datas->getRows();
    }

    public function getCustomEvent($profileId, $startAt = null, $endAt = null)
    {
        $datas = $this->_getData($profileId, $startAt, $endAt, 'ga:totalEvents', array('dimensions' => 'ga:date,ga:eventLabel'));
        if ($datas === false) {
            return array();
        }
        return $datas->getRows();
    }

    private function _getData($profileId, $startAt, $endAt, $metrics, $dimensions = array())
    {
        if ($endAt == null || $endAt == '') {
            $endAt = 'today';
        } else {
            $endAt = substr($endAt, 6, 4) . '-' . substr($endAt, 3, 2) . '-' . substr($endAt, 0, 2);
        }
        if ($startAt == null || $startAt == '') {
            $startAt = $this->dateStartMin;
        } else {
            $startAt = substr($startAt, 6, 4) . '-' . substr($startAt, 3, 2) . '-' . substr($startAt, 0, 2);
        }
        if (is_null($profileId) || $profileId == '') {
            return false;
        }
        return $this->analytics->data_ga->get('ga:' . $profileId, $startAt, $endAt, $metrics, $dimensions);
    }
}
