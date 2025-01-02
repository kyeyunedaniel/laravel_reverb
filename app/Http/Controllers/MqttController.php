<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpMqtt\Client\Facades\MQTT;
use PhpMqtt\Client\MqttClient;
use Psr\Log\LogLevel;
use PhpMqtt\Client\Examples\Shared\SimpleLogger;
use PhpMqtt\Client\Exceptions\MqttClientException;
use Illuminate\Support\Facades\Log;


class MqttController extends Controller
{
    //
    public function sendMessage(){
       

        try {
            // $logger = new SimpleLogger(LogLevel::INFO);
            
            // Create a new instance of an MQTT client and configure it to use the shared broker host and port.
            // $client = new MqttClient('broker.emqx.io', '1883', 'test-publisher-12345', MqttClient::MQTT_3_1, null);
            $client = new MqttClient(env('MQTT_HOST'), '1883', 'test-publisher-12345', MqttClient::MQTT_3_1, null);
        
            // Connect to the broker without specific connection settings but with a clean session.
            $client->connect(null, true);
            if (!$client->isConnected()) {
                return "Failed to connect to the MQTT broker.";
            }
        
            // Publish the message and check the result.
            $isPublished = $client->publish('145', 'Hello world, ttgvgvhj!',1);        
        
            // Gracefully terminate the connection to the broker.
            $client->disconnect();

            if ($client->isConnected()) {
                return " Failed to disconnect at. ";
            }

        } catch (MqttClientException $e) {
            // Handle exceptions
            return 'An error occurred: ' . $e->getMessage();
        }
       
    }

    public function listenToMessages()
{
    try {
        // Configuration
        $brokerHost = env('MQTT_BROKER_HOST', env('MQTT_HOST'));
        $brokerPort = (int)env('MQTT_BROKER_PORT', 1883);
        $clientId = 'test-listener-12345';
        $topic = env('MQTT_TOPIC', '145');
        $qos = MqttClient::QOS_AT_LEAST_ONCE;

        // Initialize MQTT Client
        $client = new MqttClient($brokerHost, $brokerPort, $clientId, MqttClient::MQTT_3_1, null);

        // Connect to the broker
        $client->connect(null, true);

        if (!$client->isConnected()) {
            Log::error('Failed to connect to the MQTT broker.');
            return "Failed to connect to the MQTT broker.";
        }

        Log::info("Connected to MQTT broker at {}:{}.");

        // Subscribe to the topic with a callback
        $client->subscribe($topic, function (string $topic, string $message) {
            Log::info("Received message on topic '{$topic}': {$message}");

            // Attempt to decode JSON message
            $data = json_decode($message, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                Log::info('Decoded JSON data: ' . json_encode($data, JSON_PRETTY_PRINT));
            } else {
                Log::error("Failed to decode JSON: {$message} | Error: " . json_last_error_msg());
            }
        }, $qos);

        Log::info("Subscribed to topic '{$topic}' with QoS {$qos}.");

        // Start the client loop to listen for messages
        $client->loop(true);

        // Disconnect when the loop ends
        $client->disconnect();
        Log::info('Disconnected from MQTT broker.');

    } catch (MqttClientException $e) {
        Log::error('An error occurred while processing MQTT messages: ' . $e->getMessage(), [
            'exceptionTrace' => $e->getTraceAsString(),
        ]);
        return 'An error occurred: ' . $e->getMessage();
    }
}


}