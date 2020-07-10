<?php

namespace ArcaSolutions\WebBundle\Mixpanel;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

class MixpanelHelper
{
    /** @var \Mixpanel */
    private $mixpanel;

    /** @var string */
    private $mixpanelDistinctId;

    /** @var string */
    private $edirectoryVersion;

    /** @var string */
    private $remoteAddr;

    /** @var string */
    private $host;

    /** @var Connection */
    private $conn;

    /** @var LoggerInterface */
    private $logger;

    /**
     * Create a mixpanel profile if the user doesn't have a distinct id.
     * In case the user already has one, identify him and skip profile creation.
     *
     * @param string $distinctId
     * @param string $username
     * @param array $options
     */
    public function createProfile($distinctId, $username, $options)
    {
        if (!$distinctId) return;

        try {
            $id = $options["is_mainsitemgr"] ? $this->getMainSitemgrDistinctId() : $this->getSitemgrDistinctId($username);

            if ($id) {
                $this->identify($id);
                $this->setProfileProperties($id, $username, $options);
                return;
            }

            $this->mixpanel->createAlias($distinctId, $this->host.'/'.$username);

            $this->mixpanel->people->setOnce($distinctId, [
                '$first_name'   => "",
                '$last_name'    => "",
                '$name'         => $options["install_name"],
                '$city'         => "",
                '$country_code' => "",
                '$region'       => "",
                '$timezone'     => "",
            ], $this->remoteAddr);

            $this->setProfileProperties($distinctId, $username, $options);

            if ($username) {
                if ($options["is_mainsitemgr"]) {
                    $sql = "REPLACE INTO Setting VALUES ('mixpanel_distinct_id', :distinctId)";

                    $this->conn->executeUpdate($sql, [
                        'distinctId' => $distinctId,
                    ]);
                } else {
                    $sql = 'UPDATE SMAccount SET mixpanel_distinct_id = :distinctId WHERE username = :username';

                    $this->conn->executeUpdate($sql, [
                        'distinctId' => $distinctId,
                        'username'   => $username,
                    ]);
                }
            }

            $this->identify($distinctId);
            $this->trackEvent('First Login');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * Get distinct id from Setting table.
     *
     * @return null|string Returns null if no distinct id was found.
     */
    public function getMainSitemgrDistinctId()
    {
        $sql = "SELECT value FROM Setting WHERE name = 'mixpanel_distinct_id'";

        $stmt = $this->conn->executeQuery($sql);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        return $result['value'];
    }

    /**
     * Get distinct id from SMAccount table.
     *
     * @param string $username
     * @return null|string Returns null if no distinct id was found.
     */
    public function getSitemgrDistinctId($username)
    {
        $sql = 'SELECT mixpanel_distinct_id FROM SMAccount WHERE username = :username';

        $stmt = $this->conn->executeQuery($sql, ['username' => $username]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        return $result['mixpanel_distinct_id'];
    }

    /**
     * Updates profile information.
     *
     * @param string $distinctId
     * @param string $username
     * @param array $options
     */
    private function setProfileProperties($distinctId, $username, $options)
    {
        $this->mixpanel->people->set($distinctId, [
            '$email'             => $username,
            'User type'          => $options["is_mainsitemgr"] ? "Main Site Manager" : "Sub Site Manager",
            'Fresh Install'      => $options["fresh_install"],
            'Free Trial'         => $options["free_trial"],
            'Free Trial Ended'   => $options["free_trial_ended"],
            'Domain URL'         => $this->host,
            "eDirectory Version" => $this->edirectoryVersion,
        ], $this->remoteAddr);
    }

    /**
     * Identify user.
     *
     * @param int $distinctId
     */
    public function identify($distinctId)
    {
        try {
            $this->mixpanelDistinctId = $distinctId;

            $this->mixpanel->identify($this->mixpanelDistinctId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * @param $event
     * @param array $properties
     */
    public function trackEvent($event, $properties = [])
    {
        if (!$this->mixpanelDistinctId) {
            return;
        }

        try {
            $this->mixpanel->track($event, $properties);

            if ($event == TrackEvents::SITEMGR_LOGGED_IN) {
                $this->mixpanel->people->increment($this->mixpanelDistinctId, "login count", 1, $this->remoteAddr);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * @param string $edirectoryVersion
     * @return MixpanelHelper
     */
    public function setEdirectoryVersion($edirectoryVersion)
    {
        $this->edirectoryVersion = $edirectoryVersion;

        return $this;
    }

    /**
     * @param string $remoteAddr
     * @return MixpanelHelper
     */
    public function setRemoteAddr($remoteAddr)
    {
        $this->remoteAddr = $remoteAddr;

        return $this;
    }

    /**
     * @param string $host
     * @return MixpanelHelper
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @param mixed $conn
     * @return MixpanelHelper
     */
    public function setConnection($conn)
    {
        $this->conn = $conn;

        return $this;
    }

    /**
     * @param \Mixpanel $mixpanel
     * @return MixpanelHelper
     */
    public function setMixpanel($mixpanel)
    {
        $this->mixpanel = $mixpanel;

        return $this;
    }

    /**
     * @param LoggerInterface $logger
     * @return MixpanelHelper
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
