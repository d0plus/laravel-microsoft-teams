<?php

declare(strict_types=1);

namespace common\components\MicrosoftTeams;

use common\components\MicrosoftTeams\Exceptions\CouldNotSendNotification;
use common\helpers\ErrorHelper;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * @see https://github.com/laravel-notification-channels/microsoft-teams/blob/master/src/MicrosoftTeams.php
 */
class MicrosoftTeams extends Component
{
    /**
     * Default channel to send notificaions if any other channel not set
     *
     * @var string
     */
    public $defaultChannel;

    /**
     * API HTTP client.
     *
     * @var Client
     */
    protected $httpClient;

    /**
     * @param Client $http
     */
    public function __construct()
    {
        $this->httpClient = new Client();
    }

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->defaultChannel) {
            throw new InvalidConfigException('Default Microsoft Teams Notification Channel is not provided.');
        }
        parent::init();
    }

    /**
     * Send a message to a MicrosoftTeams channel via queue
     *
     * @param MessageCard $message
     *
     * @return boolean
     */
    public function send(MicrosoftTeamsMessageInterface $message): bool
    {
        $job = new TeamsNotificationJob();
        $job->message = $message;
        if (!Yii::$app->queue->push($job)) {
            ErrorHelper::jobNotCreate($job, Yii::$app->queue);
            return false;
        }
        return true;
    }

    /**
     * Send prepared data to a MicrosoftTeams channel webhook url
     *
     * @param string|null $url
     * @param array $data
     *
     * @throws CouldNotSendNotification
     *
     * @return ResponseInterface|null
     */
    public function sendPreparedDataToUrl(?string $url, array $data): ?ResponseInterface
    {
        // Do not spam to real channels during development. Redirrect all messages to single dev channel
        if (YII_ENV_DEV) {
            $url = Yii::$app->params['ms_teams_channel_dev'];
            if (!$url) {
                throw new InvalidConfigException('MS_TEAMS_CHANNEL_DEV is not provided.');
            }
        }
        if (!$url) {
            $url = $this->defaultChannel;
        }
        // convert associative array to numeric array in sections (since ms is otherwise not acception the body structure)
        if (isset($data['sections'])) {
            $data['sections'] = array_values($data['sections']);
        }
        try {
            $response = $this->httpClient->post($url, [RequestOptions::JSON => $data]);
        } catch (ClientException $exception) {
            throw CouldNotSendNotification::microsoftTeamsRespondedWithAnError($exception);
        } catch (Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithMicrosoftTeams($exception);
        }
        //if Adaptive card has incorrect structure - code 200 with error string...For Example:
        //Webhook message delivery failed with error: Microsoft Teams endpoint returned HTTP error 400 with ContextId MS-CV=MgTIReCgJ0q+XzbEJhgT8Q.0..
        $errorPreffix = 'Webhook message delivery failed with error: ';
        $responseText = (string) $response->getBody();
        if (strpos($responseText, $errorPreffix) !== false) {
            throw CouldNotSendNotification::microsoftTeamsError(str_replace($errorPreffix, '', $responseText));
        }

        return $response;
    }

    /**
     * Generate link in Microsoft Teams format
     * @todo: keep here, or create helper?
     * @param string $link
     * @param string $name
     * @return string
     */
    public static function generateLink(string $link, string $name = null): string
    {
        return sprintf('[%s](%s)', $name ?: $link, $link);
    }
}
