<?php

namespace NotificationChannels\MicrosoftTeams;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use NotificationChannels\MicrosoftTeams\Exceptions\CouldNotSendNotification;
use Psr\Http\Message\ResponseInterface;

class MicrosoftTeams
{
    /**
     * API HTTP client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @param \GuzzleHttp\Client $http
     */
    public function __construct(HttpClient $http)
    {
        $this->httpClient = $http;
    }

    /**
     * Send a message to a MicrosoftTeams channel.
     *
     * @param string $url
     * @param array $data
     *
     * @throws CouldNotSendNotification
     *
     * @return ResponseInterface|null
     */
    public function send(string $url, array $data): ?ResponseInterface
    {
        if (!$url) {
            throw CouldNotSendNotification::microsoftTeamsWebhookUrlMissing();
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

}
