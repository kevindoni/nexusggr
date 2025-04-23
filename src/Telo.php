
<?php

namespace Kevindoni\Nexusggr;

use Carbon\Carbon;

class Telo
{
    protected $agent;
    protected $token;
    protected $endpoint;

    /**
     * Create a new Telo instance
     * 
     * @param string|null $agent Agent code
     * @param string|null $token Agent token
     * @param string|null $endpoint API endpoint
     */
    public function __construct(?string $agent = null, ?string $token = null, ?string $endpoint = null)
    {
        $this->agent = $agent ?? $_ENV['TELO_AGENT'] ?? '';
        $this->token = $token ?? $_ENV['TELO_TOKEN'] ?? '';
        $this->endpoint = $endpoint ?? $_ENV['TELO_ENDPOINT'] ?? 'https://api.telo.is/api/v2';
    }

    /**
     * Set client configuration options
     * 
     * @param string $agent Agent code
     * @param string $token Agent token
     * @param string $endpoint API endpoint
     * @return $this
     */
    public function setConfig(string $agent, string $token, string $endpoint)
    {
        $this->agent = $agent;
        $this->token = $token;
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * Post data to API endpoint
     * 
     * @param string $method API method path
     * @param array $data Additional data for request
     * @return array Response from API
     */
    protected function postRequest(string $method, array $data = [])
    {
        $postArray = array_merge([
            'agent_code' => $this->agent,
            'agent_token' => $this->token
        ], $data);

        $jsonData = json_encode($postArray);
        $headerArray = ['Content-Type: application/json'];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->endpoint . '/' . $method,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => $headerArray,
            CURLOPT_TIMEOUT_MS => 60000,
            CURLOPT_CONNECTTIMEOUT_MS => 30000,
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log('cURL error: ' . curl_error($ch));
            curl_close($ch);
            return ['error' => curl_error($ch)];
        }

        curl_close($ch);

        $decodedResponse = json_decode($response, true);

        if ($decodedResponse === null) {
            error_log('JSON decode error: ' . json_last_error_msg());
            return ['error' => 'Invalid JSON response'];
        }

        return $decodedResponse;
    }

    /**
     * Launch game for user
     * 
     * @param string $username User code
     * @param string $provider Provider code
     * @param string $game Game code
     * @param string $gameType Game type (slot or casino)
     * @param string|null $language Language code
     * @param int|null $depositAmount Optional deposit amount
     * @return array API response
     */
    public function launchGame(
        string $username, 
        string $provider, 
        string $game, 
        string $gameType = 'slot',
        ?string $language = null, 
        ?int $depositAmount = null
    ) {
        $data = [
            'user_code' => $username,
            'provider_code' => $provider,
            'game_code' => $game,
            'game_type' => $gameType,
            'lang' => $language ?? 'en'
        ];

        if ($depositAmount !== null) {
            $data['deposit_amount'] = $depositAmount;
        }

        return $this->postRequest('game_launch', $data);
    }

    /**
     * Create a new user
     * 
     * @param string $username User code
     * @param int|null $depositAmount Optional initial deposit
     * @return array API response
     */
    public function createUser(string $username, ?int $depositAmount = null)
    {
        $data = ['user_code' => $username];

        if ($depositAmount !== null) {
            $data['deposit_amount'] = $depositAmount;
        }

        return $this->postRequest('user_create', $data);
    }

    /**
     * Deposit funds to user account
     * 
     * @param string $username User code
     * @param int $amount Amount to deposit
     * @return array API response
     */
    public function deposit(string $username, int $amount)
    {
        $data = [
            'user_code' => $username,
            'amount' => $amount
        ];

        return $this->postRequest('user_deposit', $data);
    }

    /**
     * Withdraw funds from user account
     * 
     * @param string $username User code
     * @param int|null $amount Amount to withdraw, null for all
     * @return array API response
     */
    public function withdraw(string $username, ?int $amount = null)
    {
        $data = ['user_code' => $username];

        if ($amount !== null) {
            $data['amount'] = $amount;
        }

        return $this->postRequest('user_withdraw', $data);
    }

    /**
     * Withdraw all users' balances
     * 
     * @return array API response
     */
    public function withdrawAll()
    {
        return $this->postRequest('user_withdraw_all');
    }

    /**
     * Get agent and user information
     * 
     * @param string|null $username Optional user code
     * @return array API response
     */
    public function info(?string $username = null)
    {
        $data = [];
        
        if ($username !== null) {
            $data['user_code'] = $username;
        }

        return $this->postRequest('info', $data);
    }

    /**
     * Get list of game providers
     * 
     * @param string $gameType Game type (slot or casino)
     * @return array API response
     */
    public function providers(string $gameType = 'slot')
    {
        $data = ['game_type' => $gameType];
        
        return $this->postRequest('provider_list', $data);
    }

    /**
     * Get list of games for a provider
     * 
     * @param string $provider Provider code
     * @param string|null $language Language code
     * @return array API response
     */
    public function games(string $provider, ?string $language = null)
    {
        $data = [
            'provider_code' => $provider,
            'lang' => $language ?? 'en'
        ];
        
        return $this->postRequest('game_list', $data);
    }

