<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Cron
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Crontab observer
 *
 * @category   Mage
 * @package    Mage_Cron
 */
class Mage_Cron_Model_Observer
{
    const CACHE_KEY_LAST_SCHEDULE_GENERATE_AT = 'cron_last_schedule_generate_at';

    const XML_PATH_SCHEDULE_GENERATE_EVERY = 'system/cron/schedule_generate_freq';
    const XML_PATH_SCHEDULE_AHEAD_FOR = 'system/cron/schedule_ahead_for';
    const XML_PATH_SCHEDULE_LIFETIME = 'system/cron/schedule_lifetime';

    const CACHE_KEY_LAST_HISTORY_CLEANUP_AT = 'cron_last_history_cleanup_at';

    const XML_PATH_HISTORY_CLEANUP_EVERY = 'system/cron/history_cleanup_every';
    const XML_PATH_HISTORY_SUCCESS = 'system/cron/history_success_lifetime';
    const XML_PATH_HISTORY_FAILURE = 'system/cron/history_failure_lifetime';

    const REGEX_RUN_MODEL = '#^([a-z0-9_]+/[a-z0-9_]+)::([a-z0-9_]+)$#i';

    protected $_pendingSchedules;

    public function dispatch($observer)
    {
        $schedules = $this->getPendingSchedules();
        $scheduleLifetime = Mage::getStoreConfig(self::XML_PATH_SCHEDULE_LIFETIME) * 60;

        $now = time();
        $jobsRoot = Mage::getConfig()->getNode('crontab/jobs');
        foreach ($schedules->getIterator() as $schedule) {
            $jobConfig = $jobsRoot->{$schedule->getJobCode()};
            if (!$jobConfig || !$jobConfig->run) {
                continue;
            }
            $runConfig = $jobConfig->run;
            $time = strtotime($schedule->getScheduledAt());
            if ($time > $now) {
                continue;
            }
            try {
                $errorStatus = Mage_Cron_Model_Schedule::STATUS_ERROR;
                $errorMessage = 'Unknown error';

                if ($time < $now - $scheduleLifetime) {
                    $errorStatus = Mage_Cron_Model_Schedule::STATUS_MISSED;
                    throw Mage::exception('Mage_Cron', 'Too late for the schedule');
                }

                $schedule->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', time()));

                if ($runConfig->model) {
                    if (!preg_match(self::REGEX_RUN_MODEL, (string)$runConfig->model, $run)) {
                        throw Mage::exception('Mage_Cron', 'Invalid model/method definition, expecting "model/class::method".');
                    }
                    if (!($model = Mage::getModel($run[1])) || !method_exists($model, $run[2])) {
                        throw Mage::exception('Mage_Cron', 'Invalid callback: '.$run[1].'::'.$run[2].' does not exist');
                    }
                    $callback = array($model, $run[2]);
                    $arguments = array($schedule);
                }
                if (empty($callback)) {
                    throw Mage::exception('Mage_Cron', 'No callbacks found');
                }

                $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_RUNNING)
                    ->save();

                call_user_func_array($callback, $arguments);

                $schedule
                    ->setStatus(Mage_Cron_Model_Schedule::STATUS_SUCCESS)
                    ->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()));

            } catch (Exception $e) {
                $schedule->setStatus($errorStatus)
                    ->setMessages($e->getMessage());
            }
            $schedule->save();
        }

        $this->generate();
        $this->cleanup();
    }

    public function getPendingSchedules()
    {
        if (!$this->_pendingSchedules) {
            $this->_pendingSchedules = Mage::getModel('cron/schedule')->getCollection()
                ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_PENDING)
                ->load();
        }
        return $this->_pendingSchedules;
    }

    public function generate()
    {
        // check if schedule generation is needed
        $lastRun = Mage::app()->loadCache(self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT);
        if ($lastRun > time() - Mage::getStoreConfig(self::XML_PATH_SCHEDULE_GENERATE_EVERY)*60) {
            return $this;
        }

        $scheduleAheadFor = Mage::getStoreConfig(self::XML_PATH_SCHEDULE_AHEAD_FOR)*60;

        $schedules = $this->getPendingSchedules();
        $exists = array();
        foreach ($schedules->getIterator() as $schedule) {
            $exists[$schedule->getJobCode().'/'.$schedule->getScheduledAt()] = 1;
        }

        $schedule = Mage::getModel('cron/schedule');

        // generate jobs
        $jobs = Mage::getConfig()->getNode('crontab/jobs')->children();
        foreach ($jobs as $jobCode => $jobConfig) {
            $cronExpr = null;
            if ($jobConfig->schedule->config_path) {
                $cronExpr = Mage::getStoreConfig((string)$jobConfig->schedule->config_path);
            }
            if (empty($cronExpr) && $jobConfig->schedule->cron_expr) {
                $cronExpr = (string)$jobConfig->schedule->cron_expr;
            }
            if (!$cronExpr) {
                continue;
            }

            $now = time();
            $timeAhead = $now + $scheduleAheadFor;
            $schedule->setJobCode($jobCode)
                ->setCronExpr($cronExpr)
                ->setStatus(Mage_Cron_Model_Schedule::STATUS_PENDING);

            for ($time = $now; $time < $timeAhead; $time += 60) {
                $ts = strftime('%Y-%m-%d %H:%M:00', $time);
                if (!empty($exists[$jobCode.'/'.$ts])) {
                    // already scheduled
                    continue;
                }
                if (!$schedule->trySchedule($time)) {
                    // time does not match cron expression
                    continue;
                }
                $schedule->unsScheduleId()->save();
            }
        }
        // save time schedules generation was ran with no expiration
        Mage::app()->saveCache(time(), self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT, array('crontab'), null);

        return $this;
    }

    public function cleanup()
    {
        // check if history cleanup is needed
        $lastCleanup = Mage::app()->loadCache(self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT);
        if ($lastCleanup > time() - Mage::getStoreConfig(self::XML_PATH_HISTORY_CLEANUP_EVERY)*60) {
            return $this;
        }

        $history = Mage::getModel('cron/schedule')->getCollection()
            ->addFieldToFilter('status', array('in'=>array(
                Mage_Cron_Model_Schedule::STATUS_SUCCESS,
                Mage_Cron_Model_Schedule::STATUS_MISSED,
                Mage_Cron_Model_Schedule::STATUS_ERROR,
            )))->load();

        $historyLifetimes = array(
            Mage_Cron_Model_Schedule::STATUS_SUCCESS => Mage::getStoreConfig(self::XML_PATH_HISTORY_SUCCESS)*60,
            Mage_Cron_Model_Schedule::STATUS_MISSED => Mage::getStoreConfig(self::XML_PATH_HISTORY_FAILURE)*60,
            Mage_Cron_Model_Schedule::STATUS_ERROR => Mage::getStoreConfig(self::XML_PATH_HISTORY_FAILURE)*60,
        );

        $now = time();
        foreach ($history->getIterator() as $record) {
            if (strtotime($record->getExecutedAt()) < $now-$historyLifetimes[$record->getStatus()]) {
                $record->delete();
            }
        }

        // save time history cleanup was ran with no expiration
        Mage::app()->saveCache(time(), self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT, array('crontab'), null);

        return $this;
    }

    public function test()
    {
        echo "TEST";
    }
}