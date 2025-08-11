<?php

namespace App\Traits;

use App\Config\Constants\{ActivitiesConstants, DatabaseConstants};
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Models\Utility;
use Illuminate\Support\Facades\{Auth, Log};
use Throwable;


/**
 * Trait ZoomMeetingTrait
 */
trait ZoomMeetingTrait
{
    public string $jwt;
    public string $meeting_url = "https://api.zoom.us/v2/";
    private const BASE_URL            = 'https://api.zoom.us/v2/';
    private const OAUTH_TOKEN_ENDPOINT = 'https://zoom.us/oauth/token';
    private const TIMEZONE_DEFAULT    = 'America/Sao_Paulo';
    private Client $client;
    private array  $headers = [];
    private const MEETING_TYPE_SCHEDULED = 2;

    public function __construct(int $meetingType = 2)
    {
        Log::debug(__CLASS__ . '::__construct called');
        $this->client = new Client();
    }

    /**
     * Format date to Zoom's expected time format.
     */
    public function toZoomTimeFormat(string $dateTime): string
    {
        Log::info(__CLASS__ . '::toZoomTimeFormat called', ['input' => $dateTime]);
        try {
            return (new \DateTime($dateTime))->format('Y-m-d\TH:i:s');
        } catch (Throwable $e) {
            Log::error(__CLASS__ . '::toZoomTimeFormat failed', ['message' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Create a Zoom meeting.
     */
    public function createMeeting(array $data): array
    {
        Log::info(__CLASS__ . '::createMeeting called', ['data_keys' => array_keys($data)]);
        $path = 'users/me/meetings';
        $url = $this->retrieveZoomUrl() . $path;
        $payload = [
            'topic'      => $data['title'] ?? '',
            'type'       => self::MEETING_TYPE_SCHEDULED,
            ActivitiesConstants::COL_ST_TIME =>
            $this->toZoomTimeFormat($data[ActivitiesConstants::COL_ST_TIME] ?? ''),
            'duration'   => $data['duration'] ?? 0,
            'password'   => $data['password'] ?? '',
            'agenda'     => $data['agenda'] ?? null,
            'timezone'   => self::TIMEZONE_DEFAULT,
            DatabaseConstants::TABLE_SETTINGS   => [
                'host_video'        => !empty($data['host_video']),
                'participant_video' => !empty($data['participant_video']),
                'waiting_room'      => true,
            ],
        ];

        try {
            $response = $this->client->post($url, [
                'headers'    => $this->getHeader(),
                'json'       => $payload,
            ]);
            $body = json_decode((string) $response->getBody(), true) ?? [];
            if (empty($body))
                Log::warning(__CLASS__ . '::createMeeting returned empty body', ['url' => $url]);
            return [
                'success' => $response->getStatusCode() === 201,
                'data'    => $body,
            ];
        } catch (GuzzleException $e) {
            Log::error(__CLASS__ . '::createMeeting http error', ['message' => $e->getMessage()]);
            return ['success' => false, 'data' => null];
        }
    }

    /**
     * Update an existing Zoom meeting.
     */
    public function updateMeeting(string $id, array $data): array
    {
        Log::info(__CLASS__ . '::updateMeeting called', ['id' => $id]);
        $path = 'meetings/' . $id;
        $url = $this->retrieveZoomUrl() . $path;
        $payload = [
            'topic'      => $data['title'] ?? '',
            'type'       => self::MEETING_TYPE_SCHEDULED,
            ActivitiesConstants::COL_ST_TIME => $this->toZoomTimeFormat($data[ActivitiesConstants::COL_ST_TIME] ?? ''),
            'duration'   => $data['duration'] ?? 0,
            'agenda'     => $data['agenda'] ?? null,
            'timezone'   => config('app.timezone'),
            DatabaseConstants::TABLE_SETTINGS   => [
                'host_video'        => !empty($data['host_video']),
                'participant_video' => !empty($data['participant_video']),
                'waiting_room'      => true,
            ],
        ];
        try {
            $response = $this->client->patch($url, [
                'headers'    => $this->getHeader(),
                'json'       => $payload,
            ]);
            $body = json_decode((string) $response->getBody(), true) ?? [];
            if (empty($body))
                Log::warning(__CLASS__ . '::updateMeeting returned empty body', ['url' => $url]);
            return [
                'success' => $response->getStatusCode() === 204,
                'data'    => $body,
            ];
        } catch (GuzzleException $e) {
            Log::error(__CLASS__ . '::updateMeeting http error', ['message' => $e->getMessage()]);
            return ['success' => false, 'data' => null];
        }
    }

    /**
     * Retrieve a Zoom meeting.
     */
    public function getMeeting(string $id): array
    {
        Log::info(__CLASS__ . '::getMeeting called', ['id' => $id]);
        $path = 'meetings/' . $id;
        $url = $this->retrieveZoomUrl() . $path;
        try {
            $response = $this->client->get($url, [
                'headers' => $this->getHeader(),
            ]);
            $body = json_decode((string) $response->getBody(), true) ?? [];
            if (empty($body))
                Log::warning(__CLASS__ . '::getMeeting returned empty body', ['url' => $url]);
            return [
                'success' => $response->getStatusCode() === 200,
                'data'    => $body,
            ];
        } catch (GuzzleException $e) {
            Log::error(__CLASS__ . '::getMeeting http error', ['message' => $e->getMessage()]);
            return ['success' => false, 'data' => null];
        }
    }

    /**
     * Delete a Zoom meeting.
     */
    public function delete(string $id): array
    {
        Log::info(__CLASS__ . '::deleteMeeting called', ['id' => $id]);
        $path = 'meetings/' . $id;
        $url = $this->retrieveZoomUrl() . $path;
        try {
            $response = $this->client->delete($url, [
                'headers' => $this->getHeader(),
            ]);
            return [
                'success' => $response->getStatusCode() === 204,
            ];
        } catch (GuzzleException $e) {
            Log::error(__CLASS__ . '::deleteMeeting http error', ['message' => $e->getMessage()]);
            return ['success' => false];
        }
    }

    /**
     * Retrieve Zoom API base URL.
     */
    private function retrieveZoomUrl(): string
    {
        Log::info(__CLASS__ . '::retrieveZoomUrl -> ' . self::BASE_URL);
        return self::BASE_URL;
    }

    /**
     * Build headers for Zoom API calls.
     */
    private function getHeader(): array
    {
        Log::info(__CLASS__ . '::getHeader called');
        $token = $this->getToken();
        if ($token === false) {
            Log::warning(__CLASS__ . '::getHeader missing token');
        }
        return [
            'Authorization' => 'Bearer ' . ($token ?: ''),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * Obtain OAuth token for Zoom API.
     *
     * @return string|false
     */
    private function getToken(): string|false
    {
        Log::info(__CLASS__ . '::getToken called');
        try {
            $settings = Utility::settings(Auth::id());
            $id      = $settings['zoom_client_id']     ?? null;
            $secret  = $settings['zoom_client_secret'] ?? null;
            $account = $settings['zoom_account_id']    ?? null;

            if (!$id || !$secret || !$account) {
                Log::warning(__CLASS__ . '::getToken incomplete credentials');
                return false;
            }

            $basicAuth = base64_encode("$id:$secret");
            $response = $this->client->post(self::OAUTH_TOKEN_ENDPOINT, [
                'headers'     => ['Authorization' => "Basic $basicAuth"],
                'form_params' => [
                    'grant_type' => 'account_credentials',
                    'account_id' => $account,
                ],
            ]);

            $body = json_decode((string) $response->getBody(), true) ?? [];
            if (empty($body['access_token'])) {
                Log::warning(__CLASS__ . '::getToken no access_token in response', ['body' => $body]);
                return false;
            }
            return $body['access_token'];
        } catch (GuzzleException $e) {
            Log::error(__CLASS__ . '::getToken http error', ['message' => $e->getMessage()]);
            return false;
        } catch (Throwable $e) {
            Log::error(__CLASS__ . '::getToken failed', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
