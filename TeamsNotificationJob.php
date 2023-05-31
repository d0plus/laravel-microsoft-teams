<?php

declare(strict_types=1);

namespace common\components\MicrosoftTeams;

use common\helpers\ErrorHelper;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\RetryableJobInterface;

/**
 * Class TeamsNotificationJob
 * @package common\components\MicrosoftTeams
 */
class TeamsNotificationJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    /**
     * @var MicrosoftTeamsMessageInterface
     */
    public $message;

    /**
     * @param $queue
     */
    public function execute($queue)
    {
        if (!$this->message || !$this->message instanceof MicrosoftTeamsMessageInterface) {
            ErrorHelper::message("Microsoft Teams API notification error! Message is empty or incorrect");
        }
        try {
            Yii::$app->microsoftTeams->sendPreparedDataToUrl($this->message->getWebhookUrl(), $this->message->toArray());
        } catch (Throwable $ex) {
            ErrorHelper::fatalWithExtraData($ex, $this->message->toArray());
        }
    }

    /**
     * @return int time to reserve in seconds
     */
    public function getTtr()
    {
        return 30;
    }

    /**
     * @param int $attempt number
     * @param Throwable $error from last execute of the job
     * @return bool
     */
    public function canRetry($attempt, $error)
    {
        return $attempt < 3;
    }
}
