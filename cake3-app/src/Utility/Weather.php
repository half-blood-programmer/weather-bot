<?php
namespace App\Utility;

use Cmfcmf\OpenWeatherMap;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Laminas\Diactoros\RequestFactory;

class Weather extends OpenWeatherMap
{
    /**
     * Weather constructor.
     * @param $apiKey
     * @param null $cache
     * @param int $ttl
     */
    public function __construct($apiKey, $cache = null, $ttl = 600)
    {
        parent::__construct($apiKey, GuzzleAdapter::createWithConfig([]), new RequestFactory(), $cache, $ttl);
    }

    /**
     * @param $cityId
     * @param string $languageCode
     * @return string
     * @throws OpenWeatherMap\Exception
     */
    public function getWeatherMessage($cityId, $languageCode = 'en')
    {
        $weather = $this->getWeather($cityId, 'metric', $languageCode);
        $temperature = $weather->temperature->now;

        return $this->getIconEmoji($weather->weather->icon) . ' ' . (int) $temperature->getValue() . $temperature->getUnit() . ' | ' . $weather->wind->speed;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getIconEmoji($code)
    {
        $icons = [
            '01d' => '☀️',
            '01n' => '🌕',
            '02d' => '🌤',
            '02n' => '🌤',
            '03d' => '🌥',
            '03n' => '🌥',
            '04d' => '☁️',
            '04n' => '☁️',
            '09d' => '🌧',
            '09n' => '🌧',
            '10d' => '🌦',
            '10n' => '🌦',
            '11d' => '🌩',
            '11n' => '🌩',
            '13d' => '❄️',
            '13n' => '❄️',
            '50d' => '💨',
            '50n' => '💨',
        ];

        return $icons[$code];
    }
}
