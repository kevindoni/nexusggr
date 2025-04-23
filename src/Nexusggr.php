<?php

namespace Awkaay\Nexusggr;

use Carbon\Carbon;
use Dotenv\Dotenv;

class Nexusggr
{
    protected $agent;
    protected $token;
    protected $endpoint;

    /**
     * Create a new Nexusggr instance
     * 
     * @param string|null $agent Override agent code from .env
     * @param string|null $token Override token from .env
     * @param string|null $endpoint Override endpoint from .env
     */
    public function __construct(?string $agent = null, ?string $token = null, ?string $endpoint = null)
    {
        $rootPath = $this->getLaravelBasePath();

        if (file_exists($rootPath . '/.env')) {
            $dotenv = Dotenv::createImmutable($rootPath);
            $dotenv->load();
        }

        $this->agent = $agent ?? $_ENV['NEXUSGGR_AGENT'] ?? '';
        $this->token = $token ?? $_ENV['NEXUSGGR_TOKEN'] ?? '';
        $this->endpoint = $endpoint ?? $_ENV['NEXUSGGR_ENDPOINT'] ?? '';
    }

    protected function getLaravelBasePath()
    {
        $vendorDir = dirname(__DIR__, 3);
        if (file_exists($vendorDir . '/composer.json')) {
            return realpath($vendorDir);
        }

        return realpath(getcwd());
    }

    public function postArray(string $method, ?array $array = null)
    {
        $postArray = ['method' => $method, 'agent_code' => $this->agent, 'agent_token' => $this->token];

        if ($array !== null) {
            $postArray = array_merge($postArray, $array);
        }

        return $this->curlInitialized(json_encode($postArray));
    }

    public function info(?string $username = null)
    {
        $array = $username !== null ? ['user_code' => $username] : ['all_users' => true];

        return $this->postArray('money_info', $array);
    }

    public function register(string $username)
    {
        $array = ['user_code' => $username];

        return $this->postArray('user_create', $array);
    }

    /**
     * Perform a transaction for a user (deposit or withdraw)
     * 
     * @param string $username User code
     * @param string $type Transaction type (deposit/withdraw)
     * @param int $amount Amount to transaction
     * @param string|null $uniqueId Optional unique ID to prevent duplicate transactions
     * @return array API response
     */
    public function transaction(string $username, string $type, int $amount, ?string $uniqueId = null)
    {
        $array = ['user_code' => $username, 'amount' => $amount];
        
        if ($uniqueId !== null) {
            $array['agent_sign'] = $uniqueId;
        }

        $transactionType = 'user_' . $type;

        return $this->postArray($transactionType, $array);
    }

    public function resetUserBalance(?string $username)
    {
        $array = $username !== null ? ['user_code' => $username] : ['all_users' => true];

        return $this->postArray('user_withdraw_reset', $array);
    }

    /**
     * Launch game with additional optional parameters
     * 
     * @param string $username User code
     * @param string $provider Provider code
     * @param string $game Game code (optional for some providers like EVOLUTION)
     * @param string|null $language Language code
     * @param array|null $additionalParams Additional parameters
     * @return array API response
     */
    public function launchGame(string $username, string $provider, string $game, ?string $language = null, ?array $additionalParams = null)
    {
        $array = [
            'user_code' => $username, 
            'provider_code' => $provider, 
            'game_code' => $game, 
            'lang' => $language ?? 'en'
        ];

        if ($additionalParams !== null) {
            $array = array_merge($array, $additionalParams);
        }

        return $this->postArray('game_launch', $array);
    }

    public function providers()
    {
        return $this->postArray('provider_list');
    }

    /**
     * Get list of games for a specific provider
     *
     * @param string $provider Provider code
     * @return array API response
     */
    public function games(string $provider)
    {
        $array = ['provider_code' => $provider];

        return $this->postArray('game_list', $array);
    }