    /**
     * Get game history by date range
     * 
     * @param string $gameType Game type (slot or casino)
     * @param string|null $username Optional user code
     * @param string|null $startDate Start date (Y-m-d H:i:s)
     * @param string|null $endDate End date (Y-m-d H:i:s)
     * @param int $page Page number
     * @param int $length Records per page
     * @param string $search Optional search term
     * @return array API response
     */
    public function getGameLogByDate(
        string $gameType = 'slot',
        ?string $username = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $page = 0,
        int $length = 1000,
        string $search = ''
    ) {
        $startDate = $startDate ?? Carbon::now()->subMonth()->format('Y-m-d H:i:s');
        $endDate = $endDate ?? Carbon::now()->addDay()->format('Y-m-d H:i:s');
        
        $data = [
            'game_type' => $gameType,
            'start' => $startDate,
            'end' => $endDate,
            'page' => $page,
            'length' => $length,
            'search' => $search
        ];
        
        if ($username !== null) {
            $data['user_code'] = $username;
        }

        return $this->postRequest('get_date_log', $data);
    }

    /**
     * Get game history by ID
     * 
     * @param int $lastHistoryId Last history ID
     * @param string $gameType Game type (slot or casino)
     * @param string|null $username Optional user code
     * @return array API response
     */
    public function getGameLogById(int $lastHistoryId, string $gameType = 'slot', ?string $username = null)
    {
        $data = [
            'game_type' => $gameType,
            'last_history_id' => $lastHistoryId
        ];
        
        if ($username !== null) {
            $data['user_code'] = $username;
        }

        return $this->postRequest('get_id_log', $data);
    }

    /**
     * Get payment transaction history
     * 
     * @param string|null $username Optional user code
     * @param string|null $startDate Start date (Y-m-d H:i:s)
     * @param string|null $endDate End date (Y-m-d H:i:s)
     * @param int $page Page number
     * @param int $length Records per page
     * @return array API response
     */
    public function getExchangeHistory(
        ?string $username = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $page = 0,
        int $length = 1000
    ) {
        $startDate = $startDate ?? Carbon::now()->subMonth()->format('Y-m-d H:i:s');
        $endDate = $endDate ?? Carbon::now()->addDay()->format('Y-m-d H:i:s');
        
        $data = [
            'start' => $startDate,
            'end' => $endDate,
            'page' => $page,
            'length' => $length
        ];
        
        if ($username !== null) {
            $data['user_code'] = $username;
        }

        return $this->postRequest('get_exchange_history', $data);
    }

    /**
     * Get detailed game log
     * 
     * @param string $provider Provider code
     * @param string $roundId Round ID
     * @return array API response
     */
    public function getLogDetail(string $provider, string $roundId)
    {
        $data = [
            'provider_code' => $provider,
            'round_id' => $roundId
        ];
        
        return $this->postRequest('get_log_detail', $data);
    }

    /**
     * Get current players
     * 
     * @return array API response
     */
    public function currentPlayers()
    {
        return $this->postRequest('current_players');
    }

    /**
     * Get call list for a game
     * 
     * @param string $provider Provider code
     * @param string $game Game code
     * @param string $username User code
     * @param int $callType Call type (1: Common Free, 2: Buy Bonus Free)
     * @return array API response
     */
    public function callList(string $provider, string $game, string $username, int $callType = 1)
    {
        $data = [
            'provider_code' => $provider,
            'game_code' => $game,
            'user_code' => $username,
            'call_type' => $callType
        ];
        
        return $this->postRequest('call_list', $data);
    }

    /**
     * Apply call for a game
     * 
     * @param string $provider Provider code
     * @param string $game Game code
     * @param string $username User code
     * @param int $callRtp RTP value
     * @param int $callType Call type (1: Common Free, 2: Buy Bonus Free)
     * @return array API response
     */
    public function callApply(string $provider, string $game, string $username, int $callRtp, int $callType = 1)
    {
        $data = [
            'provider_code' => $provider,
            'game_code' => $game,
            'user_code' => $username,
            'call_rtp' => $callRtp,
            'call_type' => $callType
        ];
        
        return $this->postRequest('call_apply', $data);
    }

    /**
     * Get call history
     * 
     * @param int $offset Offset for pagination
     * @param int $limit Records per page
     * @param int|null $lastCallId Last call ID
     * @param string $orderDir Order direction (DESC or ASC)
     * @return array API response
     */
    public function callHistory(int $offset = 0, int $limit = 100, ?int $lastCallId = null, string $orderDir = 'DESC')
    {
        $data = [
            'offset' => $offset,
            'limit' => $limit,
            'order_dir' => $orderDir
        ];
        
        if ($lastCallId !== null) {
            $data['last_call_id'] = $lastCallId;
        }

        return $this->postRequest('call_history', $data);
    }

    /**
     * Cancel a call
     * 
     * @param int $callId Call ID
     * @return array API response
     */
    public function callCancel(int $callId)
    {
        $data = ['call_id' => $callId];
        
        return $this->postRequest('call_cancel', $data);
    }

    /**
     * Set agent RTP
     * 
     * @param int $rtp RTP value
     * @return array API response
     */
    public function setAgentRtp(int $rtp)
    {
        $data = ['agent_rtp' => $rtp];
        
        return $this->postRequest('agent_rtp', $data);
    }

    /**
     * Set user RTP
     * 
     * @param string $provider Provider code
     * @param string $username User code
     * @param int $rtp RTP value
     * @return array API response
     */
    public function setUserRtp(string $provider, string $username, int $rtp)
    {
        $data = [
            'provider_code' => $provider,
            'user_code' => $username,
            'user_rtp' => $rtp
        ];
        
        return $this->postRequest('user_rtp', $data);
    }
}
