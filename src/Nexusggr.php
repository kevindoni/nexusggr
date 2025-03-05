<?php

namespace Awkaay\Nexusggr;

use Carbon\Carbon;
use Dotenv\Dotenv;

class Nexusggr
{
    protected $agent;
    protected $token;
    protected $endpoint;

    public function __construct()
    {
        $rootPath = $this->getLaravelBasePath();

        if (file_exists($rootPath . '/.env')) {
            $dotenv = Dotenv::createImmutable($rootPath);
            $dotenv->load();
        }

        $this->agent = $_ENV['NEXUSGGR_AGENT'] ?? '';
        $this->token = $_ENV['NEXUSGGR_TOKEN'] ?? '';
        $this->endpoint = $_ENV['NEXUSGGR_ENDPOINT'] ?? '';
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

    public function transaction(string $username, string $type, int $amount)
    {
        $array = ['user_code' => $username, 'amount' => $amount];

        $transactionType = 'user_' . $type;

        return $this->postArray($transactionType, $array);
    }

    public function resetUserBalance(?string $username)
    {
        $array = $username !== null ? ['user_code' => $username] : ['all_users' => true];

        return $this->postArray('user_withdraw_reset', $array);
    }

    public function launchGame(string $username, string $provier, string $game, ?string $language = null)
    {
        $array = ['user_code' => $username, 'provider_code' => $provier, 'game_code' => $game, 'lang' => $language ?? 'en'];

        return $this->postArray('game_launch', $array);
    }

    public function proviers()
    {
        return $this->postArray('provider_list');
    }

    public function games(string $provier)
    {
        $array = ['provider_code' => $provier];

        return $this->postArray('game_list', $array);
    }

    public function turnovers(string $username)
    {
        $array = ['user_code' => $username, 'game_type' => 'slot', 'start' => Carbon::now()->subMonth()->format('Y-m-d H:i:s'), 'end' => Carbon::now()->addDay()->format('Y-m-d H:i:s'), 'page' => 0, 'perPage' => 1000];

        return $this->postArray('get_game_log', $array);
    }

    public final function curlInitialized($encode)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->endpoint,
            CURLOPT_POST => true,
            CURLOPT_TCP_FASTOPEN => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_POSTFIELDS => $encode,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept-Encoding: gzip'
            ],
            CURLOPT_TIMEOUT_MS => 60000,
            CURLOPT_CONNECTTIMEOUT_MS => 30000,
            CURLOPT_FRESH_CONNECT => false,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_NOSIGNAL => true,
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