    /**
     * Get game turnover history with custom parameters
     * 
     * @param string $username User code
     * @param string $gameType Game type (slot, live, etc.)
     * @param string|null $startDate Start date in Y-m-d H:i:s format
     * @param string|null $endDate End date in Y-m-d H:i:s format
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return array API response
     */
    public function turnovers(
        string $username, 
        string $gameType = 'slot',
        ?string $startDate = null, 
        ?string $endDate = null,
        int $page = 0,
        int $perPage = 1000
    ) {
        $startDate = $startDate ?? Carbon::now()->subMonth()->format('Y-m-d H:i:s');
        $endDate = $endDate ?? Carbon::now()->addDay()->format('Y-m-d H:i:s');
        
        $array = [
            'user_code' => $username,
            'game_type' => $gameType,
            'start' => $startDate,
            'end' => $endDate,
            'page' => $page,
            'perPage' => $perPage
        ];

        return $this->postArray('get_game_log', $array);
    }

    public function currentPlayers()
    {
        return $this->postArray('call_players');
    }

    /**
     * Get scatter list for a specific game
     *
     * @param string $provider Provider code
     * @param string $game Game code
     * @return array API response
     */
    public function callScatterList(string $provider, string $game)
    {
        $array = ['provider_code' => $provider, 'game_code' => $game];

        return $this->postArray('call_list', $array);
    }

    /**
     * Apply a scatter call for a user
     *
     * @param string $username User code
     * @param string $provider Provider code
     * @param string $game Game code
     * @param int $rtp RTP setting
     * @param int $type Call type (1: Common Free, 2: Buy Bonus Free)
     * @return array API response
     */
    public function callScatterApply(string $username, string $provider, string $game, int $rtp, int $type)
    {
        $array = ['provider_code' => $provider, 'game_code' => $game, 'user_code' => $username, 'call_rtp' => $rtp, 'call_type' => $type];

        return $this->postArray('call_apply', $array);
    }

    /**
     * Get call history with custom pagination
     * 
     * @param int $offset Offset for pagination
     * @param int $limit Number of records per page
     * @return array API response
     */
    public function callHistory(int $offset = 0, int $limit = 100)
    {
        $array = ['offset' => $offset, 'limit' => $limit];

        return $this->postArray('call_history', $array);
    }

    public function cancelCall(int $callId)
    {
        $array = ['call_id' => $callId];

        return $this->postArray('call_cancel', $array);
    }

    public function controlUserRtp(string $username, string $provider, int $rtp)
    {
        $array = ['provider_code' => $provider, 'user_code' => $username, 'rtp' => $rtp];

        return $this->postArray('control_rtp', $array);
    }

    public function controlAllUsersRtp(int $rtp)
    {
        $getInfo = $this->info();
        if (array_key_exists('user_list', $getInfo)) {
            $userCodes = [];
            foreach ($getInfo['user_list'] as $user) {
                $userCodes[] = $user['user_code'];
            }
            $array = ['user_codes' => json_encode($userCodes), 'rtp' => $rtp];
            return $this->postArray('control_users_rtp', $array);
        } else {
            return ['error' => 'Not found any user'];
        }
    }

    /**
     * Check the status of a transfer using the unique ID
     * 
     * @param string $username User code
     * @param string $uniqueId The unique ID used for the transfer
     * @return array API response
     */
    public function transferStatus(string $username, string $uniqueId)
    {
        $array = [
            'user_code' => $username,
            'agent_sign' => $uniqueId
        ];

        return $this->postArray('transfer_status', $array);
    }

    /**
     * Set client configuration options
     * 
     * @param string $agent Agent code
     * @param string $token Agent token
     * @param string $endpoint API endpoint
     * @return void
     */
    public function setConfig(string $agent, string $token, string $endpoint)
    {
        $this->agent = $agent;
        $this->token = $token;
        $this->endpoint = $endpoint;
    }

    public final function curlInitialized($encode)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->endpoint,
            CURLOPT_POST => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_POSTFIELDS => $encode,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept-Encoding: gzip'
            ],
            CURLOPT_TIMEOUT_MS => 60000,
            CURLOPT_CONNECTTIMEOUT_MS => 30000,
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            error_log('cURL error: ' . curl_error($curl));
            curl_close($curl);
            return ['error' => curl_error($curl)];
        }

        curl_close($curl);

        $decodedResponse = json_decode($response, true);

        if ($decodedResponse === null) {
            error_log('JSON decode error: ' . json_last_error_msg());
            return ['error' => 'Invalid JSON response'];
        }

        return $decodedResponse;
    }
}
